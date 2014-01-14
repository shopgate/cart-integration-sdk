<?php

require_once 'ShopgateOrderBaseItem.php';
// require_once '../core/ShopgateContainerVisitor.php';

class ShopgateOrderItem extends ShopgateBaseItem {

	/**
	 * @var string
	 */
	protected $item_number_public;
	
	protected $quantity;

	protected $name;

	protected $unit_amount;
	protected $unit_amount_with_tax;

	protected $tax_percent;
	protected $tax_class_key;
	protected $tax_class_id;

	protected $currency;

	protected $internal_order_info;

	protected $options = array();

	protected $inputs = array();

	protected $attributes = array();


	##########
	# Setter #
	##########

	
	/**
	 * @param string $value
	 */
	public function setItemNumberPublic($value) {
		$this->item_number_public = $value;
	}
	
	/**
	 * @param string $value
	*/
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * @param float $value
	 */
	public function setUnitAmount($value) {
		$this->unit_amount = $value;
	}

	/**
	 * @param float $value
	 */
	public function setUnitAmountWithTax($value) {
		$this->unit_amount_with_tax = $value;
	}

	/**
	 * @param int $value
	 */
	public function setQuantity($value) {
		$this->quantity = $value;
	}

	/**
	 * @param float $value
	 */
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
	}

	/**
	 * Sets the tax_class_key value
	 *
	 * @param string $value
	 */
	public function setTaxClassKey($value) {
		$this->tax_class_key = $value;
	}

	/**
	 * Sets the tax_class_id
	 *
	 * @param string $value
	 */
	public function setTaxClassId($value) {
		$this->tax_class_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}

	/**
	 * @param string $value
	 */
	public function setInternalOrderInfo($value) {
		$this->internal_order_info = $value;
	}

	/**
	 * @param ShopgateOrderItemOption[]|mixed[][] $value
	 */
	public function setOptions($value) {
		if (empty($value) || !is_array($value)) {
			$this->options = array();
			return;
		}

		// convert sub-arrays into ShopgateOrderItemOption objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemOption)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateOrderItemOption($element);
			}
		}

		$this->options = $value;
	}

	/**
	 * @param ShopgateOrderItemInput[]|mixed[][] $value
	 */
	public function setInputs($value) {
		if (empty($value) || !is_array($value)) {
			$this->inputs = array();
			return;
		}

		// convert sub-arrays into ShopgateOrderItemInputs objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemInput)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
				
			if (is_array(($element))) {
				$element = new ShopgateOrderItemInput($element);
			}
		}

		$this->inputs = $value;
	}

	/**
	 * @param ShopgateOrderItemAttribute[]|mixed[][] $value
	 */
	public function setAttributes($value) {
		if (empty($value) || !is_array($value)) {
			$this->attributes = array();
			return;
		}

		// convert sub-arrays into ShopgateOrderItemInputs objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemAttribute)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
				
			if (is_array(($element))) {
				$element = new ShopgateOrderItemAttribute($element);
			}
		}

		$this->attributes = $value;
	}


	##########
	# Getter #
	##########

	
	/**
	 * return string
	 */
	public function getItemNumberPublic() {
		return $this->item_number_public;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return float
	 */
	public function getUnitAmount() {
		return $this->unit_amount;
	}

	/**
	 * @return float
	 */
	public function getUnitAmountWithTax() {
		return $this->unit_amount_with_tax;
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * @return float
	 */
	public function getTaxPercent() {
		return $this->tax_percent;
	}

	/**
	 * @return string
	 */
	public function getTaxClassKey() {
		return $this->tax_class_key;
	}

	/**
	 * Returns the tax_class_id
	 *
	 * @return string
	 */
	public function getTaxClassId() {
		return $this->tax_class_id;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * @return string
	 */
	public function getInternalOrderInfo() {
		return $this->internal_order_info;
	}

	/**
	 * @return ShopgateOrderItemOption[]
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return ShopgateOrderItemInput[]
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * @return ShopgateOrderItemAttribute[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItem($this);
	}

}
