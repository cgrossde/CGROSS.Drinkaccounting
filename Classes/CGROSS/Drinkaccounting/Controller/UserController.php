<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use \CGROSS\Drinkaccounting\Domain\Model\User;

/**
 * User controller for the Drinkaccounting package
 *
 * @FLOW3\Scope("singleton")
 */
class UserController extends DefaultController {

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * Shows a list of users
	 *
	 * @return void
	 */
	public function indexAction() {
		$users =  $this->userRepository->findAll();
		$balanceSum = 0;
		foreach ($users as $user) {
			$balanceSum += $user->getBalance();
		}
		$this->view->assign('users', $users);
		$this->view->assign('balanceSum', $balanceSum);
	}

	/**
	 * Shows a list of users
	 *
	 * @return void
	 */
	public function inactiveAction() {
		$users =  $this->userRepository->findAllInactive();
		$balanceSum = 0;
		foreach ($users as $user) {
			$balanceSum += $user->getBalance();
		}
		$this->view->assign('users', $users);
		$this->view->assign('balanceSum', $balanceSum);
	}

	/**
	 * Shows a single user object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user to show
	 * @return void
	 */
	public function showAction(User $user) {
		$this->view->assign('accounts', $this->accountRepository->findAll());
		$this->view->assign('user', $user);
	}

	/**
	 * Shows a form for creating a new user object
	 *
	 * @return void
	 */
	public function newAction() {
	}

	/**
	 * Adds the given new user object to the user repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $newUser A new user to add
	 * @return void
	 */
	public function createAction(User $newUser) {
		$newUser->setActive(TRUE);
		$newUser->setDeposit(0);
		$this->userRepository->add($newUser);
		$this->addFlashMessage('Created a new user.');
		$this->redirect('index');
	}

	/**
	 * Shows a form for editing an existing user object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user to edit
	 * @return void
	 */
	public function editAction(User $user) {
		$this->view->assign('user', $user);
	}

	/**
	 * Updates the given user object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user to update
	 * @return void
	 */
	public function updateAction(User $user) {
		$this->userRepository->update($user);
		$this->addFlashMessage('Updated the user.');
		$this->redirect('index');
	}

	/**
	 * Updates the given user object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user to update
	 * @return void
	 */
	public function ajaxUpdateAction(User $user) {
		$this->userRepository->update($user);

		return "SUCCESS";
	}


	/**
	 * Removes the given user object from the user repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user to delete
	 * @return void
	 */
	public function deleteAction(User $user) {
		$this->userRepository->remove($user);
		$this->addFlashMessage('Deleted a user.');
		$this->redirect('index');
	}

	/**
	 * Activates the given user
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user A new user to activate
	 * @return void
	 */
	public function activateAction(User $user) {
		$user->setActive(TRUE);
		$this->userRepository->update($user);
		$this->addFlashMessage('User "'.$user->getDisplayName().'" has been activated');
		$this->redirect('index');
	}

	/**
	 * Deactivates the given user
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user A new user to activate
	 * @return void
	 */
	public function deactivateAction(User $user) {
		$user->setActive(FALSE);
		$this->userRepository->update($user);
		$this->addFlashMessage('User "'.$user->getDisplayName().'" has been deactivated');
		$this->redirect('inactive');
	}

	/**
	 * Change deposit
	 * If a deposit raises the difference to the old value will also booked on the selected account
	 * If a deposit decreases the difference to the old value will be added to the users balance
	 * After that it can be payed out as a negitve payment
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The user
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account where the transaction is made
	 * @param double $newDeposit Old deposit
	 * @return void
	 */
	public function changeDepositAction(\CGROSS\Drinkaccounting\Domain\Model\User $user, \CGROSS\Drinkaccounting\Domain\Model\Account $account, $newDeposit) {
		// Difference between old and new deposit
		$difference = $user->getDeposit() - $newDeposit;

		// Create new payment for deposit
		$newPayment = new \CGROSS\Drinkaccounting\Domain\Model\Payment();
		$newPayment->setSum($difference);
		$newPayment->setDesc('<b>Deposit</b>');
		$newPayment->setDate(new \DateTime());
		$newPayment->setDeletable(FALSE);

		// If it's a deposit raise:
		if($difference < 0) {
			// Create new transaction for deposit
			$newTransaction = new \CGROSS\Drinkaccounting\Domain\Model\Transaction();
			$newTransaction->setDate($newPayment->getDate());
			$newTransaction->setSum(-$newPayment->getSum());
			$newTransaction->setDesc('<b>'.$user->getDisplayName().':</b> '.$newPayment->getDesc());
			$newTransaction->setDeletable(false);	// It would destroy data integrity
			$account->addTransaction($newTransaction);
			$this->accountRepository->update($account);
			$newPayment->setTransaction($newTransaction);
			// Unbalanced payment, won't affect users balance but the account balance
			$user->addPayment($newPayment, TRUE);
		} else { // The deposit decreases: no booking on account but on user balance
			$user->addPayment($newPayment);
		}

		// Set deposit
		$user->setDeposit($newDeposit);
		$this->userRepository->update($user);

		$this->addFlashMessage('The deposit was updated.'.$account->getName());
		$this->redirect('show', 'User', NULL, array('user' => $user));
	}
}

?>