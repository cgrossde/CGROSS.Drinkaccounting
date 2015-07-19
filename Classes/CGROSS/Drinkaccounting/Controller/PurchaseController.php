<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \CGROSS\Drinkaccounting\Domain\Model\Purchase;

/**
 * Purchase controller for the Drinkaccounting package
 *
 * @Flow\Scope("singleton")
 */
class PurchaseController extends DefaultController {

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\PurchaseRepository
	 */
	protected $purchaseRepository;

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

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

	public function initializeCreateAction() {
	    $this->arguments['newPurchase']
	        ->getPropertyMappingConfiguration()
	        ->forProperty('date')
	        ->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
	        	\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
	        	'Y-m-d'
			);
	}

	public function initializeUpdateAction() {
		$this->arguments['purchase']
	        ->getPropertyMappingConfiguration()
	        ->forProperty('date')
	        ->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
	        	\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
	        	'Y-m-d'
			);
	}

	public function initializeAjaxUpdateAction() {
		$this->arguments['purchase']
	        ->getPropertyMappingConfiguration()
	        ->forProperty('date')
	        ->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
	        	\TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
	        	'Y-m-d'
			);
	}

	/**
	 * Shows a list of purchases
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('purchases', $this->purchaseRepository->findAll());
	}

	/**
	 * Shows a single purchase object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The purchase to show
	 * @return void
	 */
	public function showAction(Purchase $purchase) {
		// Don't edit if statement was billed
		if($purchase->getStatement() && $purchase->getStatement()->getBilled()) {
			$this->view->assign('edit', FALSE);
		} else {
			$this->view->assign('edit', TRUE);
		}

		$purchasePositions = $purchase->getPurchasePositions();
		for($p = 0; $p < count($purchasePositions); $p++) {
			$purchasePositions[$p]->bottleAmount = $purchasePositions[$p]->getProduct()->getCrateSize() * $purchasePositions[$p]->getCrateAmount();
		}
		$purchase->setPurchasePositions($purchasePositions);
		$this->view->assign('products', $this->productRepository->findAllWithSubproducts());
		$this->view->assign('purchase', $purchase);
	}

	/**
	 * Shows a form for creating a new purchase object
	 *
	 * @return void
	 */
	public function newAction() {
		$this->view->assign('statements', $this->statementRepository->findAllUnbilled());
		$this->view->assign('accounts', $this->accountRepository->findAll());
	}

	/**
	 * Adds the given new purchase object to the purchase repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $newPurchase A new purchase to add
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account where the transaction is made
	 * @return void
	 */
	public function createAction(Purchase $newPurchase, \CGROSS\Drinkaccounting\Domain\Model\Account $account) {
		// Create new transaction for purchase
		$newTransaction = new \CGROSS\Drinkaccounting\Domain\Model\Transaction();
		$newTransaction->setDate($newPurchase->getDate());
		$newTransaction->setSum(-$newPurchase->getSum());
		$newTransaction->setDesc('<b>Purchase:</b> '.$newPurchase->getInvoiceNumber());
		$newTransaction->setDeletable(false);	// It would destroy data integrity
		$account->addTransaction($newTransaction);
		$this->accountRepository->update($account);
		$newPurchase->setTransaction($newTransaction);

		$this->purchaseRepository->add($newPurchase);
		$this->addFlashMessage('Created a new purchase.');
		$this->redirect('show', 'Purchase', NULL, array('purchase' => $newPurchase));
	}
	
	/**
	 * Updates the given purchase object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The purchase to update
	 * @return void
	 */
	public function ajaxUpdateAction(Purchase $purchase) {
		$this->purchaseRepository->update($purchase);
		return ($this->arguments['purchase']->isValid()) ? "VALID" : "INVALID";
	}

	/**
	 * Removes the given purchase object from the purchase repository and makes cancel transaction
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The purchase to delete
	 * @return void
	 */
	public function deleteAction(Purchase $purchase) {
		if($purchase->getStatement() == NULL){
			// @TODO Get correspoding transaction and decouple from purchase

			// @TODO Create storno purchase


			$this->purchaseRepository->remove($purchase);
			$this->persistenceManager->persistAll();
			$this->addFlashMessage('Deleted a purchase.');
		} else {
			$this->addFlashMessage('Could not delete purchase. Purchase alread assigned to statement.');
		}

		$this->redirect('index');
	}

}

?>