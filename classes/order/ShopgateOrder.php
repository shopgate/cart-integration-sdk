<?php

require_once 'ShopgateOrderBase.php';
//require_once 'ShopgateShippingInfo.php';
//require_once 'ShopgateOrderItem.php';

class ShopgateOrder extends ShopgateOrderBase { // Merchant API

	/**
	 * @var string
	 */
	protected $customer_number;

	/**
	 * @var string
	 */
	protected $external_customer_number;

	/**
	 * @var string
	 */
	protected $external_customer_id;

	/**
	 * @var string
	 */
	protected $confirm_shipping_url;

	/**
	 * @var float
	 */
	protected $amount_shopgate_payment;

	/**
	 * @var float
	 */
	protected $amount_shop_payment;

	/**
	 * @var float
	 */
	protected $amount_items;

	/**
	 * @var float
	 */
	protected $amount_shipping;

	/**
	 * @var string
	 */
	protected $shipping_group;

	/**
	 * @var string
	 */
	protected $shipping_type;

	/**
	 * @var ShopgateShippingInfo
	 */
	protected $shipping_infos;

	/**
	 * @var bool
	 */
	protected $is_shipping_blocked;

	/**
	 * @var float
	 */
	protected $payment_tax_percent;

	/**
	 * @var string
	 */
	protected $payment_group;

	/**
	 * @var array
	 */
	protected $payment_infos;

	/**
	 * @var bool
	 */
	protected $is_customer_invoice_blocked;

	/**
	 * @var bool
	 */
	protected $is_storno;

	/**
	 * @var bool
	 */
	protected $is_test;

	/**
	 * @var bool
	 */
	protected $update_shipping;

	/**
	 * @var bool
	 */
	protected $update_payment;


	/**
	 * @param string $value
	 */
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalCustomerNumber($value) {
		$this->external_customer_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalCustomerId($value) {
		$this->external_customer_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setConfirmShippingUrl($value) {
		$this->confirm_shipping_url = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountShopgatePayment($value) {
		$this->amount_shopgate_payment = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountShopPayment($value) {
		$this->amount_shop_payment = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountItems($value) {
		$this->amount_items = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountShipping($value) {
		$this->amount_shipping = $value;
	}

	/**
	 * @param string $value
	 */
	public function setShippingGroup($value) {
		$this->shipping_group = $value;
	}

	/**
	 * @param string $value
	 */
	public function setShippingType($value) {
		$this->shipping_type = $value;
	}

	/**
	 * ShopgateShippingInfo | array
	 * @param array $value
	 */
	public function setShippingInfos($value) {
		if (is_array($value)) {
			$value = new ShopgateShippingInfo($value);
		}
		else if ( !is_object($value) || !($value instanceof ShopgateShippingInfo) ) {
			$value = null;
		}

		$this->shipping_infos = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsShippingBlocked($value) {
		$this->is_shipping_blocked = $value;
	}

	/**
	 * @param float
	 */
	public function setPaymentTaxPercent($value) {
		$this->payment_tax_percent = $value;
	}

	/**
	 * @param string
	 */
	public function setPaymentGroup($value) {
		$this->payment_group = $value;
	}

	/**
	 * @param array $value An array of additional information about the payment depending on the payment type
	 */
	public function setPaymentInfos($value) {
		$this->payment_infos = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsCustomerInvoiceBlocked($value) {
		$this->is_customer_invoice_blocked = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsStorno($value) {
		$this->is_storno = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsTest($value) {
		$this->is_test = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setUpdateShipping($value) {
		$this->update_shipping = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setUpdatePayment($value) {
		$this->update_payment = $value;
	}


	/**
	 * @return string
	 */
	public function getCustomerNumber() {
		return $this->customer_number;
	}

	/**
	 * @return string
	 */
	public function getExternalCustomerNumber() {
		return $this->external_customer_number;
	}

	/**
	 * @return string
	 */
	public function getExternalCustomerId() {
		return $this->external_customer_id;
	}

	/**
	 * @return string
	 */
	public function getConfirmShippingUrl() {
		return $this->confirm_shipping_url;
	}

	/**
	 * @return float
	 */
	public function getAmountShopgatePayment() {
		return $this->amount_shopgate_payment;
	}

	/**
	 * @return float
	 */
	public function getAmountShopPayment() {
		return $this->amount_shop_payment;
	}

	/**
	 * @return float
	 */
	public function getAmountItems() {
		return $this->amount_items;
	}

	/**
	 * @return float
	 */
	public function getAmountShipping() {
		return $this->amount_shipping;
	}

	/**
	 * @return string
	 */
	public function getShippingGroup() {
		return $this->shipping_group;
	}

	/**
	 * @return string
	 */
	public function getShippingType() {
		return $this->shipping_type;
	}

	/**
	 * @return ShopgateShippingInfo[]
	 */
	public function getShippingInfos() {
		return $this->shipping_infos;
	}

	/**
	 * @return bool
	 */
	public function getIsShippingBlocked() {
		return $this->is_shipping_blocked;
	}

	/**
	 * @return float
	 */
	public function getPaymantTaxPercent() {
		return $this->payment_tax_percent;
	}

	/**
	 * @return string
	 */
	public function getPaymentGroup() {
		return $this->payment_group;
	}

	/**
	 * @return array
	 */
	public function getPaymentInfos() {
		return $this->payment_infos;
	}

	/**
	 * @return bool
	 */
	public function getIsCustomerInvoiceBlocked() {
		return $this->is_customer_invoice_blocked;
	}

	/**
	 * @return bool
	 */
	public function getIsStorno() {
		return $this->is_storno;
	}

	/**
	 * @return bool
	 */
	public function getIsTest() {
		return $this->is_test;
	}

	/**
	 * @return bool
	 */
	public function getUpdateShipping() {
		return $this->update_shipping;
	}

	/**
	 * @return bool
	 */
	public function getUpdatePayment() {
		return $this->update_payment;
	}

	/**
	 * @see ShopgateCartBase::getOrderItem()
	 * @return ShopgateOrderItem
	 */
	protected function getOrderItem(array $options) {
		return new ShopgateOrderItem($options);
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrder($this);
	}

}