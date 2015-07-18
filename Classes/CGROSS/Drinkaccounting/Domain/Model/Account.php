<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Account
 *
 * @Flow\Entity
 */
class Account {

	/**
	 * The name
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $name;

	/**
	 * The balance
	 * @var double
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $balance;

	/**
	 * The transactions
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Transaction>
	 * @ORM\OneToMany(mappedBy="account")
	 * @ORM\OrderBy({"creationDate" = "DESC"})
	 */
	protected $transactions;


	/**
	 * Get the Account's name
	 *
	 * @return string The Account's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets this Account's name
	 *
	 * @param string $name The Account's name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get the Account's balance
	 *
	 * @return double The Account's balance
	 */
	public function getBalance() {
		return $this->balance;
	}

	/**
	 * Sets this Account's balance
	 *
	 * @param double $balance The Account's balance
	 * @return void
	 */
	public function setBalance($balance) {
		$this->balance = $balance;
	}

	/**
	 * Adds a transaction to this account
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction
	 * @return void
	 */
	public function addTransaction(\CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction) {
		// Calc transaction balance
		$transaction->setBalanceOld($this->getBalance());
		$transaction->setBalanceNew($this->getBalance() + $transaction->getSum());
		// Calc new account balance
		$this->setBalance($this->getBalance() + $transaction->getSum());
		$transaction->setAccount($this);
		$this->transactions->add($transaction);
	}

	/**
	 * Removes a transaction from this account
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Transaction The cancel transaction
	 */
	public function cancelTransaction(\CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction) {
		// Create cancel transaction
		$cancelTransaction = new Transaction();
		$cancelTransaction->setDate(new \DateTime());
		$cancelTransaction->setSum(-$transaction->getSum());
		$cancelTransaction->setDesc('<b>CANCELED ('.$transaction->getDate()->format('Y-m-d').')</b> - '.$transaction->getDesc());
		$cancelTransaction->setDeletable(false);	// Don't cancel a cancellation
		// Update desc of initial transaction
		$transaction->setDesc('<b>CANCELED</b> - '.$transaction->getDesc());
		$transaction->setDeletable(false); // Don't cancel a cancellation
		// Update and add transactions
		$this->updateTransaction($transaction);
		$this->addTransaction($cancelTransaction);
		return $cancelTransaction;
	}

	/**
	 * Updates a transaction from this account
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction
	 * @return void
	 */
	 public function updateTransaction(\CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction) {
		$transactions = $this->getTransactions();
		$transactions->set($this->getTransactions()->indexOf($transaction), $transaction);
		$this->setTransactions($transactions);
	 }

	/**
	 * Get the Account's transactions
	 *
	 * @return \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Transaction> The Account's transactions
	 */
	public function getTransactions() {
		return $this->transactions;
	}

	/**
	 * Get the last ... Account's transactions
	 *
	 * @param int $number
	 * @return \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Transaction> The Account's transactions
	 */
	public function getLastTransactions($number) {
		return new \Doctrine\Common\Collections\ArrayCollection($this->transactions->slice(0,$number));
	}

	/**
	 * Sets this Account's transactions
	 *
	 * @param \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Transaction> $transactions The Account's transactions
	 * @return void
	 */
	public function setTransactions(\Doctrine\Common\Collections\Collection $transactions) {
		$this->transactions = $transactions;
	}

}
?>