<?php
class ShopgateExternalOrder extends ShopgateOrder { // Plugin API
	/**
	 * @var ShopgateExternalOrderTax[]
	 */
	protected $order_taxes;

	/**
	 * @var ShopgateExternalOrderExtraCost[]
	 */
	protected $extra_costs;

	/**
	 * ShopgateExternalOrderTax[]|array[string, mixed]
	 * @param array $value
	 */
	public function setOrderTaxes($value) {
		if (empty($value) || !is_array($value)) {
			$this->order_taxes = array();
			return;
		}

		$orderTaxes = array();
		foreach ($value as $index => $element) {
			if (!($element instanceof ShopgateExternalOrderTax) && !is_array($element)) {
				continue;
			}

			if (is_array($element)) {
				$orderTaxes[] = new ShopgateExternalOrderTax($element);
			} else {
				$orderTaxes[] = $element;
			}
		}

		$this->order_taxes = $orderTaxes;
	}

	/**
	 * ShopgateExternalOrderExtraCost[]|array[string, mixed]
	 * @param array $value
	 */
	public function setExtraCosts($value) {
		if (empty($value) || !is_array($value)) {
			$this->extra_costs = array();
			return;
		}

		$extraCosts = array();
		foreach ($value as $index => $element) {
			if (!($element instanceof ShopgateExternalOrderExtraCost) && !is_array($element)) {
				continue;
			}

			if (is_array($element)) {
				$extraCosts[] = new ShopgateExternalOrderExtraCost($element);
			} else {
				$extraCosts[] = $element;
			}
		}

		$this->extra_costs = $extraCosts;
	}

	/**
	 * @return ShopgateOrderTax[]
	 */
	public function getOrderTaxes() {
		return $this->order_taxes;
	}

	/**
	 * @return ShopgateOrderExtraCost[]
	 */
	public function getExtraCosts() {
		return $this->extra_costs;
	}

	/**
	 * @see ShopgateCartBase::getOrderItem()
	 * @return ShopgateExternalOrderItem
	 */
	protected function getOrderItem(array $options) {
		return new ShopgateExternalOrderItem($options);
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalOrder($this);
	}
}

class ShopgateExternalOrderItem extends ShopgateOrderItem {
	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->description = $value;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function visit(ShopgateContainerVisitor $v) {
		$v->visitExternalOrderItem($this);
	}
}

class ShopgateExternalOrderExtraCost extends ShopgateContainer {
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
		$v->visitExternalOrderExtraCost($this);
	}
}

class ShopgateExternalOrderTax extends ShopgateContainer {
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
		$v->visitExternalOrderTax($this);
	}
}