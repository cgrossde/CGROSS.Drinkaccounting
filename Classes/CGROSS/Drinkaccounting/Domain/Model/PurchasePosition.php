<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Purchase position
 *
 * @Flow\Entity
 */
class PurchasePosition {

	/**
	 * The purchase
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Purchase
	 * @ORM\ManyToOne(inversedBy="purchasePositions")
	 */
	protected $purchase;

	/**
	 * The product
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Product
	 * @ORM\ManyToOne
	 */
	protected $product;

	/**
	 * The crate amount
	 * @var integer
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $crateAmount;


	/**
	 * Get the Purchase position's purchase
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Purchase The Purchase position's purchase
	 */
	public function getPurchase() {
		return $this->purchase;
	}

	/**
	 * Sets this Purchase position's purchase
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase The Purchase position's purchase
	 * @return void
	 */
	public function setPurchase(\CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase) {
		$this->purchase = $purchase;
	}

	/**
	 * Get the Purchase position's product
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Product The Purchase position's product
	 */
	public function getProduct() {
		return $this->product;
	}

	/**
	 * Sets this Purchase position's product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The Purchase position's product
	 * @return void
	 */
	public function setProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$this->product = $product;
	}

	/**
	 * Get the Purchase position's crate amount
	 *
	 * @return integer The Purchase position's crate amount
	 */
	public function getCrateAmount() {
		return $this->crateAmount;
	}

	/**
	 * Sets this Purchase position's crate amount
	 *
	 * @param integer $crateAmount The Purchase position's crate amount
	 * @return void
	 */
	public function setCrateAmount($crateAmount) {
		$this->crateAmount = $crateAmount;
	}

}
?>