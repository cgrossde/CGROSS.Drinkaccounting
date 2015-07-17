<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

use \CGROSS\Drinkaccounting\Domain\Model\Account;

/**
 * Account controller for the Drinkaccounting package
 *
 * @FLOW3\Scope("singleton")
 */
class AccountController extends DefaultController {

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * Shows a list of accounts
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('accounts', $this->accountRepository->findAll());
	}

	/**
	 * Shows a single account object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account to show
	 * @return void
	 */
	public function showAction(Account $account) {
		$account->setTransactions($account->getLastTransactions(30));
		// Get accounts except current account
		$accounts = new \Doctrine\Common\Collections\ArrayCollection($this->accountRepository->findAll()->toArray());
		$accounts->removeElement($account);

		$this->view->assign('accounts', $accounts);
		//$this->view->assign('accounts', $this->accountRepository->findAllExcept($account)); // @TODO Not working ...
		$this->view->assign('account', $account);
	}

	/**
	 * Shows a form for creating a new account object
	 *
	 * @return void
	 */
	public function newAction() {
	}

	/**
	 * Adds the given new account object to the account repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $newAccount A new account to add
	 * @return void
	 */
	public function createAction(Account $newAccount) {
		$this->accountRepository->add($newAccount);
		$this->addFlashMessage('Created a new account.');
		$this->redirect('index');
	}

	/**
	 * Updates the given account object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account to update
	 * @return void
	 */
	public function ajaxUpdateAction(Account $account) {
		$this->accountRepository->update($account);
		return ($this->arguments['account']->isValid()) ? "VALID" : "INVALID";
	}

	/**
	 * Removes the given account object from the account repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account to delete
	 * @return void
	 */
	public function deleteAction(Account $account) {
		$this->accountRepository->remove($account);
		$this->addFlashMessage('Deleted a account.');
		$this->redirect('index');
	}

	/**
	 * Rebook from one account to another
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account From account
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $newAccount To account
	 * @param double $sum The amount to book
	 */
	public function rebookAction(\CGROSS\Drinkaccounting\Domain\Model\Account $account, \CGROSS\Drinkaccounting\Domain\Model\Account $newAccount, $sum) {
		// Create new transaction in old account
		$fromTransaction = new \CGROSS\Drinkaccounting\Domain\Model\Transaction();
		$fromTransaction->setDate(new \DateTime);
		$fromTransaction->setDesc('<b>Rebook<b> to '.$newAccount->getName());
		$fromTransaction->setSum(-$sum);
		$fromTransaction->setDeletable(FALSE);
		$account->addTransaction($fromTransaction);

		// Create new transaction in new account
		$toTransaction = new \CGROSS\Drinkaccounting\Domain\Model\Transaction();
		$toTransaction->setDate(new \DateTime);
		$toTransaction->setDesc('<b>Rebook</b> from '.$account->getName());
		$toTransaction->setSum($sum);
		$toTransaction->setDeletable(FALSE);
		$newAccount->addTransaction($toTransaction);

		// Make updates
		$this->accountRepository->update($account);
		$this->accountRepository->update($newAccount);

		$this->addFlashMessage('Rebooked to '.$newAccount->getName().' done.');
		$this->redirect('show', 'Account', NULL, array('account' => $account));
	}
}

?>