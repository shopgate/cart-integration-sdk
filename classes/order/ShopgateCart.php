<?php

require_once 'ShopgateCartBase.php';
require_once 'ShopgateCoupon.php';
//require_once 'ShopgateShippingInfo.php';
//require_once 'ShopgateContainerVisitor.php';

class ShopgateCart extends ShopgateCartBase {

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
	protected $external_customer_group;

	/**
	 * @var string
	 */
	protected $external_customer_group_id;

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
	 * @var string
	 */
	protected $payment_group;

	/**
	 * @var ShopgateCoupons[] 
	 */
	protected $shopgate_coupons;

	
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
	public function setExternalCustomerGroup($value) {
		$this->external_customer_group = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setExternalCustomerGroupId($value) {
		$this->external_customer_group_id = $value;
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
	 * @param string $value
	 */
	public function setPaymentGroup($value) {
		$this->payment_group = $value;
	} 
	
	/**
	 * mixed: ShopgateCoupon[] | array[]
	 * @param array $value
	 */
	public function setShopgateCoupons($value) {
		// TODO: $this->shopgate_coupons = $this->validateList($value, 'ShopgateCoupon');
	
		if (!is_array($value)) {
			$this->shopgate_coupons = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = new ShopgateCoupon($element);
			}
			else if ( !is_object($element) || !($element instanceof ShopgateCoupon) ) {
				unset($value[$index]);
			}
		}
	
		$this->shopgate_coupons = $value;
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
	public function getExternalCustomerGroup() {
		return $this->external_customer_group;
	}
	
	/**
	 * @return string
	 */
	public function getExternalCustomerGroupId() {
		return $this->external_customer_group_id;
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
	 * @return string
	 */
	public function getPaymentGroup() {
		return $this->payment_group;
	}

	/**
	 * @return ShopgateCoupons[]
	 */
	public function getShopgateCoupons() {
		return $this->shopgate_coupons;
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
		$v->visitCart($this);
	}

}
