<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Transaction
 *
 * @FLOW3\Entity
 */
class Transaction {

	 /**
	 * The account
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Account
	 * @ORM\ManyToOne(inversedBy="transactions")
	 */
	protected $account;

	/**
	 * The date
	 * @var \DateTime
	 * @ORM\Column(name="transactiondate")
	 * @FLOW3\Validate(type="NotEmpty")
	 */
	protected $date;

	/**
	 * The creationDate
	 * @var \DateTime
	 */
	protected $creationDate;

	/**
	 * The sum
	 * @var double
	 * @FLOW3\Validate(type="NotEmpty")
	 */
	protected $sum;

	/**
	 * The balance old
	 * @var double
	 */
	protected $balanceOld;

	/**
	 * The balance new
	 * @var double
	 */
	protected $balanceNew;

	/**
	 * The desc
	 * @var string
	 * @ORM\Column(name="transactiondesc")
	 * @FLOW3\Validate(type="NotEmpty")
	 */
	protected $desc;

	/**
	 * Is transaction deletable
	 * @var boolean
	 */
	 protected $deletable;

	/**
	 * Construct this transaction
	 *
	 */
	 public function __construct() {
	 	$this->creationDate = new \DateTime();
		$this->deletable = true;
	 }

	/**
	 * Get the Transaction's account
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Account The Transaction's account
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * Sets this Transaction's account
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Account $account The Transaction's account
	 * @return void
	 */
	public function setAccount(\CGROSS\Drinkaccounting\Domain\Model\Account $account) {
		$this->account = $account;
	}

	/**
	 * Get the Transaction's date
	 *
	 * @return \DateTime The Transaction's date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Sets this Transaction's date
	 *
	 * @param \DateTime $date The Transaction's date
	 * @return void
	 */
	public function setDate($date) {
		$this->date = $date;
	}

	/**
	 * Get the Transaction's sum
	 *
	 * @return double The Transaction's sum
	 */
	public function getSum() {
		return $this->sum;
	}

	/**
	 * Sets this Transaction's sum
	 *
	 * @param double $sum The Transaction's sum
	 * @return void
	 */
	public function setSum($sum) {
		$this->sum = $sum;
	}

	/**
	 * Get the Transaction's balance old
	 *
	 * @return double The Transaction's balance old
	 */
	public function getBalanceOld() {
		return $this->balanceOld;
	}

	/**
	 * Sets this Transaction's balance old
	 *
	 * @param double $balanceOld The Transaction's balance old
	 * @return void
	 */
	public function setBalanceOld($balanceOld) {
		$this->balanceOld = $balanceOld;
	}

	/**
	 * Get the Transaction's balance new
	 *
	 * @return double The Transaction's balance new
	 */
	public function getBalanceNew() {
		return $this->balanceNew;
	}

	/**
	 * Sets this Transaction's balance new
	 *
	 * @param double $balanceNew The Transaction's balance new
	 * @return void
	 */
	public function setBalanceNew($balanceNew) {
		$this->balanceNew = $balanceNew;
	}

	/**
	 * Get the Transaction's desc
	 *
	 * @return string The Transaction's desc
	 */
	public function getDesc() {
		return $this->desc;
	}

	/**
	 * Sets this Transaction's desc
	 *
	 * @param string $desc The Transaction's desc
	 * @return void
	 */
	public function setDesc($desc) {
		$this->desc = $desc;
	}

	/**
	 * Is transaction deletable
	 *
	 * @return boolean Is transaction deletable
	 */
	 public function getDeletable() {
	 	return $this->deletable;
	 }

	 /**
	  * Set deletable state
	  *
	  * @param boolean $deletable Is transaction deletable
	  * @return void
	  */
	  public function setDeletable($deletable) {
	  	$this->deletable = $deletable;
	  }

}
?>