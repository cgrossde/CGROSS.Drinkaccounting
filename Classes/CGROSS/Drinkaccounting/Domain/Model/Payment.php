<?php
namespace CGROSS\Drinkaccounting\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Payment
 *
 * @FLOW3\Entity
 */
class Payment {

	/**
	 * The user
	 * @var \CGROSS\Drinkaccounting\Domain\Model\User
	 * @ORM\ManyToOne(inversedBy="payments")
	 */
	protected $user;

	/**
	 * The date
	 * @var \DateTime
	 * @ORM\Column(name="paymentdate")
	 */
	protected $date;

	/**
	 * The desc
	 * @var string
	 * @ORM\Column(name="paymentdesc")
	 */
	protected $desc;

	/**
	 * The statement
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Statement
	 * @ORM\ManyToOne(inversedBy="payments")
	 */
	protected $statement;

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
	 * The transaction
	 * @var \CGROSS\Drinkaccounting\Domain\Model\Transaction
	 * @ORM\OneToOne
	 */
	protected $transaction;

	/**
	 * The creationDate
	 * @var \DateTime
	 */
	protected $creationDate;

	/**
	 * Is payment deletable
	 * @var boolean
	 */
	 protected $deletable;

	/**
	 * Constructs this payment
	 *
	 */
	public function __construct() {
		$this->creationDate = new \DateTime();
		$this->deletable = true;
	}

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
	 * Get the Payment's date
	 *
	 * @return \DateTime The Payment's date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Sets this Payment's date
	 *
	 * @param \DateTime $date The Payment's date
	 * @return void
	 */
	public function setDate($date) {
		$this->date = $date;
	}

	/**
	 * Get the Payment's desc
	 *
	 * @return string The Payment's desc
	 */
	public function getDesc() {
		return $this->desc;
	}

	/**
	 * Sets this Payment's desc
	 *
	 * @param string $desc The Payment's desc
	 * @return void
	 */
	public function setDesc($desc) {
		$this->desc = $desc;
	}

	/**
	 * Get the Payment's statement
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Statement The Payment's statement
	 */
	public function getStatement() {
		return $this->statement;
	}

	/**
	 * Sets this Payment's statement
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Statementn $statement The Payment's statement
	 * @return void
	 */
	public function setStatement(\CGROSS\Drinkaccounting\Domain\Model\Statement $statement) {
		$this->statement = $statement;
	}

	/**
	 * Get the Payment's sum
	 *
	 * @return double The Payment's sum
	 */
	public function getSum() {
		return $this->sum;
	}

	/**
	 * Sets this Payment's sum
	 *
	 * @param double $sum The Payment's sum
	 * @return void
	 */
	public function setSum($sum) {
		$this->sum = $sum;
	}

	/**
	 * Get the Payment's balance old
	 *
	 * @return double The Payment's balance old
	 */
	public function getBalanceOld() {
		return $this->balanceOld;
	}

	/**
	 * Sets this Payment's balance old
	 *
	 * @param double $balanceOld The Payment's balance old
	 * @return void
	 */
	public function setBalanceOld($balanceOld) {
		$this->balanceOld = $balanceOld;
	}

	/**
	 * Get the Payment's balance new
	 *
	 * @return double The Payment's balance new
	 */
	public function getBalanceNew() {
		return $this->balanceNew;
	}

	/**
	 * Sets this Payment's balance new
	 *
	 * @param double $balanceNew The Payment's balance new
	 * @return void
	 */
	public function setBalanceNew($balanceNew) {
		$this->balanceNew = $balanceNew;
	}

	/**
	 * Get the Payment's transaction
	 *
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Transaction The Payment's transaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	/**
	 * Sets this Payment's transaction
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction The Payment's transaction
	 * @return void
	 */
	public function setTransaction(\CGROSS\Drinkaccounting\Domain\Model\Transaction $transaction = NULL) {
		$this->transaction = $transaction;
	}

	 /**
	  * Get deletable
	  *
	  * @return boolean deletable
	  */
	  public function getDeletable() {
	  	return $this->deletable;
	  }

	 /**
	  * Set deletable state
	  *
	  * @param boolean $deletable Is payment deletable
	  * @return void
	  */
	  public function setDeletable($deletable) {
	  	$this->deletable = $deletable;
	  }

}
?>