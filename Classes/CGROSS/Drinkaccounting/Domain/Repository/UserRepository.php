<?php
namespace CGROSS\Drinkaccounting\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

/**
 * A repository for Users
 *
 * @FLOW3\Scope("singleton")
 */
class UserRepository extends \TYPO3\Flow\Persistence\Repository {

	protected $defaultOrderings = array ('roomNumber' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING);

	/**
	 * Finds all active users
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The posts
	 */
	public function findAll() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('roomNumber' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
					->matching($query->equals('active', 1))
					->execute();
	}

	/**
	 * Finds highest balance users
	 * @param int $limit
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The posts
	 */
	public function findHighest($limit) {
		$query = $this->createQuery();
		return $query->setOrderings(array ('balance' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
					->matching($query->equals('active', 1))
					->setLimit($limit)
					->execute();
	}

	/**
	 * Finds all inactive users
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The posts
	 */
	public function findAllInactive() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('roomNumber' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
					->matching($query->equals('active', 0))
					->execute();
	}

}
?>