<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

/**
 * Overview controller for the Drinkaccounting package
 *
 * @FLOW3\Scope("singleton")
 */
class OverviewController extends DefaultController {

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\PurchaseRepository
	 */
	protected $purchaseRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\StatementRepository
	 */
	protected $statementRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {

		// Assing view data
		$this->view->assign('accounts', $this->accountRepository->findAll());
		$this->view->assign('users', $this->userRepository->findHighest(5));
		$this->view->assign('statements', $this->statementRepository->findLatest(5));


	}

}

?>