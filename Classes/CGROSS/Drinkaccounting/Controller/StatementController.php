<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \CGROSS\Drinkaccounting\Domain\Model\Statement;

/**
 * Statement controller for the Drinkaccounting package
 *
 * @Flow\Scope("singleton")
 */
class StatementController extends DefaultController {

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @var array
	 */
	protected $settings;

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
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\StatementRepository
	 */
	protected $statementRepository;

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\ProductRepository
	 */
	protected $productRepository;

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\PurchaseRepository
	 */
	protected $purchaseRepository;

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 *
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \TYPO3\Flow\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\Flow\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Shows a list of unbilled statements
	 *
	 * @return void
	 */
	public function indexAction() {
		$statements = $this->statementRepository->findAllUnbilled();
		// Calculate wizard progress
		for($p = 0; $p < count($statements); $p++) {
			$statements[$p]->progress = (100/$this->settings['statementWizard']['steps']) * $statements[$p]->getStep();
		}
		$this->view->assign('statements', $statements);
	}

	/**
	 * Shows a list of billed statements
	 *
	 * @return void
	 */
	public function billedAction() {
		$this->view->assign('statements', $this->statementRepository->findAllBilled());
	}

	public function initializeAjaxUpdateAction() {
		$this->arguments['statement' ]
			->getPropertyMappingConfiguration()
			->forProperty('dateStart')
			->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
				\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
				$this->settings['system']['dateFormat']);
		$this->arguments['statement' ]
			->getPropertyMappingConfiguration()
			->forProperty('dateStop')
			->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
				\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
				$this->settings['system']['dateFormat']);
	}

	/**
	 * Updates the given statement object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to update
	 * @return string
	 */
	public function ajaxUpdateAction(Statement $statement) {
		$this->statementRepository -> update($statement);
		$this->persistenceManager->persistAll();
		return ($this->arguments['statement']->isValid()) ? "VALID" : "INVALID";
	}

	/**
	 * Removes the given statement object from the statement repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to delete
	 * @return void
	 */
	public function deleteAction(Statement $statement) {
		// Remove purchases from statement
		foreach ($statement->getPurchases() as $purchase) {
			$purchase->setStatement(NULL);
			$this->purchaseRepository->update($purchase);
		}
		$statement->setPurchases(NULL);
		$this -> statementRepository -> remove($statement);
		$this->persistenceManager->persistAll();
		$this -> addFlashMessage('Deleted a statement.');
		$this -> redirect('index');
	}


	/**
 	 * STEP 1: New statement
	 *
	 * @return void
	 */
	public function newAction() {
		$this->view->assign('steps', $this->settings['statementWizard']['steps']);
		$this->view->assign('progress', 100/$this->settings['statementWizard']['steps']);
	}

	public function initializeCreateAction() {
		$this->arguments['newStatement' ]
			->getPropertyMappingConfiguration()
			->forProperty('dateStart')
			->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
				\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
				$this->settings['system']['dateFormat']);
		$this->arguments['newStatement' ]
			->getPropertyMappingConfiguration()
			->forProperty('dateStop')
			->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
				\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
				$this->settings['system']['dateFormat']);
	}
	/**
	 * STEP 1: Create statement
	 * Adds the given new statement object to the statement repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $newStatement A new statement to add
	 * @return void
	 */
	public function createAction(Statement $newStatement) {
		$newStatement->setBilled(false);
		$newStatement->setStep(2);
		$newStatement->setStepController('config');
		$this->statementRepository -> add($newStatement);
		$this->addFlashMessage('Created a new statement.');
		$this->redirect('config', 'Statement', NULL, array('statement' => $newStatement));
	}

	/**
	 * STEP 2: Config statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to config
	 * @return void
	 */
	public function configAction(Statement $statement) {
		#if($statement->getStep() < 2) {
			$statement->setStep(2);
			$statement->setStepController('config');
			$this->statementRepository->update($statement);
			$this->persistenceManager->persistAll();
		#}

		$this->view->assign('statement', $statement);
		$this->view->assign('products', $this->productRepository->findAll());
		$this->view->assign('users', $this->userRepository->findAll());

		$this->view->assign('steps', $this->settings['statementWizard']['steps']);
		$this->view->assign('progress', (100/$this->settings['statementWizard']['steps']) * 2);
	}

	/**
	 * STEP 2: Update selected products, users
	 * 			If users or products are removed, we have to take care that the related
	 * 			stocks and consumptions are removed to, else we will get some zombies
	 *
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @param array $selectedUsers
	 * @param array $selectedProducts
	 * @return void
	 */
	public function updateConfigAction(Statement $statement, $selectedUsers, $selectedProducts) {
		// Prevent changes if statement was already billed
		if($statement->getBilled()) {
			$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
		}

		// Get copy of old product list
		$oldProducts = $statement->getProducts()->toArray();
		$oldProducts = new \Doctrine\Common\Collections\ArrayCollection($oldProducts);

		// Clear products
		$statement->clearProducts();

		// Get product objects by identifier and add them to the statement
		foreach ($selectedProducts as $productID) {
			if($productID != 'noneSelectedWorkaround') {
				$product = $this->persistenceManager->getObjectByIdentifier($productID, '\CGROSS\Drinkaccounting\Domain\Model\Product');
				// Check if previously selected to get a list of deleted products
				if($oldProducts->contains($product)) {
					#$this->systemLogger->log("Removing ".$product->getName());
					$oldProducts->removeElement($product);
				}
				$statement->addProduct($product);
			}
		}

		/*// DEBUG
		$productsbefore = array();
		foreach($oldProducts as $oldProduct) {
			$productsbefore[] = $oldProduct->getName();
		}
		$this->systemLogger->log(print_r($productsbefore, TRUE));
		// DEBUG*/

		// oldProducts contains now the "deleted" Products
		if($oldProducts->count() > 0) {
			// Remove all stocks and consumptions of these deleted products
			$stocks = $statement->getStocks();
			$consumptions = $statement->getConsumptions();

			// Remove from stocks
			if($stocks->count() > 0) {
				foreach($stocks as $stock) {
					if($oldProducts->contains($stock->getProduct())) {
						#$this->systemLogger->log("Found deprc. stock of ".$stock->getProduct()->getName());
						$statement->removeStock($stock);
					}
				}
			}
			// Remove from consumptions
			if($consumptions->count() > 0) {
				foreach($consumptions as $consumption) {
					if($oldProducts->contains($consumption->getProduct())) {
						#$this->systemLogger->log("Found deprc. consumptions of ".$consumption->getProduct()->getName());
						$statement->removeConsumption($consumption);
					}
				}
			}

		}

		// Get copy of old user list
		$oldUsers = $statement->getUsers()->toArray();
		$oldUsers = new \Doctrine\Common\Collections\ArrayCollection($oldUsers);

		// Clear users
		$statement -> clearUsers();
		// Get user objects by identifier
		foreach ($selectedUsers as $userID) {
			if($userID != 'noneSelectedWorkaround') {
				$user = $this -> persistenceManager -> getObjectByIdentifier($userID, '\CGROSS\Drinkaccounting\Domain\Model\User');
				// Check if previously selected to get a list of deleted products
				if($oldUsers->contains($user)) {
					#$this->systemLogger->log("Removing ".$user->getName());
					$oldUsers->removeElement($user);
				}
				$statement -> addUser($user);
			}
		}

		// oldUsers contains now the "deleted" Users
		if($oldUsers->count() > 0) {
			// Remove all consumptions of these deleted Users
			$consumptions = $statement->getConsumptions();

			// Remove from consumptions
			if($consumptions->count() > 0) {
				foreach($consumptions as $consumption) {
					if($oldUsers->contains($consumption->getUser())) {
						#$this->systemLogger->log("Found deprc. consumptions of ".$consumption->getUser()->getName());
						$statement->removeConsumption($consumption);
					}
				}
			}
		}

		$this -> statementRepository -> update($statement);
		$this->persistenceManager->persistAll();
		$this -> addFlashMessage('Updated config');
		$this -> redirect('config', 'Statement', NULL, array('statement' => $statement));
	}

	/**
	 * STEP 2: Print list
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to show
	 * @return void
	 */
	public function printListAction(Statement $statement) {
		$this->view->assign('statement', $statement);
	}

	/**
	 * STEP 3: Add purchases
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to config
	 * @return void
	 */
	public function purchasesAction(Statement $statement) {
		// If initial stock statement -> skip this wizard
		if($statement->isInitialStockStatement()) {
			$this->addFlashMessage('Purchases wizard was skipped because this is an initial stock statement');
			$this->redirect('stocks', 'Statement', NULL, array('statement' => $statement));
		}

		#if($statement->getStep() < 3) {
			$statement->setStep(3);
			$statement->setStepController('purchases');
			$this->statementRepository->update($statement);
			$this->persistenceManager->persistAll();
		#}

		$this->view->assign('statement', $statement);
		$this->view->assign('purchases', $this->purchaseRepository->findAllWithoutStatement($statement));

		// Check if selected purchases contain products that are not in this statement
		$inactiveProducts = $this->checkPurchasesForInactiveProducts($statement);

		$this->view->assign('inactiveProducts', $inactiveProducts);
		$this->view->assign('steps', $this->settings['statementWizard']['steps']);
		$this->view->assign('progress', (100/$this->settings['statementWizard']['steps']) * 3);
	}

	/**
	 * STEP 3: Update selected purchases
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @param array $selectedPurchases
	 * @return void
	 */
	public function updatePurchasesAction(Statement $statement, $selectedPurchases) {
		// Prevent changes if statement was already billed
		if($statement->getBilled()) {
			$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
		}
		// Clear products - does not work, we need to remove them one by one
		// beacuse the purchases<->statement relation is bidrectional
		foreach($statement->getPurchases() as $purchase) {
			$purchase->setStatement(NULL);
			$this->purchaseRepository->update($purchase);
		}
		$statement->clearPurchases();

		// Get product objects by identifier
		foreach ($selectedPurchases as $purchaseID) {
			if($purchaseID != 'noneSelectedWorkaround') {
				$purchase = $this->persistenceManager->getObjectByIdentifier($purchaseID, '\CGROSS\Drinkaccounting\Domain\Model\Purchase');
				$statement->addPurchase($purchase);
				$this->purchaseRepository->update($purchase);
			}
		}

		$this -> statementRepository -> update($statement);
		$this->persistenceManager->persistAll();
		$this -> addFlashMessage('Updated purchases');
		$this -> redirect('purchases', 'Statement', NULL, array('statement' => $statement));
	}

	/**
	 * STEP 4: Add stocks and select initial stock
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to config
	 * @return void
	 */
	public function stocksAction(Statement $statement) {
		#if($statement->getStep() < 4) {
			$statement->setStep(4);
			$statement->setStepController('stocks');
			$this->statementRepository->update($statement);
		#}

		// Products with added info about initial stock, purchased bottles and supposed consumption
		$products = $this->addInfoToProducts($statement);
		$statement->setProducts($products);

		// Create stocks array with bottleAmount values
		$stocks = array();
		foreach ($statement->getStocks() as $stock) {
			$productID = $this->persistenceManager->getIdentifierByObject($stock->getProduct());
			$stocks[$productID] = $stock->getBottleAmount();
		}

		// Assign vars for view
		$this->view->assign('statement', $statement);
		$this->view->assign('statements', $this->statementRepository->findAllPossibleInitialStockStatements(
			($statement->getInitialStockStatement()) ? $statement->getInitialStockStatement() : NULL
		));
		$this->view->assign('stocks', $stocks);

		$this->view->assign('steps', $this->settings['statementWizard']['steps']);
		$this->view->assign('progress', (100/$this->settings['statementWizard']['steps']) * 4);
		$this->persistenceManager->persistAll();
	}

	/**
	 * STEP 4: Update initial stock statement and current stocks
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @param array $stocks The stock values of the products
	 * @return void
	 */
	public function updateStocksAction(Statement $statement, $stocks) {
		// Prevent changes if statement was already billed
		if($statement->getBilled()) {
			$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
		}
		// Clear stocks
		$statement->clearStocks();
		// Generate stocks
		foreach ($stocks as $productID => $bottleAmount) {
			$product = $this->persistenceManager->getObjectByIdentifier($productID, '\CGROSS\Drinkaccounting\Domain\Model\Product');
			$newStock = new \CGROSS\Drinkaccounting\Domain\Model\Stock();
			$newStock->setProduct($product);
			$newStock->setStatement($statement);
			$newStock->setBottleAmount($bottleAmount);
			$statement->addStock($newStock);
		}

		$this -> statementRepository -> update($statement);
		$this->persistenceManager->persistAll();
		$this -> addFlashMessage('Updated config');
		$this -> redirect('stocks', 'Statement', NULL, array('statement' => $statement));
	}

	/**
	 * STEP 5: Input consumption
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @return void
	 */
	public function consumptionAction(Statement $statement) {
		// If initial stock statement -> skip this wizard
		if($statement->isInitialStockStatement()) {
			$this->addFlashMessage('Consumption wizard was skipped because this is an initial stock statement');
			$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
		}

		#if($statement->getStep() < 5) {
			$statement->setStep(5);
			$statement->setStepController('consumption');
			$this->statementRepository->update($statement);
		#}

		// Get consumption array
		$consumptionArray = $this->createConsumptionArray($statement);

		// Products with added info about uuid, initial stock, purchased bottles, supposed consumption and actual consumption
		$products = $this->addInfoToProducts($statement, $consumptionArray);
		$statement->setProducts($products);

		// Create values array
 		$values = $this->createValuesArray($statement, $consumptionArray);

		$this->view->assign('statement', $statement);
		$this->view->assign('values', $values);

		$this->view->assign('steps', $this->settings['statementWizard']['steps']);
		$this->view->assign('progress', (100/$this->settings['statementWizard']['steps']) * 5);
		$this->persistenceManager->persistAll();
	}

	/**
	 * STEP 5: Update consumption
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @param array $consumption The stock values of the products
	 * @return void
	 */
	public function updateConsumptionAction(Statement $statement, $consumption) {
		// Prevent changes if statement was already billed
		if($statement->getBilled()) {
			$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
		}

		// Clear consumptions
		$statement->clearConsumptions();
		// Generate consumptions
		foreach ($consumption as $userID => $products) {
			foreach($products as $productID => $value) {
				$product = $this->persistenceManager->getObjectByIdentifier($productID, '\CGROSS\Drinkaccounting\Domain\Model\Product');
				$user = $this->persistenceManager->getObjectByIdentifier($userID, '\CGROSS\Drinkaccounting\Domain\Model\User');
				$date = $statement->getDateStop();
				$newConsumption = new \CGROSS\Drinkaccounting\Domain\Model\Consumption();
				$newConsumption->setProduct($product);
				$newConsumption->setUser($user);
				$newConsumption->setDate($date);
				$newConsumption->setBottleAmount($value);
				$statement->addConsumption($newConsumption);
			}
		}

		$this->statementRepository->update($statement);
		$this->persistenceManager->persistAll();
		$this->addFlashMessage('Updated consumption');
		$this->redirect('consumption', 'Statement', NULL, array('statement' => $statement));
	}

	/**
	 * STEP 6: Check statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @return void
	 */
	public function finalAction(Statement $statement) {
		#if($statement->getStep() < 6) {
			$statement->setStep(6);
			$statement->setStepController('final');
			$this->statementRepository->update($statement);
		#}

		// Get consumption array
		$consumptionArray = $this->createConsumptionArray($statement);

		// Products with added info about uuid, initial stock, purchased bottles, supposed consumption and actual consumption
		$products = $this->addInfoToProducts($statement, $consumptionArray);
		$statement->setProducts($products);

		// Create values array
		$values = $this->createValuesArray($statement, $consumptionArray, TRUE);

		$this->view->assign('statement', $statement);
		$this->view->assign('values', $values);

		$this->view->assign('steps', $this->settings['statementWizard']['steps']);
		$this->view->assign('progress', (100/$this->settings['statementWizard']['steps']) * 6);
		$this->persistenceManager->persistAll();
	}

	/**
	 * STEP 7: Final step, commit statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @return void
	 */
	public function commitAction(Statement $statement) {
		// Don't commit again if statement was already billed
		if($statement->getBilled()) {
			$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
		}

		// Don't commit if purchases contain products that are not part of this statement
		if($this->checkPurchasesForInactiveProducts($statement)) {
			$this->redirect('purchases', 'Statement', NULL, array('statement' => $statement));
		}

		// Get consumption array
		$consumptionArray = $this->createConsumptionArray($statement);

		// Products with added info about uuid, initial stock, purchased bottles, supposed consumption and actual consumption
		$products = $this->addInfoToProducts($statement, $consumptionArray);
		$statement->setProducts($products);

		// Create values array
 		$values = $this->createValuesArray($statement, $consumptionArray, TRUE);

		// Add payments for statement to all users
		foreach ($values as $userInfo) {
			$user = $this->persistenceManager->getObjectByIdentifier($userInfo['userUUID'], '\CGROSS\Drinkaccounting\Domain\Model\User');
			// Only if sum to pay is not 0 (in case of initial stock statement)
			if(-$userInfo['current'] - $userInfo['lossFee'] != 0) {
				$newPayment = new \CGROSS\Drinkaccounting\Domain\Model\Payment();
				$newPayment->setSum(-$userInfo['current'] - $userInfo['lossFee']);
				$newPayment->setDate(new \DateTime);
				$newPayment->setDeletable(FALSE);
				$newPayment->setDesc('<b>Statement:</b> '.$statement->getTitle());

				$statement->addPayment($newPayment);
				$user->addPayment($newPayment);

				// Update user
				$this->userRepository->update($user);
			}
		}
		// Mark statement as billed
		$statement->setBilled(TRUE);

		// Update statement
		$this->statementRepository->update($statement);
		$this->persistenceManager->persistAll();
		// Return to final statement
		$this->redirect('final', 'Statement', NULL, array('statement' => $statement));
	}

	/**
	 * Add initial stock, purchased bottles and consumption info to products
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @param array $consumptionArray The consumption info
	 * @return \Doctrine\Common\Collections\Collection The products with integrated info for the view
	 */
	public function addInfoToProducts(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement, $consumptionArray = NULL) {
		// Get purchased bottles amount array by product
		$purchasedBottles = array();
		foreach ($statement->getPurchases() as $purchase) {
			foreach ($purchase->getPurchasePositions() as $purchasePosition) {
				// Check if is subproduct
				if($purchasePosition->getProduct()->getParent()) {
					$uuid = $this->persistenceManager->getIdentifierByObject($purchasePosition->getProduct()->getParent());
					$purchasedBottles[$uuid] = (isset($purchasedBottles[$uuid])) ? $purchasedBottles[$uuid] + ($purchasePosition->getCrateAmount() * $purchasePosition->getProduct()->getCrateSize()) : ($purchasePosition->getCrateAmount() * $purchasePosition->getProduct()->getCrateSize());
				} // Is no sub product:
				else {
					$uuid = $this->persistenceManager->getIdentifierByObject($purchasePosition->getProduct());
					$purchasedBottles[$uuid] = (isset($purchasedBottles[$uuid])) ? $purchasedBottles[$uuid] + ($purchasePosition->getCrateAmount() * $purchasePosition->getProduct()->getCrateSize()) : ($purchasePosition->getCrateAmount() * $purchasePosition->getProduct()->getCrateSize());
				}
			}
		}

		// Total loss in euro
		$statement->totalLoss = 0;
		// Unaccounted losses - there was loss in a product, but no purchases
		$statement->unaccountedLoss = 0;

		// Workaround to get uuid of product and bottleAmount for stock fields
		$products = $statement->getProducts();
		for($p = 0; $p < count($products); $p++) {
			$products[$p]->uuid = $this->persistenceManager->getIdentifierByObject($products[$p]);
			// Bottle amount
			if($statement->containsStockWithProduct($products[$p])) {
				$products[$p]->bottleAmount = $statement->containsStockWithProduct($products[$p])->getBottleAmount();
			} else {
				$products[$p]->bottleAmount = 0;
			}
			// Initial bottle amount
			if($statement->getInitialStockStatement()){
				// Check if current or ancestor product is in statement (product might have been edited)
				$products[$p]->initialBottleAmount = 0;
				if($statement->getInitialStockStatement()->containsStockWithProduct($products[$p])) {
					$products[$p]->initialBottleAmount = $statement->getInitialStockStatement()->containsStockWithProduct($products[$p])->getBottleAmount();
				} else if ($products[$p]->getAncestor() && $statement->getInitialStockStatement()->containsStockWithProduct($products[$p]->getAncestor())) {
					$products[$p]->initialBottleAmount = $statement->getInitialStockStatement()->containsStockWithProduct($products[$p]->getAncestor())->getBottleAmount();
				}
			} else {
				$products[$p]->initialBottleAmount = 0;
			}
			// Purchased bottles
			if(array_key_exists($products[$p]->uuid, $purchasedBottles)) {
				$products[$p]->purchasedBottles = $purchasedBottles[$products[$p]->uuid];
			} else {
				$products[$p]->purchasedBottles = 0;
			}
			// Consumption (inkl. Loss)
			$products[$p]->consumption = $products[$p]->purchasedBottles + $products[$p]->initialBottleAmount - $products[$p]->bottleAmount;

			// Actual consumption / sold bottles
			$products[$p]->soldBottles = (isset($consumptionArray['products'][$products[$p]->uuid])) ? $consumptionArray['products'][$products[$p]->uuid] : 0;

			// Loss only if not an initialStockStatement
			$products[$p]->loss = (!$statement->isInitialStockStatement()) ? $products[$p]->consumption - $products[$p]->soldBottles : 0;

			// Loss in euro
			$products[$p]->lossEuro = $products[$p]->loss * $products[$p]->getPrice();

			// Calculate loss per bottle
			if($products[$p]->soldBottles != 0) {
				$products[$p]->lossPerBottle = $products[$p]->lossEuro / $products[$p]->soldBottles;
			} else {
				$products[$p]->lossPerBottle = 0;
				$statement->unaccountedLoss += $products[$p]->lossEuro;
			}
			// Add to total loss
			$statement->totalLoss += $products[$p]->lossEuro;
		}

		return $products;
	}

	/**
	 * Create consumption array consumption[user.uuid][product.uuid] = value
	 * And add info of total bottles consumed by product consumption['products'][product.uuid] = total
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @return array
	 */
	public function createConsumptionArray(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement) {
		$consumptions = $statement->getConsumptions();
		if(count($consumptions) > 0) {
			$consumptionArray = array();
			$consumptionArray['products'] = array();
			foreach($consumptions as $consumption) {
				$userID = $this->persistenceManager->getIdentifierByObject($consumption->getUser());
				$productID = $this->persistenceManager->getIdentifierByObject($consumption->getProduct());
				$consumptionArray[$userID][$productID] = $consumption->getBottleAmount();
				// Add to total consumed amount
				$consumptionArray['products'][$productID] = (array_key_exists($productID, $consumptionArray['products'])) ? $consumptionArray['products'][$productID] + $consumption->getBottleAmount() : $consumption->getBottleAmount();
			}
			return $consumptionArray;
		}
		return NULL;
	}

	/**
	 * Create values array
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement
	 * @param array $consumptionArray The consumption info
	 * @param boolean $addBillingInfo Add info about current statement, old balance, lossfee and new balance
	 */
	public function createValuesArray(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement, $consumptionArray, $addBillingInfo = FALSE) {
		$values = array();
		$statement->newTotalBalance  = 0;
		$statement->totalBill = 0;
		foreach ($statement->getUsers() as $user) {
			$tempArray = array();
			// Get uuid of user
			$userID = $this->persistenceManager->getIdentifierByObject($user);
			// If billing info init current
			if($addBillingInfo) {
				$current = 0;
			}
			foreach ($statement->getProducts() as $product) {
				// Get uuid of product
				$productID = $this->persistenceManager->getIdentifierByObject($product);
				// Get value if present in consumption array
				$value = (isset($consumptionArray[$userID][$productID])) ? $consumptionArray[$userID][$productID] : 0;

				// Add uuid's and value to array
				if($addBillingInfo) {
					$tempArray[] = array(
						'value' => $value
					);
					$current += $value * $product->getPrice();
				} else {
					$tempArray[] = array(
						'userUUID' => $userID,
						'productUUID' => $productID,
						'value' => $value
					);
				}
			}
			// If billingInfo, add current, lossFee, balance old and new
			if($addBillingInfo) {
				$lossFee = 0;
				if(!$statement->isInitialStockStatement()) {
					foreach ($statement->getProducts() as $product) {
						$productID = $this->persistenceManager->getIdentifierByObject($product);
						$value = (isset($consumptionArray[$userID][$productID])) ? $consumptionArray[$userID][$productID] : 0;
						$lossFee += $product->lossPerBottle * $value;
					}
					$lossFee += $statement->unaccountedLoss / count($statement->getUsers());
				}
				// If statement was already billed, get balance from payment
				$oldBalance = (!$statement->isInitialStockStatement() && $statement->getBilled() && $statement->containsPaymentWithUser($user)) ? $statement->containsPaymentWithUser($user)->getBalanceOld() : $user->getBalance();
				$newBalance = (!$statement->isInitialStockStatement() && $statement->getBilled() && $statement->containsPaymentWithUser($user)) ? $statement->containsPaymentWithUser($user)->getBalanceNew() : $user->getBalance() - $current - $lossFee;
				$values[] = array(
					'products' => $tempArray,
					'displayName' => $user->getDisplayName(),
					'current' => $current,
					'oldBalance' => $oldBalance,
					'lossFee' => $lossFee,
					'newBalance' => $newBalance,
					'userUUID' => $userID
				);
				$statement->newTotalBalance += $newBalance;
				$statement->totalBill += $current;
			} else {
				$values[] = array(
					'products' => $tempArray,
					'displayName' => $user->getDisplayName()
				);
			}
		}

		return $values;
	}

	/**
	 * Check purchases for products that are not selected in statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The statement to check
	 * @return array Array with products and purchases they are used in
	 */
	public function checkPurchasesForInactiveProducts(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement) {
		// Active products
		$activeProducts = $statement->getProducts();

		// Purchases with inactive products
		$inactiveProducts = array();

		// Check each purchase
		foreach($statement->getPurchases() as $purchase) {
			// Check each purchase position
			foreach($purchase->getPurchasePositions() as $purchasePosition) {
				// Get product and check if parent or subproduct, we want the parent
				$inactiveProduct = $purchasePosition->getProduct();
				if($inactiveProduct->getParent()) {
					$inactiveProduct = $inactiveProduct->getParent();
				}
				// Check if product of purchasePosition not in activeProducts of this statement
				if(!$activeProducts->contains($inactiveProduct)) {
					// Add product to purchase inactiveProducts array
					$uuid = $this->persistenceManager->getIdentifierByObject($inactiveProduct);
					$uuidPurchase = $this->persistenceManager->getIdentifierByObject($purchase);
					$inactiveProducts[$uuid]['name'] = $inactiveProduct->getName();
					$inactiveProducts[$uuid]['purchases'][$uuidPurchase] = $purchase;
				}
			}
		}

		return $inactiveProducts;
	}
}
?>