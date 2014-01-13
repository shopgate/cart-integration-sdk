<?php
/*
* Shopgate GmbH
*
* URHEBERRECHTSHINWEIS
*
* Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
* zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
* Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
* öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
* schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
*
* COPYRIGHT NOTICE
*
* This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
* for the purpose of facilitating communication between the IT system of the customer and the IT system
* of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
* transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
* of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
*
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/

abstract class ShopgateCartBase extends ShopgateContainer {

	const SHOPGATE = "SHOPGATE";
	const PREPAY = "PREPAY";
	
	const DEBIT = "DEBIT";
	const COD = "COD";
	
	const INVOICE = "INVOICE";
	const KLARNA_INV = "KLARNA_INV";
	const BILLSAFE = "BILLSAFE";
	const MSTPAY_INV = "MSTPAY_INV";
	
	const PAYPAL = "PAYPAL";
	const MASTPAY_PP = "MASTPAY_PP";
	const SAGEPAY_PP = "SAGEPAY_PP";
	
	const CC = "CC";
	const DT_CC = "DT_CC";
	const AUTHN_CC = "AUTHN_CC";
	const FRSTDAT_CC = "FRSTDAT_CC";
	const MASTPAY_CC = "MASTPAY_CC";
	const BRAINTR_CC = "BRAINTR_CC";
	const CYBRSRC_CC = "CYBRSRC_CC";
	const DTCASH_CC = "DTCASH_CC";
	const OGONE_CC = "OGONE_CC";
	const SAGEPAY_CC = "SAGEPAY_CC";
	const EWAY_CC = "EWAY_CC";
	const PAYJUNC_CC = "PAYJUNC_CC";
	const PP_WSPP_CC = "PP_WSPP_CC";
	
	const PAYU = "PAYU";
	
	
	/**
	 * @var string
	 */
	protected $mail;

	/**
	 * @var ShopgateOrderCustomField[]
	 */
	protected $custom_fields;

	/**
	 * @var string
	 */
	protected $payment_method;

	/**
	 * ShopgateExternalCoupon[]
	 * @var array
	 */
	protected $external_coupons;

	/**
	 * @var ShopgateAddress
	 */
	protected $invoice_address;

	/**
	 * @var ShopgateAddress
	 */
	protected $delivery_address;

	/**
	 * @var ShopgateOrderBaseItem[]
	 */
	protected $items;

	/**
	 * @param string $value
	 */
	public function setMail($value) {
		$this->mail = $value;
	}

	/**
	 * ShopgateOrderCustomField[] | array[]
	 * @param mixed $value
	 */
	public function setCustomFields($value) {
		// TODO: $this->custom_fields = $this->validateList($value, 'ShopgateOrderCustomField');
		/**
		 * e.g.:
		 * public function validateList(array $rawList, $className) {
		 *	foreach ($rawList as $index => &$element) {
		 *		if (is_array($element)) {
		 *			// implies that the class is known (included or required)!
		 *			$element = new $className($element);
		 *		}
		 *		else if (!(is_object($element) && get_class($element) === $className)) {
		 *			unset($rawList[$index]);
		 *		}
		 *	}
		 *	return count($rawList) ? $rawList : null;
		 * }
		 */
		
		if (!is_array($value)) {
			$this->custom_fields = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = new ShopgateOrderCustomField($element);
			}
			else if ( !is_object($element) || !($element instanceof ShopgateOrderCustomField) ) {
				unset($value[$index]);
			}
		}
	
		$this->custom_fields = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setPaymentMethod($value) {
		$this->payment_method = $value;
	}

	/**
	 * mixed: ShopgateExternalCoupon[] | array[]
	 * @param array $value
	 */
	public function setExternalCoupons($value) {
		// TODO: $this->external_coupons = $this->validateList($value, 'ShopgateExternalCoupon'); // vgl. $this->setCustomFields(array)
	
		if (!is_array($value)) {
			$this->external_coupons = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = new ShopgateExternalCoupon($element);
			}
			else if ( !is_object($element) || !($element instanceof ShopgateExternalCoupon) ) {
				unset($value[$index]);
			}
		}
	
		$this->external_coupons = $value;
	}
	
	/**
	 * ShopgateAddress | array
	 * @param mixed $value
	 */
	public function setInvoiceAddress($value) {
		if (is_array($value)) {
			$value = new ShopgateAddress($value);
			$value->setIsDeliveryAddress(false);
			$value->setIsInvoiceAddress(true);
		}
		else if ( !is_object($value) || !($value instanceof ShopgateAddress) ) {
			$value = null;
		}

		$this->invoice_address = $value;
	}
	
	/**
	 * ShopgateAddress | array
	 * @param mixed $value
	 */
	public function setDeliveryAddress($value) {
		if (is_array($value)) {
			$value = new ShopgateAddress($value);
			$value->setIsDeliveryAddress(true);
			$value->setIsInvoiceAddress(false);
		}
		
		if ( !is_object($value) || !($value instanceof ShopgateAddress) ) {
			$value = null;
		}
	
		$this->delivery_address = $value;
	}
	
	/**
	 * ShopgateOrderBaseItem[] | array[]
	 * @param array $value
	 */
	public function setItems($value) {
		if (!is_array($value)) {
			$this->items = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = $this->getOrderItem($element);
			}
			
			/**
			 * TODO instanceof garantiert NICHT die korrekte Ausprägung!
			 * deshalb:
			 * - hier abstract
			 * - evtl.: parent: $this->validateList($value, '{Ausprägung}'); vgl.: $this->setCustomFields(array)
			 */
			else if ( !is_object($element) || !($element instanceof ShopgateOrderBaseItem) ) {
				unset($value[$index]);
			}
		}
	
		$this->items = $value;
	}
	
	/**
	 * @return string
	 */
	public function getMail() {
		return $this->mail;
	}

	/**
	 * @return ShopgateOrderCustomField[]
	 */
	public function getCustomFields() {
		return $this->custom_fields;
	}

	/**
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->payment_method;
	}

	/**
	 * @return array ShopgateExternalCoupon[]
	 */
	public function getExternalCoupons() {
		return $this->external_coupons;
	}

	/**
	 * @return ShopgateAddress
	 */
	public function getInvoiceAddress() {
		return $this->invoice_address;
	}

	/**
	 * @return ShopgateAddress
	 */
	public function getDeliveryAddress() {
		return $this->delivery_address;
	}

	/**
	 * @return ShopgateOrderBaseItem[]
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @param array $options
	 * @return ShopgateOrderBaseItem
	 */
	protected abstract function getOrderItem(array $options);
	
}

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

abstract class ShopgateOrderBase extends ShopgateCartBase {

	/**
	 * @var string
	 */
	protected $order_number;

	/**
	 * @var string
	 */
	protected $external_order_number;

	/**
	 * @var string
	 */
	protected $external_order_id;

	/**
	 * @var string
	 */
	protected $created_time;

	/**
	 * @var string
	 */
	protected $phone;

	/**
	 * @var string
	 */
	protected $mobile;

	/**
	 * @var string
	 */
	protected $currency;

	/**
	 * @var float
	 */
	protected $amount_complete;

	/**
	 * @var bool
	 */
	protected $is_shipping_completed;

	/**
	 * @var string
	 */
	protected $shipping_completed_time;

	/**
	 * @var bool
	 */
	protected $is_paid;

	/**
	 * @var string
	 */
	protected $payment_time;

	/**
	 * @var string
	 */
	protected $payment_transaction_number;

	/**
	 * @var ShopgateDeliveryNote[]
	 */
	protected $delivery_notes;


	/**
	 * @param string $value
	 */
	public function setOrderNumber($value) {
		$this->order_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalOrderNumber($value) {
		$this->external_order_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalOrderId($value) {
		$this->external_order_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setCreatedTime($value) {
		$this->created_time = $value;
	}

	/**
	 * @param string $value
	 */
	public function setPhone($value) {
		$this->phone = $value;
	}

	/**
	 * @param string $value
	 */
	public function setMobile($value) {
		$this->mobile = $value;
	}

	/**
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountComplete($value) {
		$this->amount_complete = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsShippingCompleted($value) {
		$this->is_shipping_completed = $value;
	}

	/**
	 * @param string $value
	 */
	public function setShippingCompletedTime($value) {
		$this->shipping_completed_time = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsPaid($value) {
		$this->is_paid = $value;
	}

	/**
	 * @param string $value
	 */
	public function setPaymentTime($value) {
		$this->payment_time = $value;
	}

	/**
	 * @param string $value
	 */
	public function setPaymentTransactionNumber($value) {
		$this->payment_transaction_number = $value;
	}

	/**
	 * mixed: ShopgateDeliveryNote[] | array[]
	 * @param array $value
	 */
	public function setDeliveryNotes($value) {
		// TODO: $this->delivery_notes = $this->validateList($value, 'ShopgateDeliveryNote');

		if (!is_array($value)) {
			$this->delivery_notes = null;
			return;
		}

		foreach ($value as $index => &$element) {
			if (is_array($element)) {
				$element = new ShopgateDeliveryNote($element);
			}
			else if ( !is_object($element) || !($element instanceof ShopgateDeliveryNote) ) {
				unset($value[$index]);
			}
		}

		$this->delivery_notes = $value;
	}


	/**
	 * @return string
	 */
	public function getOrderNumber() {
		return $this->order_number;
	}

	/**
	 * @return string
	 */
	public function getExternalOrderNumber() {
		return $this->external_order_number;
	}

	/**
	 * @return string
	 */
	public function getExternalOrderId() {
		return $this->external_order_id;
	}

	/**
	 * @return string
	 */
	public function getCreatedTime() {
		return $this->created_time;
	}

	/**
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * @return string
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * @return float
	 */
	public function getAmountComplete() {
		return $this->amount_complete;
	}

	/**
	 * @return bool
	 */
	public function getIsShippingCompleted() {
		return $this->is_shipping_completed;
	}

	/**
	 * @return string
	 */
	public function getShippingCompletedTime() {
		return $this->shipping_completed_time;
	}

	/**
	 * @return bool
	 */
	public function getIsPaid() {
		return $this->is_paid;
	}

	/**
	 * @return string
	 */
	public function getPaymentTime() {
		return $this->payment_time;
	}

	/**
	 * @return string
	 */
	public function getPaymentTransactionNumber() {
		return $this->payment_transaction_number;
	}

	/**
	 * @return ShopgateDeliveryNote[]
	 */
	public function getDeliveryNotes() {
		return $this->delivery_notes;
	}

}

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

abstract class ShopgateOrderBaseItem extends ShopgateContainer {

	/**
	 * @var string
	 */
	protected $item_number = null;

	/**
	 * @var string
	 */
	protected $item_number_public = null;

	/**
	 * @param string $value
	 */
	public function setItemNumber($value) {
		$this->item_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setItemNumberPublic($value) {
		$this->item_number_public = $value;
	}

	/**
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}

	/**
	 * return string
	 */
	public function getItemNumberPublic() {
		return $this->item_number_public;
	}

}

class ShopgateOrderItem extends ShopgateOrderBaseItem {

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

class ShopgateSyncOrderItem extends ShopgateOrderBaseItem {

	const STATUS_NEW = 'new';
	const STATUS_DELETED = 'deleted';
	const STATUS_EXISTING = 'existing';

	/**
	 * @var string either one of new, deleted, existing
	 */
	protected $status = null;

	/**
	 * @param string $value
	 */
	public function setStatus($value) {
		if (
		self::STATUS_NEW != $value &&
		self::STATUS_DELETED != $value &&
		self::STATUS_EXISTING != $value
		) {
			$value = null;
		}

		$this->status = $value;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitSyncOrderItem($this);
	}

}

class ShopgateExternalOrderItem extends ShopgateOrderItem {

	/**
	 * @var string
	 */
	protected $description = null;

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


class ShopgateOrderItemOption extends ShopgateContainer {
	protected $name;
	protected $value;
	protected $additional_amount_with_tax;
	protected $value_number;
	protected $option_number;


	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @param string $value
	 */
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValueNumber($value) {
		$this->value_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setOptionNumber($value) {
		$this->option_number = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return int
	 */
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}

	/**
	 * @return string
	 */
	public function getValueNumber() {
		return $this->value_number;
	}

	/**
	 * @return string
	 */
	public function getOptionNumber() {
		return $this->option_number;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemOption($this);
	}
}

class ShopgateOrderItemInput extends ShopgateContainer {
	protected $input_number;
	protected $type;
	protected $additional_amount_with_tax;
	protected $label;
	protected $user_input;
	protected $info_text;
	
	
	##########
	# Setter #
	##########

	/**
	 * @param string $value
	 */
	public function setInputNumber($value) {
		$this->input_number = $value;
	}
	
	/**
	 * @param string $value "text"|"image"
	 */
	public function setType($value) {
		$this->type = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setLabel($value) {
		$this->label = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setUserInput($value) {
		$this->user_input = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setInfoText($value) {
		$this->info_text = $value;
	}
	
	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getInputNumber() {
		return $this->input_number;
	}
	
	/**
	 * @return string "text"|"image"
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @return float
	 */
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string
	 */
	public function getUserInput() {
		return $this->user_input;
	}
	
	/**
	 * @return string
	 */
	public function getInfoText() {
		return $this->info_text;
	}
	
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemInput($this);
	}
}

class ShopgateOrderItemAttribute extends ShopgateContainer {
	protected $name;
	protected $value;


	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemAttribute($this);
	}
}

class ShopgateShippingInfo extends ShopgateContainer {
	protected $name;
	protected $description;
	protected $amount;
	protected $weight;
	protected $api_response;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitShippingInfo($this);
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
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->description = $value;
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
	 * @return int
	 */
	public function getWeight() {
		return $this->weight;
	}
	/**
	 *
	 * @param int $value
	 */
	public function setWeight($value) {
		$this->weight = $value;
	}
	
	/**
	 *
	 * @return mixed[]
	 */
	public function getApiResponse() {
		return $this->api_response;
	}
	/**
	 *
	 * @param string|mixed[] $value
	 */
	public function setApiResponse($value) {
		if(is_string($value)) {
			$value = $this->jsonDecode($value, true);
		}
		
		$this->api_response = $value;
	}
}

class ShopgateDeliveryNote extends ShopgateContainer {
	// shipping groups
	const DHL			= "DHL"; // DHL
	const DHLEXPRESS	= "DHLEXPRESS"; // DHLEXPRESS
	const DP			= "DP"; // Deutsche Post
	const DPD			= "DPD"; // Deutscher Paket Dienst
	const FEDEX			= "FEDEX"; // FedEx
	const GLS			= "GLS"; // GLS
	const HLG			= "HLG"; // Hermes
	const OTHER			= "OTHER"; // Anderer Lieferant
	const TNT			= "TNT"; // TNT
	const TOF			= "TOF"; // Trnas-o-Flex
	const UPS			= "UPS"; // UPS
	const USPS			= "USPS"; // USPS

	// shipping types
	const MANUAL		= "MANUAL";
	const USPS_API_V1	= "USPS_API_V1";
	const UPS_API_V1	= "UPS_API_V1";
	
	protected $shipping_service_id = ShopgateDeliveryNote::DHL;
	protected $tracking_number = "";
	protected $shipping_time = null;

	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setShippingServiceId($value) {
		$this->shipping_service_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setTrackingNumber($value) {
		$this->tracking_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setShippingTime($value) {
		$this->shipping_time = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getShippingServiceId() {
		return $this->shipping_service_id;
	}

	/**
	 * @return string
	 */
	public function getTrackingNumber() {
		return $this->tracking_number;
	}

	/**
	 * @return string
	 */
	public function getShippingTime() {
		return $this->shipping_time;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderDeliveryNote($this);
	}
}

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
		$v->visitExtraCost($this);
	}

}


abstract class ShopgateCoupon extends ShopgateContainer {
	
	protected $order_index;
	
	protected $code;
	protected $name;
	protected $description;
	protected $amount;
	protected $amount_net;
	protected $amount_gross;
	protected $tax_type = 'auto';
	protected $currency;
	protected $is_free_shipping;
	protected $internal_info;
	
	##########
	# Setter #
	##########
	
	/**
	 * @param int $value
	 */
	public function setOrderIndex($value) {
		$this->order_index = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCode($value) {
		$this->code = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}
	
	/**
	 *
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->description = $value;
	}
	
	/**
	 * @param float $value
	 * @deprecated
	 */
	public function setAmount($value) {
		$this->amount = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAmountNet($value) {
		$this->amount_net = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAmountGross($value) {
		$this->amount_gross = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setTaxType($value) {
		$this->tax_type = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsFreeShipping($value) {
		$this->is_free_shipping = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setInternalInfo($value) {
		$this->internal_info = $value;
	}
	
	##########
	# Getter #
	##########

	/**
	 * @return int
	 */
	public function getOrderIndex() {
		return $this->order_index;
	}
	
	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @return float
	 * @deprecated
	 */
	public function getAmount() {
		return $this->amount;
	}
	
	/**
	 * @return float
	 */
	public function getAmountNet() {
		return $this->amount_net;
	}
	
	/**
	 * @return float
	 */
	public function getAmountGross() {
		return $this->amount_gross;
	}
	
	/**
	 * @return string
	 */
	public function getTaxType() {
		return $this->tax_type;
	}
	
	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}
	
	/**
	 * @return bool
	 */
	public function getIsFreeShipping() {
		return $this->is_free_shipping;
	}
	
	/**
	 * @return string
	 */
	public function getInternalInfo() {
		return $this->internal_info;
	}
	
}

class ShopgateExternalCoupon extends ShopgateCoupon {
	
	protected $is_valid;
	protected $not_valid_message;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalCoupon($this);
	}
	
	##########
	# Setter #
	##########
	
	/**
	 * @param bool $value
	 */
	public function setIsValid($value) {
		$this->is_valid = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setNotValidMessage($value) {
		$this->not_valid_message = $value;
	}
	
	##########
	# Getter #
	##########
	
	/**
	 * @return bool
	 */
	public function getIsValid() {
		return $this->is_valid;
	}
	
	/**
	 * @return string
	 */
	public function getNotValidMessage() {
		return $this->not_valid_message;
	}
}

class ShopgateShopgateCoupon extends ShopgateCoupon {

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCoupon($this);
	}
	
	##########
	# Setter #
	##########
	
	##########
	# Getter #
	##########

}

class ShopgateOrderCustomField extends ShopgateContainer {
	protected $label;
	protected $internal_field_name;
	protected $value;
	
	
	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setLabel($value) {
		$this->label = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setInternalFieldName($value) {
		$this->internal_field_name = $value;
	}
	
	/**
	 * @param mixed $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	
	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string
	 */
	public function getInternalFieldName() {
		return $this->internal_field_name;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
	
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderCustomField($this);
	}
}