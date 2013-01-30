<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

/**
 * Transaction controller for the Drinkaccounting package
 *
 * @FLOW3\Scope("singleton")
 */
class TransactionController extends DefaultController {

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

	public function initializeCreateAction() {
	    $this->arguments['newTransaction']
	        ->getPropertyMappingConfiguration()
	        ->forProperty('date')
	        ->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter', \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT, 'Y-m-d');
	}


	/**
	 * Creates a new transaction
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account which will contain the new transaction
	 * @param  \CGROSS\Drinkaccounting\Domain\Model\Transaction $newTransaction A fresh Transaction object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\CGROSS\Drinkaccounting\Domain\Model\Account $account, \CGROSS\Drinkaccounting\Domain\Model\Transaction $newTransaction) {
		// Add transaction
		$account->addTransaction($newTransaction);
		$this->accountRepository->update($account);

		$this->addFlashMessage('Your new transaction was created.');
		$this->redirect('show', 'Account', NULL, array('account' => $account));
	}

	public function initializeEditAction() {
	    $this->arguments['transaction']
	        ->getPropertyMappingConfiguration()
	        ->forProperty('date')
	        ->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter', \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT, 'Y-m-d');
	}

	/**
	 * Edits a transaction
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account which will contain the new transaction
	 * @param  \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction A fresh Transaction object which has not yet been added to the repository
	 * @return string
	 */
	public function editAction(\CGROSS\Drinkaccounting\Domain\Model\Account $account, \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction) {
		$account->updateTransaction($transaction);
		$this->accountRepository->update($account);

		return "SUCCESS";

	}

	/**
	 * Cancels an transaction
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account from which the transaction will be deleted
	 * @param  \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction The transaction to delete
	 * @return void
	 */
	public function cancelAction(\CGROSS\Drinkaccounting\Domain\Model\Account $account, \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction) {
		// Is the transaction cancelable
		if($transaction->getDeletable()) {
			// Cancel transaction
			$account->cancelTransaction($transaction);
			$this->accountRepository->update($account);
			// Add flashmessage and redirect
			$this->addFlashMessage('The Transaction was cancelled');
			$this->redirect('show', 'Account', NULL, array('account' => $account));
		} else {
			$this->addFlashMessage('ERROR: You can\'t cancel this transaction from here. You need to cancel the associated payment or this is already a cancelled transaction.');
			$this->redirect('show', 'Account', NULL, array('account' => $account));
		}
	}

}

?>