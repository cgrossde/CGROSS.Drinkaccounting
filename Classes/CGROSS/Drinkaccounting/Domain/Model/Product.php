<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Product
 *
 * @Flow\Entity
 */
class Product {

	/**
	 * The name
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $name;

	/**
	 * The price
	 * @var double
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $price;

	/**
	 * The crate size
	 * @var integer
	 * @Flow\Validate(type="NotEmpty", validationGroups={"addSubproductAction","Default"})
	 * @Flow\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $crateSize;

	/**
	 * The purchase price
	 * @var double
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $purchasePrice;

	/**
	 * The deposit
	 * @var double
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $deposit;

	/**
	 * The active
	 * @var boolean
	 */
	protected $active;

	/**
	 * Is product hidden
	 * @var boolean
	 */
	protected $hidden;

	/**
	 * The parent of the product
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Product
	 * @ORM\ManyToOne
	 */
	protected $parent;

	/**
	 * The ancestor of this product
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Product
	 * @ORM\OneToOne
	 */
	protected $ancestor;

	/**
	 * The product position in product listings
	 * @var integer
	 * @-FLOW3\Validate(type="NumberRange", options={ "minimum" = 0 }, validationGroups={"usedUpdateAction","Default"})
	 */
	protected $position;

	/**
	 * The products width in the consumption list
	 * @var integer
	 * @-FLOW3\Validate(type="NumberRange", options={ "minimum" = 0 }, validationGroups={"usedUpdateActionAction","Default"})
	 */
	protected $colWidth;

	/**
	 * Get the Product's name
	 *
	 * @return string The Product's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets this Product's name
	 *
	 * @param string $name The Product's name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get the Product's price
	 *
	 * @return double The Product's price
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * Sets this Product's price
	 *
	 * @param double $price The Product's price
	 * @return void
	 */
	public function setPrice($price) {
		$this->price = $price;
	}

	/**
	 * Get the Product's crate size
	 *
	 * @return integer The Product's crate size
	 */
	public function getCrateSize() {
		return $this->crateSize;
	}

	/**
	 * Sets this Product's crate size
	 *
	 * @param integer $crateSize The Product's crate size
	 * @return void
	 */
	public function setCrateSize($crateSize) {
		$this->crateSize = $crateSize;
	}

	/**
	 * Get the Product's purchase price
	 *
	 * @return double The Product's purchase price
	 */
	public function getPurchasePrice() {
		return $this->purchasePrice;
	}

	/**
	 * Sets this Product's purchase price
	 *
	 * @param double $purchasePrice The Product's purchase price
	 * @return void
	 */
	public function setPurchasePrice($purchasePrice) {
		$this->purchasePrice = $purchasePrice;
	}

	/**
	 * Get the Product's deposit
	 *
	 * @return double The Product's deposit
	 */
	public function getDeposit() {
		return $this->deposit;
	}

	/**
	 * Sets this Product's deposit
	 *
	 * @param double $deposit The Product's deposit
	 * @return void
	 */
	public function setDeposit($deposit) {
		$this->deposit = $deposit;
	}

	/**
	 * Get the Product's position
	 *
	 * @return integer The Product's position
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * Sets this Product's position
	 *
	 * @param integer $position The Product's position
	 * @return void
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * Get the Product's colWidth
	 *
	 * @return integer The Product's colWidth
	 */
	public function getColWidth() {
		return $this->colWidth;
	}

	/**
	 * Sets this Product's colWidth
	 *
	 * @param integer $colWidth The Product's colWidth
	 * @return void
	 */
	public function setColWidth($colWidth) {
		$this->colWidth = $colWidth;
	}

	/**
	 * Get the Product's active
	 *
	 * @return boolean The Product's active
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * Sets this Product's active
	 *
	 * @param boolean $active The Product's active
	 * @return void
	 */
	public function setActive($active) {
		$this->active = $active;
	}

	/**
	 * Get the Product's hidden
	 *
	 * @return boolean Is product hidden?
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * Sets this Product's hidden
	 *
	 * @param boolean $hidden Hide or unhide product
	 * @return void
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	/**
	 * Get the product's parent
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Product The parent of this product
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Sets this product's parent
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $user The parent of this product
	 * @return void
	 */
	public function setParent(\CGROSS\Drinkaccounting\Domain\Model\Product $parent) {
		$this->parent = $parent;
	}

	/**
	 * Get the product's ancestor
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Product The ancestor of this product
	 */
	public function getAncestor() {
		return $this->ancestor;
	}

	/**
	 * Sets this product's ancestor
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $ancestor The ancestor of this product
	 * @return void
	 */
	public function setAncestor(\CGROSS\Drinkaccounting\Domain\Model\Product $ancestor) {
		$this->ancestor = $ancestor;
	}
}
?>