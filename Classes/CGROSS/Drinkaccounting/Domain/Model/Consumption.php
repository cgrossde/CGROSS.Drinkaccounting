<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Consumption
 *
 * @Flow\Entity
 */
class Consumption {

	/**
	 * The statement
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Statement
	 * @ORM\ManyToOne(inversedBy="consumptions")
	 */
	protected $statement;

	/**
	 * The date
	 * @var \DateTime
	 * @ORM\Column(name="consumptiondate")
	 */
	protected $date;

	/**
	 * The user
	 * @var \CGROSS\Drinkaccounting\Domain\Model\User
	 * @ORM\ManyToOne
	 */
	protected $user;

	/**
	 * The product
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Product
	 * @ORM\ManyToOne
	 */
	protected $product;

	/**
	 * The bottle amount
	 * @var integer
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="NumberRange", options={ "minimum" = 0 })
	 */
	protected $bottleAmount;


	/**
	 * Get the Consumption's statement
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Statement The Consumption's statement
	 */
	public function getStatement() {
		return $this->statement;
	}

	/**
	 * Sets this Consumption's statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The Consumption's statement
	 * @return void
	 */
	public function setStatement(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement) {
		$this->statement = $statement;
	}

	/**
	 * Get the Consumption's date
	 *
	 * @return \DateTime The Consumption's date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Sets this Consumption's date
	 *
	 * @param \DateTime $date The Consumption's date
	 * @return void
	 */
	public function setDate($date) {
		$this->date = $date;
	}

	/**
	 * Get the Consumption's user
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\User The Consumption's user
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Sets this Consumption's user
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The Consumption's user
	 * @return void
	 */
	public function setUser(\CGROSS\Drinkaccounting\Domain\Model\User $user) {
		$this->user = $user;
	}

	/**
	 * Get the Consumption's product
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Product The Consumption's product
	 */
	public function getProduct() {
		return $this->product;
	}

	/**
	 * Sets this Consumption's product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The Consumption's product
	 * @return void
	 */
	public function setProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$this->product = $product;
	}

	/**
	 * Get the Consumption's bottle amount
	 *
	 * @return integer The Consumption's bottle amount
	 */
	public function getBottleAmount() {
		return $this->bottleAmount;
	}

	/**
	 * Sets this Consumption's bottle amount
	 *
	 * @param integer $bottleAmount The Consumption's bottle amount
	 * @return void
	 */
	public function setBottleAmount($bottleAmount) {
		$this->bottleAmount = $bottleAmount;
	}

	/**
	 * To string
	 *
	 * @return string
	 */
	public function __toString() {
		$result =  ' ## Product: '.$this->product->getName();
		$result .= ' ## User: '.$this->user->getDisplayName();
		$result .= ' ## Statement: '.$this->statement->getTitle();
		$result .= ' ## BottleAmount: '.$this->bottleAmount;
		return $result;
	}

}
?>