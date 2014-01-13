<?php

require_once '../core.php';

//require_once '../core/ShopgateContainer.php';
//require_once '../customer/ShopgateAddress.php';
//require_once 'ShopgateOrderCustomField.php';
//require_once 'ShopgateExternalCoupon.php';
//require_once 'ShopgateOrderBaseItem.php';

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