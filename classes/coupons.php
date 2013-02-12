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
	 * @var ShopgateCoupon[]
	 */
	protected $coupons;
	
	/**
	 * @var ShopgateAddress
	 */
	protected $invoice_address;
	
	/**
	 * @var ShopgateAddress
	 */
	protected $delivery_address;
	
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
		$this->coupons = $value;
	}
	
	public function getDeliveryAddress() {
		return $this->delivery_address;
	}
	
	public function setDeliveryAddress($value) {
		$this->delivery_address = $value;
	}
	
	public function getInvoiceAddress() {
		return $this->invoice_address;
	}
	
	public function setInvoiceAddress($value) {
		$this->invoice_address = $value;
	}
	
	public function getItems() {
		return $this->items;
	}
	
	public function setItems($value) {
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

class ShopgateCartItem {
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
		$v->visitCoupon($this);
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
		$this->options = $value;
	}
	
	public function getInputs() {
		return $this->inputs;
	}
	
	public function setInputs($value) {
		$this->inputs = $value;
	}
}

class ShopgateCartItemOption {
	protected $option_number;
	protected $name;
	protected $value_number;
	protected $value;
	protected $additional_amount_with_tax;
	
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

class ShopgateCartItemInput {
	protected $type;
	protected $additional_amount_with_tax;
	protected $label;
	protected $user_input;
	protected $info_text;
	
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

