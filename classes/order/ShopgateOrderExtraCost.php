<?php

//require_once '../core/ShopgateCopntainer.php';

class ShopgateOrderExtraCost extends ShopgateContainer {

	const TYPE_SHIPPING = 'shipping';
	const TYPE_PAYMENT = 'payment';
	const TYPE_MISC = 'misc';

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var float
	 */
	protected $tax_percent;

	/**
	 * @var float
	 */
	protected $amount;


	/**
	 * @param string $value
	 */
	public function setType($value) {
		if (
		self::TYPE_SHIPPING != $value &&
		self::TYPE_PAYMENT != $value &&
		self::TYPE_MISC != $value
		) {
			$value = null;
		}

		$this->type = $value;
	}

	/**
	 * @param float $value
	 */
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmount($value) {
		$this->amount = $value;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return float
	 */
	public function getTaxPercent() {
		return $this->tax_percent;
	}

	/**
	 *
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderExtraCost($this);
	}

}
