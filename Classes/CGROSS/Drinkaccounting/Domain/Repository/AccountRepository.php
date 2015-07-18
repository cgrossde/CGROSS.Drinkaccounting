<?php
namespace CGROSS\Drinkaccounting\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A repository for Accounts
 *
 * @Flow\Scope("singleton")
 */
class AccountRepository extends \TYPO3\Flow\Persistence\Repository {


	/**
	 * Finds all accounts, except the given
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The account that should be ignored
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The statements
	 */
	public function findAllExcept(\CGROSS\Drinkaccounting\Domain\Model\Account $account) {
		$query = $this->createQuery();
		return $query->matching(
					$query->logicalNot(
						$query->equals('account', $account))
					)->execute();
	}

}
?>