<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Stock
 *
 * @FLOW3\Entity
 */
class Stock {

	/**
	 * The product
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Product
	 * @ORM\ManyToOne
	 */
	protected $product;

	/**
	 * The bottle amount
	 * @var integer
	 * @FLOW3\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $bottleAmount;

	/**
	 * The statement
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Statement
	 * @ORM\ManyToOne(inversedBy="payments")
	 */
	protected $statement;

	/**
	 * Get the Stock's product
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Product The Stock's product
	 */
	public function getProduct() {
		return $this->product;
	}

	/**
	 * Sets this Stock's product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The Stock's product
	 * @return void
	 */
	public function setProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$this->product = $product;
	}

	/**
	 * Get the Stock's bottle amount
	 *
	 * @return integer The Stock's bottle amount
	 */
	public function getBottleAmount() {
		return $this->bottleAmount;
	}

	/**
	 * Sets this Stock's bottle amount
	 *
	 * @param integer $bottleAmount The Stock's bottle amount
	 * @return void
	 */
	public function setBottleAmount($bottleAmount) {
		$this->bottleAmount = $bottleAmount;
	}

	/**
	 * Get the Stock's statement
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Statement The Payment's statement
	 */
	public function getStatement() {
		return $this->statement;
	}

	/**
	 * Sets this Stock's statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statementn $statement The Payment's statement
	 * @return void
	 */
	public function setStatement(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement) {
		$this->statement = $statement;
	}
}
?>