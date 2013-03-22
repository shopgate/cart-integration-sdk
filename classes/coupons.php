<?php

/**
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateCart extends ShopgateContainer {
	protected $customer_number;
	protected $external_customer_number;
	protected $external_customer_id;
	protected $external_customer_group;
	protected $external_customer_group_id;
	protected $mail;
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
	 * @var ShopgateShopCoupon[]
	 */
	protected $coupons;
	/**
	 * @var ShopgateCoupon[]
	 */
	protected $shopgate_coupons;
	
	/**
	 * @var ShopgateCartItem[]
	 */
	protected $items;
	
	public function __construct($data) {
		$this->coupons = array();
		$this->items = array();
		
		parent::__construct($data);
	}
	
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
	
	public function getExternalCustomerGroup() {
		return $this->external_customer_group;
	}
	
	public function setExternalCustomerGroup($value) {
		$this->external_customer_group = $value;
	}
	
	public function getExternalCustomerGroupId() {
		return $this->external_customer_group_id;
	}
	
	public function setExternalCustomerGroupId($value) {
		$this->external_customer_group_id = $value;
	}
	
	public function getMail() {
		return $this->mail;
	}
	
	public function setMail($value) {
		$this->mail = $value;
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
			if ((!is_object($element) || !($element instanceof ShopgateShopCoupon)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateShopCoupon($element);
			}
		}
		
		$this->coupons = $value;
	}
	
	public function getShopgateCoupons() {
		return $this->shopgate_coupons;
	}
	
	public function setShopgateCoupons($value) {
// 		if (!is_array($value)) {
// 			$this->shopgate_coupons = null;
// 			return;
// 		}
		
// 		foreach ($value as $index => &$element) {
// 			if ((!is_object($element) || !($element instanceof ShopgateCoupon)) && !is_array($element)) {
// 				unset($value[$index]);
// 				continue;
// 			}
		
// 			if (is_array($element)) {
// 				$element = new ShopgateCoupon($element);
// 			}
// 		}
		
// 		$this->shopgate_coupons = $value;
	}
	
	/**
	 *
	 * @return ShopgateAddress
	 */
	public function getInvoiceAddress() {
		return $this->invoice_address;
	}
	
	/**
	 *
	 * @param ShopgateAddress $value
	 */
	public function setInvoiceAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->invoice_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
		}
		
		$value->setFirstName("Shopgate Firstname");
		$value->setLastName("Shopgate Lastname");

		$this->invoice_address = $value;
	}
	
	/**
	 *
	 * @return ShopgateAddress
	 */
	public function getDeliveryAddress() {
		return $this->delivery_address;
	}
	
	/**
	 *
	 * @param ShopgateAddress $value
	 */
	public function setDeliveryAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->delivery_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
		}
		
		$value->setFirstName("Shopgate Firstname");
		$value->setLastName("Shopgate Lastname");

		$this->delivery_address = $value;
	}
	
	/**
	 *
	 * @return ShopgateCartItem[]
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 *
	 * @param ShopgateCartItem[] $value
	 */
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

class ShopgateShopCoupon extends ShopgateContainer {
	/**
	 *
	 * @var string
	 */
	protected $code;
	/**
	 *
	 * @var int
	 */
	protected $order_index;
	/**
	 *
	 * @var string
	 */
	protected $internal_info;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitShopCoupon($this);
	}
	
	/**
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setCode($value) {
		$this->code = $value;
	}
	/**
	 *
	 * @return int
	 */
	public function getOrderIndex() {
		return $this->order_index;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setOrderIndex($value) {
		$this->order_index = $value;
	}
	/**
	 *
	 * @return string
	 */
	public function getInternalInfo() {
		return $this->internal_info;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setInternalInfo($value) {
		$this->internal_info = $value;
	}
}

class ShopgateCoupon extends ShopgateContainer {
	/**
	 *
	 * @var string
	 */
	protected $code;
	/**
	 *
	 * @var string
	 */
	protected $name;
	/**
	 *
	 * @var int
	 */
	protected $order_index;
	/**
	 *
	 * @var float
	 */
	protected $amount;
	/**
	 *
	 * @var boolean
	 */
	protected $is_free_shipping;
	/**
	 *
	 * @var string
	 */
	protected $internal_info;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCoupon($this);
	}
	
	/**
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setCode($value) {
		$this->code = $value;
	}
	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}
	/**
	 *
	 * @return int
	 */
	public function getOrderIndex() {
		return $this->order_index;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setOrderIndex($value) {
		$this->order_index = $value;
	}
	/**
	 *
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}
	/**
	 *
	 * @param float $value
	 */
	public function setAmount($value) {
		$this->amount = $value;
	}
	/**
	 *
	 * @return boolean
	 */
	public function getIsFreeShipping() {
		return $this->is_free_shipping;
	}
	/**
	 *
	 * @param boolean $value
	 */
	public function setIsFreeShipping($value) {
		$this->is_free_shipping = $value;
	}
	/**
	 *
	 * @return string
	 */
	public function getInternalInfo() {
		return $this->internal_info;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setInternalInfo($value) {
		$this->internal_info = $value;
	}
}

class ShopgateCartItem extends ShopgateContainer {
	protected $item_number;
	protected $item_number_public;
	protected $quantity;
	protected $name;
	protected $unit_amount;
	protected $unit_amount_with_tax;
	protected $tax_class;
	protected $tax_percent;
	protected $currency;
	protected $internal_order_info;
	
	protected $options;
	
	protected $inputs;
	
	public function __construct($data) {
		$this->options = array();
		$this->inputs = array();
	
		parent::__construct($data);
	}
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCartItem($this);
	}
	
	public function getItemNumber() {
		return $this->item_number;
	}
	
	public function setItemNumber($value) {
		$this->item_number = $value;
	}
	
	public function getItemNumberPublic() {
		return $this->item_number_public;
	}
	
	public function setItemNumberPublic($value) {
		$this->item_number_public = $value;
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
	
	public function getTaxClass() {
		return $this->tax_class;
	}
	
	public function setTaxClass($value) {
		$this->tax_class = $value;
	}
	
	public function getTaxPercent() {
		return $this->tax_percent;
	}
	
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
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
