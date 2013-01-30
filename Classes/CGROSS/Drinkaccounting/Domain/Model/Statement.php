<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Statement
 *
 * @FLOW3\Entity
 */
class Statement {

	/**
	 * The title
	 * @var string
	 */
	protected $title;

	/**
	 * The products
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Product>
	 * @ORM\ManyToMany
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $products;

	/**
	 * The payments
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Payment>
	 * @ORM\OneToMany(mappedBy="statement")
	 */
	protected $payments;

	/**
	 * The users
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\User>
	 * @ORM\ManyToMany(inversedBy="statement")
	 * @ORM\OrderBy({"roomNumber" = "ASC"})
	 */
	protected $users;

	/**
	 * The stocks
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Stock>
	 * @ORM\OneToMany(mappedBy="statement")
	 */
	protected $stocks;

	/**
	 * The purchases
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Purchase>
	 * @ORM\OneToMany(mappedBy="statement")
	 */
	protected $purchases;

	/**
	 * The consumptions
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Consumption>
	 * @ORM\OneToMany(mappedBy="statement")
	 */
	protected $consumptions;

	/**
	 * The initial stock statement
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Statement
	 * @ORM\OneToOne
	 */
	protected $initialStockStatement;


	/**
	 * The date start
	 * @var \DateTime
	 */
	protected $dateStart;

	/**
	 * The date stop
	 * @var \DateTime
	 */
	protected $dateStop;

	/**
	 * The billed
	 * @var boolean
	 */
	protected $billed;

	/**
	 * The current step of the statement
	 * @var int
	 */
	protected $step;

	/**
	 * The current controller of the statement
	 * @var string
	 */
	protected $stepController;

	/**
	 * Get the Statement's title
	 *
	 * @return string The Statement's title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets this Statement's title
	 *
	 * @param string $title The Statement's title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Get the Statement's products
	 *
	 * @return \Doctrine\Common\Collections\Collection The Statement's products
	 */
	public function getProducts() {
		return $this->products;
	}

	/**
	 * Sets this Statement's products
	 *
	 * @param \Doctrine\Common\Collections\Collection $products The Statement's products
	 * @return void
	 */
	public function setProducts(\Doctrine\Common\Collections\Collection $products) {
		$this->products = $products;
	}

	/**
	 * Add product to statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product
	 * @return void
	 */
	public function addProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$this->products->add($product);
	}

	/**
	 * Check if contains product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product
	 * @return boolean
	 */
	public function containsProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		return $this->products->contains($product);
	}

	/**
	 * Clear products
	 *
	 * @return void
	 */
	public function clearProducts() {
		$this->products->clear();
	}

	/**
	 * Get the Statement's payments
	 *
	 * @return \Doctrine\Common\Collections\Collection The Statement's payments
	 */
	public function getPayments() {
		return $this->payments;
	}

	/**
	 * Sets this Statement's payments
	 *
	 * @param \Doctrine\Common\Collections\Collection $payments The Statement's payments
	 * @return void
	 */
	public function setPayments(\Doctrine\Common\Collections\Collection $payments) {
		$this->payments = $payments;
	}

	/**
	 * Adds a payment to this statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Payment $payment
	 * @return void
	 */
	public function addPayment(\CGROSS\Drinkaccounting\Domain\Model\Payment $payment) {
		$payment->setStatement($this);
		$this->payments->add($payment);
	}

	/**
	 * Check if contains payment about user
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Payment
	 */
	public function containsPaymentWithUser(\CGROSS\Drinkaccounting\Domain\Model\User $user) {
		$result = NULL;
		foreach ($this->payments as $payment) {
			if($payment->getUser() == $user) {
				$result = $payment;
			}
		}
		return $result;
	}

	/**
	 * Add purchase to statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase
	 * @return void
	 */
	public function addPurchase(\CGROSS\Drinkaccounting\Domain\Model\Purchase $purchase) {
		$this->purchases->add($purchase);
		$purchase->setStatement($this);
	}



	/**
	 * Clear purchases
	 *
	 * @return void
	 */
	public function clearPurchases() {
		$this->purchases->clear();
	}

	/**
	 * Get the Statement's users
	 *
	 * @return \Doctrine\Common\Collections\Collection The Statement's users
	 */
	public function getUsers() {
		return $this->users;
	}

	/**
	 * Sets this Statement's users
	 *
	 * @param \Doctrine\Common\Collections\Collection $users The Statement's users
	 * @return void
	 */
	public function setUsers(\Doctrine\Common\Collections\Collection $users) {
		$this->users = $users;
	}

	/**
	 * Add user to statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user
	 * @return void
	 */
	public function addUser(\CGROSS\Drinkaccounting\Domain\Model\User $user) {
		$this->users->add($user);
	}

	/**
	 * Clear users
	 *
	 * @return void
	 */
	public function clearUsers() {
		$this->users->clear();
	}

	/**
	 * Get the Statement's stocks
	 *
	 * @return \Doctrine\Common\Collections\Collection The Statement's stocks
	 */
	public function getStocks() {
		return $this->stocks;
	}

	/**
	 * Sets this Statement's stocks
	 *
	 * @param \Doctrine\Common\Collections\Collection $stocks The Statement's stocks
	 * @return void
	 */
	public function setStocks(\Doctrine\Common\Collections\Collection $stocks) {
		$this->stocks = $stocks;
	}

	/**
	 * Remove stock from statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Stock $stock
	 * @return void
	 */
	public function removeStock(\CGROSS\Drinkaccounting\Domain\Model\Stock $stock) {
		$this->stocks->removeElement($stock);
	}

	/**
	 * Add stock to statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Stock $stock
	 * @return void
	 */
	public function addStock(\CGROSS\Drinkaccounting\Domain\Model\Stock $stock) {
		$this->stocks->add($stock);
		$stock->setStatement($this);
	}

	/**
	 * Check if contains stock about product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Stock
	 */
	public function containsStockWithProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		$result = NULL;
		foreach ($this->stocks as $stock) {
			if($stock->getProduct() == $product) {
				$result = $stock;
			}
		}
		return $result;
	}

	/**
	 * Clear stocks
	 *
	 * @return void
	 */
	public function clearStocks() {
		$this->stocks->clear();
	}

	/**
	 * Get the Statement's purchases
	 *
	 * @return \Doctrine\Common\Collections\Collection The Statement's purchases
	 */
	public function getPurchases() {
		return $this->purchases;
	}

	/**
	 * Sets this Statement's purchases
	 *
	 * @param \Doctrine\Common\Collections\Collection $purchases The Statement's purchases
	 * @return void
	 */
	public function setPurchases(\Doctrine\Common\Collections\Collection $purchases = NULL) {
		$this->purchases = $purchases;
	}

	/**
	 * Get the Statement's consumptions
	 *
	 * @return \Doctrine\Common\Collections\Collection The Statement's consumptions
	 */
	public function getConsumptions() {
		return $this->consumptions;
	}

	/**
	 * Sets this Statement's consumptions
	 *
	 * @param \Doctrine\Common\Collections\Collection $consumptions The Statement's consumptions
	 * @return void
	 */
	public function setConsumptions(\Doctrine\Common\Collections\Collection $consumptions) {
		$this->consumptions = $consumptions;
	}

	/**
	 * Add consumption to statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Consumption $consumption
	 * @return void
	 */
	public function addConsumption(\CGROSS\Drinkaccounting\Domain\Model\Consumption $consumption) {
		$this->consumptions->add($consumption);
		$consumption->setStatement($this);
	}

	/**
	 * Remove consumption from statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Consumption $consumption
	 * @return void
	 */
	public function removeConsumption(\CGROSS\Drinkaccounting\Domain\Model\Consumption $consumption) {
		$this->consumptions->removeElement($consumption);
	}

	/**
	 * Clear consumptions
	 *
	 * @return void
	 */
	public function clearConsumptions() {
		$this->consumptions->clear();
	}


	/**
	 * Get initial stock statement
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Statement The Purchase's statement
	 */
	public function getInitialStockStatement() {
		return $this->initialStockStatement;
	}

	/**
	 * Sets initial stock statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The Purchase's statement
	 * @return void
	 */
	public function setInitialStockStatement(\CGROSS\Drinkaccounting\Domain\Model\Statement $initialStockStatement = NULL) {
		$this->initialStockStatement = $initialStockStatement;
	}

	/**
	 * Get the Statement's date start
	 *
	 * @return \DateTime The Statement's date start
	 */
	public function getDateStart() {
		return $this->dateStart;
	}

	/**
	 * Sets this Statement's date start
	 *
	 * @param \DateTime $dateStart The Statement's date start
	 * @return void
	 */
	public function setDateStart($dateStart) {
		$this->dateStart = $dateStart;
	}

	/**
	 * Get the Statement's date stop
	 *
	 * @return \DateTime The Statement's date stop
	 */
	public function getDateStop() {
		return $this->dateStop;
	}

	/**
	 * Sets this Statement's date stop
	 *
	 * @param \DateTime $dateStop The Statement's date stop
	 * @return void
	 */
	public function setDateStop($dateStop) {
		$this->dateStop = $dateStop;
	}

	/**
	 * Get the Statement's billed
	 *
	 * @return boolean The Statement's billed
	 */
	public function getBilled() {
		return $this->billed;
	}

	/**
	 * Sets this Statement's billed
	 *
	 * @param boolean $billed The Statement's billed
	 * @return void
	 */
	public function setBilled($billed) {
		$this->billed = $billed;
	}

	/**
	 * Set step of statement
	 * @param int The step
	 * @return void
	 */
	public function setStep($step) {
		$this->step = $step;
	}

	/**
	 * Get step of statement
	 * @return int
	 */
	public function getStep() {
		return $this->step;
	}

	/**
	 * Set stepController of statement
	 * @param string Name of the controller
	 * @return void
	 */
	public function setStepController($stepController) {
		$this->stepController = $stepController;
	}

	/**
	 * Get stepController of statement
	 * @return string Name of the controller
	 */
	public function getStepController() {
		return $this->stepController;
	}

	/**
	 * Get array off all purchased crates wiht key = product uuid
	 *
	 * @return array
	 */
	public function calculatePurchasedCrates() {
		$crates = array();
		foreach ($this->getPurchases() as $purchase) {
			foreach ($purchase->getPurchasePositions() as $purchasePosition) {
				$crates[$purchasePosition->FLOW3_Persistence_Identifier] = $purchasePosition->getCrateAmount();
			}
		}
		\TYPO3\Flow\var_dump($crates);
		return $crates;
	}

	/**
	 * Is this an initial stock statement ?
	 * True if statement has no initialStockStatement and no users assigned
	 *
	 * @return boolean
	 */
	public function isInitialStockStatement() {
		if(!$this->getInitialStockStatement() && count($this->getUsers()) == 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Getter isInitialStockStatement for view
	 *
	 * @return boolean
	 */
	public function getIsInitialStockStatement() {
		return $this->isInitialStockStatement();
	}
}
?>