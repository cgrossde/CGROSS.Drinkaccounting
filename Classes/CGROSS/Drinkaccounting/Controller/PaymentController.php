<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Payment controller for the Drinkaccounting package
 *
 * @Flow\Scope("singleton")
 */
class PaymentController extends DefaultController {

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

	public function initializeCreateAction() {
	    $this->arguments['newPayment']
	        ->getPropertyMappingConfiguration()
	        ->forProperty('date')
	        ->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\DateTimeConverter', \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT, 'Y-m-d');
	}


	/**
	 * Creates a new payment
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user which will contain the new payment
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account where the transaction is made
	 * @param  \CGROSS\Drinkaccounting\Domain\Model\Payment $newPayment A fresh Payment object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\CGROSS\Drinkaccounting\Domain\Model\User $user, \CGROSS\Drinkaccounting\Domain\Model\Account $account, \CGROSS\Drinkaccounting\Domain\Model\Payment $newPayment) {
		// Create new transaction
		$newTransaction = new \CGROSS\Drinkaccounting\Domain\Model\Transaction();
		$newTransaction->setDate($newPayment->getDate());
		$newTransaction->setSum($newPayment->getSum());
		$newTransaction->setDesc('<b>'.$user->getDisplayName().':</b> '.$newPayment->getDesc());
		$newTransaction->setDeletable(false);	// It would destroy data integrity
		$account->addTransaction($newTransaction);
		$this->accountRepository->update($account);
		$newPayment->setTransaction($newTransaction);

		// Add payment
		$user->addPayment($newPayment);
		$this->userRepository->update($user);

		$this->addFlashMessage('Your new payment was created.'.$account->getName());
		$this->redirect('show', 'User', NULL, array('user' => $user));
	}


	/**
	 * Edits a payment
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user which will contain the new payment
	 * @param  \CGROSS\Drinkaccounting\Domain\Model\Payment $payment A fresh Payment object which has not yet been added to the repository
	 * @return string
	 */
	public function ajaxEditAction(\CGROSS\Drinkaccounting\Domain\Model\User $user, \CGROSS\Drinkaccounting\Domain\Model\Payment $payment) {
		$user->updatePayment($payment);
		$this->userRepository->update($user);

		return true;

	}

	/**
	 * Cancels payment
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user from which the payment will be deleted
	 * @param  \CGROSS\Drinkaccounting\Domain\Model\Payment $payment The payment to delete
	 * @return void
	 */
	public function cancelAction(\CGROSS\Drinkaccounting\Domain\Model\User $user, \CGROSS\Drinkaccounting\Domain\Model\Payment $payment) {
		if($payment->getDeletable()) {
			// Cancel payment
			$cancelPayment = $user->cancelPayment($payment);

			// Make updates
			$this->accountRepository->update($cancelPayment->getTransaction()->getAccount());
			$this->userRepository->update($user);
			// Add flashmessage and redirect
			$this->addFlashMessage('The Payment was deleted');
			$this->redirect('show', 'User', NULL, array('user' => $user));
		} else {
			$this->addFlashMessage('ERROR: You can\'t cancel a cancelled payment');
			$this->redirect('show', 'User', NULL, array('user' => $user));
		}
	}

}

?>