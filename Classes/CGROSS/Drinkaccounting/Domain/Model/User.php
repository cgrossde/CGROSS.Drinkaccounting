<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A User
 *
 * @Flow\Entity
 */
class User {

	/**
	 * The statements
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Statement>
	 * @ORM\ManyToMany(mappedBy="users")
	 */
	protected $statements;

	/**
	 * The name
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $name;

	/**
	 * The surname
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $surname;

	/**
	 * The room number
	 * @var integer
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $roomNumber;

	/**
	 * The nick name
	 * @var string
	 */
	protected $nickName;

	/**
	 * The balance
	 * @var double
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $balance;

	/**
	 * The deposit
	 * @var double
	 */
	protected $deposit;

	/**
	 * The payments
	 * @var \Doctrine\Common\Collections\Collection<\CGROSS\Drinkaccounting\Domain\Model\Payment>
	 * @ORM\OneToMany(mappedBy="user")
	 * @ORM\OrderBy({"creationDate" = "ASC"})
	 */
	protected $payments;

	/**
	 * The active
	 * @var boolean
	 */
	protected $active;

	/**
	 * Get the Payment's user
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\User The Payment's user
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Sets this Payment's user
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\User $user The Payment's user
	 * @return void
	 */
	public function setUser(\CGROSS\Drinkaccounting\Domain\Model\User $user) {
		$this->user = $user;
	}

	/**
	 * Get the User's statements
	 *
	 * @return \Doctrine\Common\Collections\Collection The User's statements
	 */
	public function getStatements() {
		return $this->statements;
	}

	/**
	 * Sets this User's statements
	 *
	 * @param \Doctrine\Common\Collections\Collection $statements The User's statements
	 * @return void
	 */
	public function setStatements(\Doctrine\Common\Collections\Collection $statements) {
		$this->statements = $statements;
	}

	/**
	 * Get the User's name
	 *
	 * @return string The User's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets this User's name
	 *
	 * @param string $name The User's name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get the User's surname
	 *
	 * @return string The User's surname
	 */
	public function getSurname() {
		return $this->surname;
	}

	/**
	 * Sets this User's surname
	 *
	 * @param string $surname The User's surname
	 * @return void
	 */
	public function setSurname($surname) {
		$this->surname = $surname;
	}

	/**
	 * Get the User's room number
	 *
	 * @return integer The User's room number
	 */
	public function getRoomNumber() {
		return $this->roomNumber;
	}

	/**
	 * Sets this User's room number
	 *
	 * @param integer $roomNumber The User's room number
	 * @return void
	 */
	public function setRoomNumber($roomNumber) {
		$this->roomNumber = $roomNumber;
	}

	/**
	 * Get the User's nick name
	 *
	 * @return string The User's nick name
	 */
	public function getNickName() {
		return $this->nickName;
	}

	/**
	 * Sets this User's nick name
	 *
	 * @param string $nickName The User's nick name
	 * @return void
	 */
	public function setNickName($nickName) {
		$this->nickName = $nickName;
	}

	/**
	 * Get the User's balance
	 *
	 * @return double The User's balance
	 */
	public function getBalance() {
		return $this->balance;
	}

	/**
	 * Sets this User's balance
	 *
	 * @param double $balance The User's balance
	 * @return void
	 */
	public function setBalance($balance = 0) {
		$this->balance = $balance;
	}

	/**
	 * Get the User's deposit
	 *
	 * @return double The User's deposit
	 */
	public function getDeposit() {
		return $this->deposit;
	}

	/**
	 * Sets this User's deposit
	 *
	 * @param double $deposit The User's deposit
	 * @return void
	 */
	public function setDeposit($deposit) {
		$this->deposit = $deposit;
	}

	/**
	 * Adds a payment to this user
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Payment $payment
	 * @param boolean $unbalanced If payment should not change the users balance
	 * @return void
	 */
	public function addPayment(\CGROSS\Drinkaccounting\Domain\Model\Payment $payment, $unbalance = FALSE) {
		if($unbalance) {
			$payment->setBalanceOld($this->getBalance());
			$payment->setBalanceNew($this->getBalance());
		} else {
			// Calc payment balance
			$payment->setBalanceOld($this->getBalance());
			$payment->setBalanceNew($this->getBalance() + $payment->getSum());
			// Calc new user balance
			$this->setBalance($this->getBalance() + $payment->getSum());
		}
		$payment->setUser($this);
		$this->payments->add($payment);
	}

	/**
	 * Removes a payment from this account
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Payment $payment
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Payment The cancel payment
	 */
	public function cancelPayment(\CGROSS\Drinkaccounting\Domain\Model\Payment $payment) {
		// Cancel corresponding transaction
		$cancelTransaction = $payment->getTransaction()->getAccount()->cancelTransaction($payment->getTransaction());

		// Create cancel payment
		$cancelPayment = new Payment();
		$cancelPayment->setDate(new \DateTime());
		$cancelPayment->setSum(-$payment->getSum());
		$cancelPayment->setDesc('<b>CANCELED ('.$payment->getDate()->format('Y-m-d').')</b> - '.$payment->getDesc());
		$cancelPayment->setTransaction($cancelTransaction);
		$cancelPayment->setDeletable(false); // Don't cancel a cancellation
		// Update desc of initial payment
		$payment->setDesc('<b>CANCELED</b> - '.$payment->getDesc());
		$payment->setDeletable(false); // Don't cancel a cancellation
		// Update and add payments
		$this->updatePayment($payment);
		$this->addPayment($cancelPayment);
		return $cancelPayment;
	}

	/**
	 * Updates a payment from this account
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Payment $payment
	 * @return void
	 */
	 public function updatePayment(\CGROSS\Drinkaccounting\Domain\Model\Payment $payment) {
		$payments = $this->getPayments();
		$payments->set($this->getPayments()->indexOf($payment), $payment);
		$this->setPayments($payments);
	 }

	/**
	 * Get the User's payments
	 *
	 * @return \Doctrine\Common\Collections\Collection The User's payments
	 */
	public function getPayments() {
		return $this->payments;
	}

	/**
	 * Sets this User's payments
	 *
	 * @param \Doctrine\Common\Collections\Collection $payments The User's payments
	 * @return void
	 */
	public function setPayments(\Doctrine\Common\Collections\Collection $payments) {
		$this->payments = $payments;
	}

	/**
	 * Get the User's active
	 *
	 * @return boolean The User's active
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * Sets this User's active
	 *
	 * @param boolean $active The User's active
	 * @return void
	 */
	public function setActive($active) {
		$this->active = $active;
	}

	/**
	 * Display name for statement wizard
	 *
	 * @return string
	 */
	public function getDisplayName() {
		$name = ($this->nickName != '') ? $this->nickName : $this->surname;
		$name .= ($this->deposit == 0) ? '*' : '';
		return $this->roomNumber." - ".$name;
	}

}
?>