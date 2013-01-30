<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Purchase
 *
 * @FLOW3\Entity
 */
class Purchase {

	/**
	 * The statement
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Statement
	 * @ORM\ManyToOne(inversedBy="pruchases")
	 */
	protected $statement;

	/**
	 * The date
	 * @var \DateTime
	 * @ORM\Column(name="purchasedate")
	 */
	protected $date;

	/**
	 * The invoice number
	 * @var string
	 */
	protected $invoiceNumber;

	/**
	 * The purchase positions
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\PurchasePosition>
	 * @ORM\OneToMany(mappedBy="purchase")
	 */
	protected $purchasePositions;

	/**
	 * The transaction
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Transaction
	 * @ORM\OneToOne
	 */
	protected $transaction;

	/**
	 * The sum
	 * @var double
	 */
	protected $sum;


	/**
	 * Get the Purchase's statement
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Statement The Purchase's statement
	 */
	public function getStatement() {
		return $this->statement;
	}

	/**
	 * Sets this Purchase's statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statement $statement The Purchase's statement
	 * @return void
	 */
	public function setStatement(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement = NULL) {
		$this->statement = $statement;
	}

	/**
	 * Get the Purchase's date
	 *
	 * @return \DateTime The Purchase's date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Sets this Purchase's date
	 *
	 * @param \DateTime $date The Purchase's date
	 * @return void
	 */
	public function setDate($date) {
		$this->date = $date;
	}

	/**
	 * Get the Purchase's invoice number
	 *
	 * @return string The Purchase's invoice number
	 */
	public function getInvoiceNumber() {
		return $this->invoiceNumber;
	}

	/**
	 * Sets this Purchase's invoice number
	 *
	 * @param string $invoiceNumber The Purchase's invoice number
	 * @return void
	 */
	public function setInvoiceNumber($invoiceNumber) {
		$this->invoiceNumber = $invoiceNumber;
	}

	/**
	 * Add purchasePosition
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $newPurchasePosition The new position to add
	 * @return void
	 */
	public function addPurchasePosition(\CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $newPurchasePosition) {
		$newPurchasePosition->setPurchase($this);
		$this->purchasePositions->add($newPurchasePosition);
	}

	/**
	 * Remove purchasePosition
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition The new position to add
	 * @return void
	 */
	public function removePurchasePosition(\CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition) {
		$purchasePositions = $this->getPurchasePositions();
		$purchasePositions->removeElement($purchasePosition);
		$this->setPurchasePositions($purchasePositions);
	}

	/**
	 * Updates a purchasePosition from this purchase
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition
	 * @return void
	 */
	 public function updatePurchasePosition(\CGROSS\Drinkaccounting\Domain\Model\PurchasePosition $purchasePosition) {
		$purchasePositions = $this->getPurchasePositions();
		$purchasePositions->set($this->getPurchasePositions()->indexOf($purchasePosition), $purchasePosition);
		$this->setPurchasePositions($purchasePositions);
	 }

	/**
	 * Get the Purchase's purchase positions
	 *
	 * @return \Doctrine\Common\Collections\Collection The Purchase's purchase positions
	 */
	public function getPurchasePositions() {
		return $this->purchasePositions;
	}

	/**
	 * Sets this Purchase's purchase positions
	 *
	 * @param \Doctrine\Common\Collections\Collection $purchasePositions The Purchase's purchase positions
	 * @return void
	 */
	public function setPurchasePositions(\Doctrine\Common\Collections\Collection $purchasePositions) {
		$this->purchasePositions = $purchasePositions;
	}

	/**
	 * Get the Purchase's transaction
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Transaction The Purchase's transaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	/**
	 * Sets this Purchase's transaction
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction The Purchase's transaction
	 * @return void
	 */
	public function setTransaction(\CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction) {
		$this->transaction = $transaction;
	}

	/**
	 * Get the Purchase's sum
	 *
	 * @return double The Purchase's sum
	 */
	public function getSum() {
		return $this->sum;
	}

	/**
	 * Sets this Purchase's sum
	 *
	 * @param double $sum The Purchase's sum
	 * @return void
	 */
	public function setSum($sum) {
		$this->sum = $sum;
	}

	/**
	 * The display name: invoiceNumber - sum
	 * @return string
	 */
	public function getDisplayName() {
		return 'Nr.: '. $this->invoiceNumber.' ('. $this->getDate()->format('Y-m-d').', '.number_format($this->sum, 2, ',', '.').' €)';
	}

}
?>