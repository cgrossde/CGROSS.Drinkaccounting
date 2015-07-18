<?php
namespace CGROSS\Drinkaccounting\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A repository for Backups
 *
 * @Flow\Scope("singleton")
 */
class BackupRepository extends \TYPO3\Flow\Persistence\Repository {

	/**
	 * Finds all backups, order by date
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The products
	 */
	public function findAll() {
		$query = $this->createQuery();
		return $query->setOrderings(array ('date' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
			->execute();
	}

}
?>