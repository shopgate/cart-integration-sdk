<?php

require_once 'ShopgateOrderBase.php';
require_once 'ShopgateOrderExtraCost.php';
require_once 'ShopgateOrderTax.php';
//require_once 'ShopgateExternalOrderItem.php';

class ShopgateExternalOrder extends ShopgateOrderBase { // Plugin API

	/**
	 * @var ShopgateOrderTax[]
	 */
	protected $order_taxes;

	/**
	 * @var ShopgateOrderExtraCost[]
	 */
	protected $extra_costs;

	/**
	 * ShopgateOrderTax[] | array[]
	 * @param array $value
	 */
	public function setOrderTaxes($value) {
		// TODO: $this->order_taxes = $this->validateList($value, 'ShopgateOrderTax');
		
		if (!is_array($value)) {
			$this->order_taxes = null;
			return;
		}
		
		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = new ShopgateOrderTax($element);
			}
			else if ( !is_object($element) || !($element instanceof ShopgateOrderTax) ) {
				unset($value[$index]);
			}
		}
		
		$this->order_taxes = $value;
	}

	/**
	 * ShopgateOrderExtraCost[] | array[]
	 * @param array $value
	 */
	public function setExtraCosts($value) {
		// TODO: $this->extra_cost = $this->validateList($value, 'ShopgateOrderExtraCost');
		
		if (!is_array($value)) {
			$this->extra_costs = null;
			return;
		}

		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = new ShopgateOrderExtraCost($element);
			}
			else if ( !is_object($element) || !($element instanceof ShopgateOrderExtraCost) ) {
				unset($value[$index]);
			}
		}

		$this->extra_costs = $value;
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
