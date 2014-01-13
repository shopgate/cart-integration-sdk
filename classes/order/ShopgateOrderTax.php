<?php

// require_once '../core/ShopgateContainer.php';

class ShopgateOrderTax extends ShopgateContainer {

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var float
	 */
	protected $tax_percent;

	/**
	 * @var float
	 */
	protected $amount;


	/**
	 *
	 * @param string $value
	 */
	public function setLabel($value){
		$this->label = $value;
	}

	/**
	 *
	 * @param float $value
	 */
	public function setTaxPercent($value){
		$this->tax_percent = $value;
	}

	/**
	 *
	 * @param float $value
	 */
	public function setAmount($value){
		$this->amount = $value;
	}


	/**
	 *
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 *
	 * @return float
	 */
	public function getTaxPercent(){
		return $this->tax_percent;
	}

	/**
	 *
	 * @return float
	 */
	public function getAmount(){
		return $this->amount;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderTax($this);
	}

}

