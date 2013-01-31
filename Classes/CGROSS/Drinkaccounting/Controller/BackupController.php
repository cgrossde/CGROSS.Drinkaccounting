<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

use \CGROSS\Drinkaccounting\Domain\Model\Backup;

/**
 * Backup controller for the Drinkaccounting package
 *
 * @FLOW3\Scope("singleton")
 */
class BackupController extends DefaultController {

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\BackupRepository
	 */
	protected $backupRepository;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * globalSettings
	 * @var array
	 */
	protected $globalSettings;

	/**
	 * Configuration manager
	 */
	protected $configurationManager;

	/**
	 * Inject configuration manager
	 */
	public function injectConfigurationManager(\TYPO3\Flow\Configuration\ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * Inject the settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Shows a list of accounts
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('backups', $this->backupRepository->findAll());
	}

	/**
	 * Shows a form for creating a new backup object
	 *
	 * @return void
	 */
	public function newAction() {
	}

	public function initializeAction() {
		$this->globalSettings = $this->configurationManager->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Flow');
	}

	public function initializeCreateAction() {
		$this->arguments['newBackup' ]
			->getPropertyMappingConfiguration()
			->forProperty('date')
			->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
			\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
			$this->settings['system']['dateFormat']);
	}

	/**
	 * Adds the given new backup object to the backup repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Backup $newBackup A new backup to add
	 * @return void
	 */
	public function createAction(Backup $newBackup) {
		// Create backup
		$filePath = $this->backupTables();

		// Create Ressource
		$file = $this->resourceManager->importResource($filePath);

		// Delete backup in tmp
		unlink($filePath);

		// Add file to backup
		$newBackup->setFile($file);
		$newBackup->setRestoreBackup(false);

		// If everything works out add backup to repository
		$this->backupRepository->add($newBackup);
		$this->addFlashMessage('Created a new backup.');
		$this->redirect('index');
	}

	/**
	 * Removes the given backup object from the backup repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Backup $backup The backup to delete
	 * @return void
	 */
	public function deleteAction(Backup $backup) {
		$this->backupRepository->remove($backup);
		$this->addFlashMessage('Deleted backup.');
		$this->redirect('index');
	}

	/**
	 * Download action, to download a backup
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Backup $backup The backup to download
	 */
	public function downloadAction(\CGROSS\Drinkaccounting\Domain\Model\Backup $backup) {
		// Get file
		$file = 'resource://'.$backup->getFile();

		// Create header
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . 'db_backup_'.date('Y-m-d', $backup->getDate()->getTimestamp()).'.sql' );
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));

		if (count(ob_list_handlers()) > 0) {
			ob_clean();
		}
		flush();
		readfile($file);
		exit;
	}

	/**
	 * Restore a backup
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Backup $backup The backup to restore
	 */
	public function restoreAction(\CGROSS\Drinkaccounting\Domain\Model\Backup $backup) {
		// If no restoreBackup, make backup first to not loose other backups if needed
		if(!$backup->getRestoreBackup()) {
			// Create new object
			$newBackup = new Backup();
			$newBackup->setDesc('Before restore: '.$backup->getDesc());

			// Create backup
			$filePath = $this->backupTables();

			// Create Ressource
			$file = $this->resourceManager->importResource($filePath);

			// Delete backup in tmp
			unlink($filePath);

			// Add file to backup
			$newBackup->setFile($file);
			$newBackup->setRestoreBackup(true);
		}

		// Get filename of backup
		$file = $this->resourceManager->getPersistentResourcesStorageBaseUri().$backup->getFile();

		// Drop all tables
		$num = $this->dropTables();

		// Restore
		$this->restoreTables($file);

		// Doctrine update (So application does not break if restore fails, or models were added)
		\TYPO3\Flow\Core\Booting\Scripts::executeCommand('typo3.flow:doctrine:update', $this->globalSettings);

		if(!$backup->getRestoreBackup()) {
			// After update add restoreBackup to repository
			$this->backupRepository->add($newBackup);
		}

		$this->addFlashMessage('Restored backup.'.$num);
		$this->redirect('index');
	}

	/**
	 * Backup function, whole db or single table
	 *
	 * @param string $tables
	 * @return Filepath of backup
	 */
	function backupTables($tables = '*') {
		// Get DB configuration
		$name		= $this->globalSettings['persistence']['backendOptions']['dbname'];
		$user		= $this->globalSettings['persistence']['backendOptions']['user'];
		$pass		= $this->globalSettings['persistence']['backendOptions']['password'];
		$host		= $this->globalSettings['persistence']['backendOptions']['host'];


		//save file
		$fileName = $this->settings['system']['backupPath'].'db-backup-'.time().'.sql';
		exec($this->settings['system']['mysqldumpPath'].'mysqldump -u'.$user.' -p"'.$pass.'" -h'.$host.' -r'.$fileName.' '.$name);


		return $fileName;
	}

	/**
	 * Drop all tables
	 *
	 * @return int
	 */
	function dropTables() {
		// Get DB configuration
		$globalSettings = $this->configurationManager->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Flow');
		$name		= $globalSettings['persistence']['backendOptions']['dbname'];
		$user		= $globalSettings['persistence']['backendOptions']['user'];
		$pass		= $globalSettings['persistence']['backendOptions']['password'];
		$host		= $globalSettings['persistence']['backendOptions']['host'];

		// Connect
		$link = mysql_connect($host,$user,$pass);
		mysql_select_db($name,$link);

		// While there are tables
		$tablesExist = true;
		$num = 0;
		while($tablesExist) {
			//get all of the tables
			$tables = array();
			$result = mysql_query('SHOW TABLES');
			while($row = mysql_fetch_row($result))
			{
				$tables[] = $row[0];
			}

			$tablesExist = (count($tables) > 1);

			$return = "";
			//cycle through
			foreach($tables as $table)
			{
				$return .= $table.',';
			}
			mysql_query('DROP TABLE '.rtrim($return,',').' ;');
			$num++;

			// Limit number of cycles
			if($num > 10) {
				$tablesExist = false;
			}
		}
		mysql_close($link);
		return $num;
	}

	/**
	 * Restore Tables
	 *
	 * @param string $file File to restore tables from
	 * @return void
	 */
	public function restoreTables($file) {
		// Get DB configuration
		$name		= $this->globalSettings['persistence']['backendOptions']['dbname'];
		$user		= $this->globalSettings['persistence']['backendOptions']['user'];
		$pass		= $this->globalSettings['persistence']['backendOptions']['password'];
		$host		= $this->globalSettings['persistence']['backendOptions']['host'];

		exec($this->settings['system']['mysqlPath'].'mysql -u'.$user.' -p"'.$pass.'" -h'.$host.' '.$name.' < '.$file);
	}

}

?>