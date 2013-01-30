<?php
namespace CGROSS\Drinkaccounting\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;

/**
 * A repository for Purchases
 *
 * @FLOW3\Scope("singleton")
 */
class PurchaseRepository extends \TYPO3\Flow\Persistence\Repository {

	protected $defaultOrderings = array ('date' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING);

	/**
	 *
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
 	 * Collect purchases the product was used in
	 *
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Purchase>
	 */
	protected $usedInPurchases;

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
	 * Finds all purchases not assigne to a statement except the given statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The whitelisted statement
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The posts
	 */
	public function findAllWithoutStatement(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement) {
		$query = $this->createQuery();
		return $query->setOrderings(array ('date' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
					->matching(
						$query->logicalOr(
							$query->equals('statement', NULL),
							$query->equals('statement', $statement)
						)
					)
					->execute();
	}

	/**
	 * Checks if a purchase contains a given product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product we want to check for
	 * @return boolean
	 */
	public function productUsed(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$query = $this->createQuery();
		$result = $query->setOrderings(array ('date' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->execute();
		$purchases = new \Doctrine\Common\Collections\ArrayCollection($result->toArray());

		// Check each purchaseposition
		foreach ($purchases as $purchase) {
			foreach($purchase->getPurchasePositions() as $purchasePosition) {
				if($this->persistenceManager->getIdentifierByObject($purchasePosition->getProduct())
					== $this->persistenceManager->getIdentifierByObject($product)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if a purchase contains a given product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product we want to check for
	 * @return boolean
	 */
	public function productUsedInUnbilledPurchase(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$query = $this->createQuery();
		$result = $query->setOrderings(array ('date' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING))
			->execute();
		$purchases = new \Doctrine\Common\Collections\ArrayCollection($result->toArray());

		// Empty from possible earlier runs
		if($this->usedInPurchases) {
			$this->usedInPurchases->clear();
		} else {
			$this->usedInPurchases = new \Doctrine\Common\Collections\ArrayCollection();
		}


		// Check each purchaseposition
		foreach ($purchases as $purchase) {
			// Check if purchase is assigned to no statement or an unbilled statement
			if(!$purchase->getStatement() || !$purchase->getStatement()->getBilled()) {
				foreach($purchase->getPurchasePositions() as $purchasePosition) {
					if($this->persistenceManager->getIdentifierByObject($purchasePosition->getProduct())
						== $this->persistenceManager->getIdentifierByObject($product)) {
							// Add to purchase list
							$this->usedInPurchases->add($purchase);
					}
				}
			}
		}

		// If used, return true
		if($this->usedInPurchases->count() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Get used purchases from latest run
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getUsedPurchases() {
		return ($this->usedInPurchases) ? $this->usedInPurchases : NULL;
	}
}
?>