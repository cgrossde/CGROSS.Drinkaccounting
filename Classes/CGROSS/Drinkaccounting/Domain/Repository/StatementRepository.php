<?php
namespace CGROSS\Drinkaccounting\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

/**
 * A repository for Statements
 *
 * @FLOW3\Scope("singleton")
 */
class StatementRepository extends \TYPO3\Flow\Persistence\Repository {

	protected $defaultOrderings = array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING);

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @FLOW3\Inject
	 */
	protected $systemLogger;

	/**
	 * Collect statements the product was used in
	 *
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Statement>
	 */
	protected $usedInStatements;

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
		$this -> persistenceManager = $persistenceManager;
	}

	/**
	 * Finds all unbilled statements
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The statements
	 */
	public function findAll() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
					->execute();
	}

	/**
	 * Finds latest statements
	 *
	 * @param int $limit Latest x statement
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The statements
	 */
	public function findLatest($limit) {
		$query = $this->createQuery();
		return $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
					->setLimit($limit)
					->execute();
	}

	/**
	 * Finds all unbilled statements
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The statements
	 */
	public function findAllUnbilled() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
					->matching($query->equals('billed', 0))
					->execute();
	}

	/**
	 * Finds all billed statements
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The statements
	 */
	public function findAllBilled() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
					->matching($query->equals('billed', 1))
					->execute();
	}

	/**
	 * Finds all statements that are not used as initial stock statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $whiteListStatement The statement to whitelist if it is the initial stock statement of the current statement
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The statements
	 */
	public function findAllPossibleInitialStockStatements(\CGROSS\Drinkaccounting\Domain\Model\Statement $whiteListStatement = NULL) {
		$query = $this->createQuery();
		$result = $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
					->execute();
		$statements = new \Doctrine\Common\Collections\ArrayCollection($result->toArray());

		$blackList = array();
		// Get all initial stock statements
		foreach ($statements as $statement) {
			if($statement->getInitialStockStatement()) {
				$blackList[$this->persistenceManager->getIdentifierByObject($statement->getInitialStockStatement())] = $statement->getInitialStockStatement()->getTitle();
			}
		}

		// Filter
		$filteredStatements = new \Doctrine\Common\Collections\ArrayCollection();
		foreach ($statements as $statement) {
			// Only add if not on blacklist
			if(!array_key_exists($this->persistenceManager->getIdentifierByObject($statement), $blackList) && $statement->getBilled()) {
				$filteredStatements->add($statement);
			}
		}

		// Add whitelisted statement
		if($whiteListStatement != NULL) {
			$filteredStatements->add($whiteListStatement);
		}

		return $filteredStatements;
	}

	/**
	 * Checks if a statement contains a given product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product we want to check for
	 * @return boolean
	 */
	public function productUsed(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$query = $this->createQuery();
		$result = $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->execute();
		$statements = new \Doctrine\Common\Collections\ArrayCollection($result->toArray());

		// Check each statement
		foreach ($statements as $statement) {
			if($statement->getProducts()->contains($product)) {
				$this->systemLogger->log("Used as product in statement");
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if an unbilled statement contains a given product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product we want to check for
	 * @return boolean
	 */
	public function productUsedInUnbilledStatement(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$query = $this->createQuery();
		$result = $query->setOrderings(array ('dateStart' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->matching($query->equals('billed', 0))
			->execute();
		$statements = new \Doctrine\Common\Collections\ArrayCollection($result->toArray());

		// Empty from possible earlier runs
		if($this->usedInStatements) {
			$this->usedInStatements->clear();
		} else {
			$this->usedInStatements = new \Doctrine\Common\Collections\ArrayCollection();
		}

		// Check each statement
		foreach ($statements as $statement) {
			if($statement->getProducts()->contains($product)) {
				$this->usedInStatements->add($statement);
			}
		}

		// If there are used Statements return true
		if($this->usedInStatements->count() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Get used statements from latest run
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getUsedStatements() {
		return ($this->usedInStatements) ? $this->usedInStatements : NULL;
	}
}
?>