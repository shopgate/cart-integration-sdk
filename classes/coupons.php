<?php

class ShopgateCart extends ShopgateContainer {
	protected $customer_number;
	protected $external_customer_number;
	protected $external_customer_id;
	protected $customer_group;
	protected $customer_group_id;
	protected $payment_group;
	protected $payment_method;
	
	/**
	 * @var ShopgateAddress
	 */
	protected $invoice_address;
	
	/**
	 * @var ShopgateAddress
	 */
	protected $delivery_address;
	
	/**
	 * @var ShopgateCoupon[]
	 */
	protected $coupons;
	
	/**
	 * @var ShopgateCartItem[]
	 */
	protected $items;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCart($this);
	}
	
	public function getCustomerNumber() {
		return $this->customer_number;
	}
	
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}
	
	public function getExternalCustomerNumber() {
		return $this->external_customer_number;
	}
	
	public function setExternalCustomerNumber($value) {
		$this->external_customer_number = $value;
	}
	
	public function getExternalCustomerId() {
		return $this->external_customer_id;
	}
	
	public function setExternalCustomerId($value) {
		$this->external_customer_id = $value;
	}
	
	public function getCustomerGroup() {
		return $this->customer_group;
	}
	
	public function setCustomerGroup($value) {
		$this->customer_group = $value;
	}
	
	public function getCustomerGroupId() {
		return $this->customer_group_id;
	}
	
	public function setCustomerGroupId($value) {
		$this->customer_group_id = $value;
	}
	
	public function getPaymentGroup() {
		return $this->payment_group;
	}
	
	public function setPaymentGroup($value) {
		$this->payment_group = $value;
	}
	
	public function getPaymentMethod() {
		return $this->payment_method;
	}
	
	public function setPaymentMethod($value) {
		$this->payment_method = $value;
	}
	
	public function getCoupons() {
		return $this->coupons;
	}
	
	public function setCoupons($value) {
		if (!is_array($value)) {
			$this->coupons = null;
			return;
		}
		
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateCoupon)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
		
			if (is_array($element)) {
				$element = new ShopgateCoupon($element);
			}
		}
		
		$this->coupons = $value;
	}
	
	public function getDeliveryAddress() {
		return $this->delivery_address;
	}
	
	public function setDeliveryAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->delivery_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
		}

		$this->delivery_address = $value;
	}
	
	public function getInvoiceAddress() {
		return $this->invoice_address;
	}
	
	public function setInvoiceAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->invoice_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
		}

		$this->invoice_address = $value;
	}
	
	public function getItems() {
		return $this->items;
	}
	
	public function setItems($value) {
		if (!is_array($value)) {
			$this->items = null;
			return;
		}
		
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateCartItem)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
		
			if (is_array($element)) {
				$element = new ShopgateCartItem($element);
			}
		}
		
		$this->items = $value;
	}
}

class ShopgateCoupon extends ShopgateContainer {
	protected $coupon_code;
	protected $coupon_value;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCoupon($this);
	}
	
	public function getCouponCode() {
		return $this->coupon_code;
	}
	
	public function setCouponCode($value) {
		$this->coupon_code = $value;
	}
	
	public function getCouponValue() {
		return $this->coupon_value;
	}
	
	public function setCouponValue($value) {
		$this->coupon_value = $value;
	}
}

class ShopgateCartItem extends ShopgateContainer {
	protected $item_number;
	protected $quantity;
	protected $name;
	protected $unit_amount;
	protected $unit_amount_with_tax;
	protected $tax_percent;
	protected $currency;
	protected $weight;
	protected $internal_order_info;
	
	protected $options;
	
	protected $inputs;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCartItem($this);
	}
	
	public function getItemNumber() {
		return $this->item_number;
	}
	
	public function setItemNumber($value) {
		$this->item_number = $value;
	}
	
	public function getQuantity() {
		return $this->quantity;
	}
	
	public function setQuantity($value) {
		$this->quantity = $value;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($value) {
		$this->name = $value;
	}
	
	public function getUnitAmount() {
		return $this->unit_amount;
	}
	
	public function setUnitAmount($value) {
		$this->unit_amount = $value;
	}
	
	public function getUnitAmountWithTax() {
		return $this->unit_amount_with_tax;
	}
	
	public function setUnitAmountWithTax($value) {
		$this->unit_amount_with_tax = $value;
	}
	
	public function getTaxPercent() {
		return $this->tax_percent;
	}
	
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
	}
	
	public function getWeight() {
		return $this->weight;
	}
	
	public function setWeight($value) {
		$this->weight = $value;
	}
	
	public function getInternalOrderInfo() {
		return $this->internal_order_info;
	}
	
	public function setInternalOrderInfo($value) {
		$this->internal_order_info = $value;
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function setOptions($value) {
		if (!is_array($value)) {
			$this->options = null;
			return;
		}
		
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateCartItemOption)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
		
			if (is_array($element)) {
				$element = new ShopgateCartItemOption($element);
			}
		}
		
		$this->options = $value;
	}
	
	public function getInputs() {
		return $this->inputs;
	}
	
	public function setInputs($value) {
		if (!is_array($value)) {
			$this->inputs = null;
			return;
		}
		
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateCartItemInput)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
		
			if (is_array($element)) {
				$element = new ShopgateCartItemInput($element);
			}
		}
		
		$this->inputs = $value;
	}
}

class ShopgateCartItemOption extends ShopgateContainer {
	protected $option_number;
	protected $name;
	protected $value_number;
	protected $value;
	protected $additional_amount_with_tax;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCartItemOption($this);
	}
	
	public function getOptionNumber() {
		return $this->option_number;
	}
	
	public function setOptionNumber($value) {
		$this->option_number = $value;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($value) {
		$this->name = $value;
	}
	
	public function getValueNumber() {
		return $this->value_number;
	}
	
	public function setValueNumber($value) {
		$this->value_number = $value;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}
	
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}
}

class ShopgateCartItemInput extends ShopgateContainer {
	protected $type;
	protected $additional_amount_with_tax;
	protected $label;
	protected $user_input;
	protected $info_text;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCartItemInput($this);
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($value) {
		$this->type = $value;
	}
	
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}
	
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($value) {
		$this->label = $value;
	}
}
