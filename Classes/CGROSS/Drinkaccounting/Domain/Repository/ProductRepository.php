<?php
namespace CGROSS\Drinkaccounting\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

/**
 * A repository for Products
 *
 * @FLOW3\Scope("singleton")
 */
class ProductRepository extends \TYPO3\Flow\Persistence\Repository {

	protected $defaultOrderings = array ('position' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING);

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
	 * Finds all active Products
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The products
	 */
	public function findAll() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('position' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->matching(
			$query->logicalAnd(array(
				$query->equals('active', 1),
				$query->equals('hidden', 0),
				$query->equals('parent', NULL)
			))
		)->execute();
	}

	/**
	 * Finds all Products and Subproducts
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The products
	 */
	public function findAllWithSubproducts() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('position' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->matching(
			$query->logicalAnd(array(
				$query->equals('active', 1),
				$query->equals('hidden', 0),
			))
		)->execute();
	}

	/**
	 * Finds all inactive Products
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The products
	 */
	public function findAllInactive() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('position' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->matching(
				$query->logicalAnd(
					$query->equals('active', 0),
					$query->equals('hidden', 0)
				)
			)->execute();
	}

	/**
	 * Find all subproducts of given product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product with possible subproducts
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The products
	 */
	public function findAllSubproducts(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$query = $this->createQuery();
		return $query->setOrderings(array ('position' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->matching($query->equals('parent', $product))
			->execute();
	}
}
?>