<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Backup
 *
 * @Flow\Entity
 */
class Backup {

	/**
	 * Date of backup
	 * @var \DateTime
	 * @ORM\Column(name="backupdate")
	 */
	protected $date;

	/**
	 * Backup file
	 * @var \TYPO3\Flow\Resource\Resource
	 * @ORM\OneToOne
	 */
	protected $file;

	/**
	 * The desc
	 * @var string
	 * @ORM\Column(name="backupdesc")
	 */
	protected $desc;

	/**
	 * Is it a restore backup
	 *
	 * @var boolean
	 */
	protected $restoreBackup;

	/**
	 * Construtor
	 */
	public function __construct() {
		$this->date = new \DateTime();
	}

	/**
	 * Get date
	 *
	 * @return \DateTime The backup's date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Set date
	 *
	 * @param \DateTime $date The backup's date
	 */
	public function setDate(\DateTime $date) {
		$this->date = $date;
	}

	/**
	 * @param \TYPO3\Flow\Resource\Resource $file
	 * @return void
	 */
	public function setFile(\TYPO3\Flow\Resource\Resource $file) {
		$this->file = $file;
	}

	/**
	 * @return \TYPO3\Flow\Resource\Resource
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Set description
	 *
	 * @param string $desc The backup's desc
	 */
	public function setDesc($desc) {
		$this->desc = $desc;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDesc() {
		return $this->desc;
	}

	/**
	 * Get restoreBackup
	 *
	 * @return boolean
	 */
	public function getRestoreBackup() {
		return $this->restoreBackup;
	}

	/**
	 * Set restoreBackup
	 *
	 * @param boolean $restoreBackup
	 */
	public function setRestoreBackup($restoreBackup) {
		$this->restoreBackup = $restoreBackup;
	}

}
