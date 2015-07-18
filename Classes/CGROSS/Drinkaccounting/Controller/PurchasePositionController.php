<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition;

/**
 * PurchasePosition controller for the Drinkaccounting package
 *
 * @Flow\Scope("singleton")
 */
class PurchasePositionController extends DefaultController {

	/**
	 * @Flow\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\PurchaseRepository
	 */
	protected $purchaseRepository;

	/**
	 * Adds the given new purchase position object to the purchase
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The purchase that will contain the new purchasePosition
	 * @param \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $newPurchasePosition The new purchase position
	 * @return void
	 */
	public function createAction(\CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase, \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $newPurchasePosition) {
		$purchase->addPurchasePosition($newPurchasePosition);
		$this->purchaseRepository->update($purchase);
		$this->addFlashMessage('Your new purchaseposition was added.');
		$this->redirect('show', 'Purchase', NULL, array('purchase' => $purchase));
	}

	/**
	 * Edits a payment
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The purchase that will contain the new purchasePosition
	 * @param \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition The new purchase position
	 * @return void
	 */
	public function ajaxEditAction(\CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase, \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition) {
		$purchase->updatePurchasePosition($purchasePosition);
		$this->purchaseRepository->update($purchase);

		return true;
	}



	/**
	 * Removes the given purchase position object from the purchase
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The purchase
	 * @param \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition The removed purchase position
	 * @return void
	 */
	public function removeAction(\CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase, \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition) {
		$purchase->removePurchasePosition($purchasePosition);
		$this->purchaseRepository->update($purchase);
		$this->addFlashMessage('Your new purchaseposition was removed.');
		$this->redirect('show', 'Purchase', NULL, array('purchase' => $purchase));
	}
}
?>