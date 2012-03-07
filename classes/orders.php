<?php
class ShopgateOrder extends ShopgateObject {
	const SHOPGATE = "SHOPGATE";
	const PREPAY = "PREPAY";
	const CC = "CC";
	const INVOICE = "INVOICE";
	const DEBIT = "DEBIT";
	const COD = "COD";
	const PAYPAL = "PAYPAL";

	private $order_number;
	private $customer_number;

	private $external_order_number;
	private $external_order_id;

	private $external_customer_number;
	private $external_customer_id;

	private $mail;
	private $phone;
	private $mobile;

	private $confirm_shipping_url;

	private $created_time;

	private $payment_method;

	private $is_paid;

	private $payment_time;
	private $payment_transaction_number;
	private $payment_infos;

	private $is_shipping_blocked;
	private $is_shipping_completed;
	private $shipping_completed_time;

	private $amount_items;
	private $amount_shipping;
	private $amount_payment;
	private $amount_complete;
	private $currency;
	private $is_test;
	private $is_storno;

	private $invoice_address;
	private $delivery_address;

	private $items;

	private $delivery_notes;

	/**
	 * @param array $data  Ein Array mit allen Informationen der Bestellung
	 */
	public function __construct( $data = null ) {
		if( is_array( $data ) ) {
			foreach( $data as $key => $value ) {

				if( $key == "delivery_address" || $key == "invoice_address" ) {
					$value = new ShopgateAddress($value);
				} else if( $key == "delivery_notes" ) {
					$notes = array();
					foreach ( $value as $note ) $notes[] = new ShopgateOrderDeliveryNote( $note );
					$value = $notes;
				} else if( $key == "items" ) {
					$items = array();
					foreach ($value as $item) $items[] = new ShopgateOrderItem( $item );
					$value = $items;
				}

				$this->{$key} = $value;
			}
		}
	}


	/**
	 * The Shopgate order number
	 *
	 * Format: Exact 10 Digits
	 * Sample: 1012001234
	 *
	 * @return string
	 */
	public function getOrderNumber() { return $this->order_number; }

	/**
	 * The customer number by shopgate
	 *
	 * Format: Exact 5 Digits
	 * Sample: 101234
	 *
	 * @return string
	 */
	public function getCustomerNumber() { return $this->customer_number; }

	/**
	 * The order number in your system
	 *
	 *  @return string
	 */
	public function getExternalOrderNumber() { return $this->external_order_number; }

	/**
	 * The order id in your system
	 *
	 * @return string
	 */
	public function getExternalOrderId() { return $this->external_order_id; }

	/**
	 * The customer number in your system
	 *
	 * @return string
	 */
	public function getExternalCustomerNumber() { return $this->external_customer_number; }

	/**
	 * The customer id in your system
	 *
	 * @return string
	 */
	public function getExternalCustomerId() { return $this->external_customer_id ; }

	/**
	 * The eMail-Adress of the customer
	 *
	 * @return string
	 */
	public function getMail() { return $this->mail; }

	/**
	 * The phone-number of the cutsomer
	 *
	 * Sample: +49123456789
	 *
	 * @return string
	 */
	public function getPhone() { return $this->phone; }

	/**
	 * The mobile-number of the cutsomer
	 *
	 * Sample: +49123456789
	 *
	 * @return string
	 */
	public function getMobile() { return $this->mobile; }

	/**
	 * The confirm shipping url to confirm the shipping manual
	 *
	 *  @return string
	 */
	public function getConfirmShippingUrl() { return $this->confirm_shipping_url ; }

	/**
	 * The DateTime when the order was created
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getCreatedTime($format = "") {
		$time = $this->created_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}

	/**
	 * The payment method for the order
	 *
	 * Sample: <ul><li>DEBIT</li><li>SHOPGATE</li><li>PREPAY</li><li>CC</li><li>INVOICE</li><li>DEBIT</li><li>COD</li><li>PAYPAL</li></ul>
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/de
	 * @return string
	 */
	public function getPaymentMethod() { return $this->payment_method; }

	/**
	 * Is the order is payed
	 *
	 * @return bool
	 */
	public function getIsPaid() { return (bool) $this->is_paid; }

	/**
	 * The Time when the order was payed
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getPaymentTime($format="") {
		$time = $this->payment_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}

	/**
	 * The Transactioncode for some paymentproviders
	 *
	 * @return string
	 */
	public function getPaymentTransactionNumber() { return $this->payment_transaction_number; }

	public function getPaymentInfos() { return $this->payment_infos; }

	/**
	 * Is the shipping is blocked
	 *
	 * @return bool
	 */
	public function getIsShippingBlocked() { return (bool) $this->is_shipping_blocked; }

	/**
	 * Is the Shipping is completed
	 */
	public function getIsShippingCompleted() { return (bool) $this->is_shipping_completed; }

	/**
	 * The Time when the Shipping was set completed
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getShippingCompletedTime($format='') {
		$time = $this->shipping_completed_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}

	/**
	 * The full amount of Items
	 *
	 * @return float
	 */
	public function getAmountItems() { return $this->amount_items; }

	/**
	 * The shipping price
	 *
	 * @return float
	 */
	public function getAmountShipping() { return $this->amount_shipping; }

	/**
	 * Amount for Payment
	 *
	 * @return float
	 */
	public function getAmountPayment() { return $this->amount_payment; }

	/**
	 * Complete amount for the order
	 *
	 * @return float
	 */
	public function getAmountComplete() { return $this->amount_complete; }

	/**
	 * The currency for this order
	 *
	 * The currency ISO-Code from ISO-4217
	 *
	 * Sample: <ul><li>EUR</li><li>CHF</li></ul>
	 *
	 * @see http://de.wikipedia.org/wiki/ISO_4217
	 * @return string
	 */
	public function getCurency() { return $this->currency; }

	/**
	 * Is this flag is set to 1, the Order is a Test
	 *
	 * @return bool
	 */
	public function getIsTest() { return (bool) $this->is_test; }

	/**
	 * Is this flag is set to 1 the order is cancled
	 *
	 * @return bool
	 */
	public function getIsStorno() { return (bool) $this->is_storno; }

	/**
	 * The invoice address of the customer
	 *
	 * @return ShopgateAddress
	 */
	public function getInvoiceAddress() { return $this->invoice_address; }

	/**
	 * The delivery address of the customer
	 *
	 * @return ShopgateAddress
	 */
	public function getDeliveryAddress() { return $this->delivery_address; }

	/**
	 * The list of itmes in the order
	 *
	 * @return ShopgateOrderItem[]
	 */
	public function getItems() { return $this->items; }

	/**
	 * The list of delivery Notes of the order
	 *
	 * @return ShopgateOrderDeliveryNote[]
	 */
	public function getDeliveryNotes() { return $this->delivery_notes; }
}

class ShopgateOrderItem extends ShopgateObject {
	private $item_number;

	private $quantity;

	private $name;

	private $unit_amount;
	private $unit_amount_with_tax;

	private $tax_percent;

	private $currency;

	private $internal_order_info;

	private $options = array();

	/**
	 * Der Konstruktor der ShopgateOrderItem-Klasse.
	 *
	 * @param array $data Ein Array mit allen Informationen zu einem Produkt der Bestellung.
	 */
	public function __construct( $data = null ) {
		if( is_array( $data ) ) {
			foreach($data as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Returns the name value
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the item_number value
	 *
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}

	/**
	 * Returns the unit_amount value
	 *
	 * @return float
	 */
	public function getUnitAmount() {
		return $this->unit_amount;
	}

	/**
	 * Returns the unit_amount_with_tax value
	 *
	 * @return float
	 */
	public function getUnitAmountWithTax() {
		return $this->unit_amount_with_tax;
	}

	/**
	 * Returns the quantity value
	 *
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	* Returns the tax_percent value
	*
	* @return float
	*/
	public function getTaxPercent() {
		return $this->tax_percent;
	}

	/**
	 * Returns the currency value
	 *
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * Returns the internal_order_info value
	 *
	 * @return string
	 */
	public function getInternalOrderInfo() { return $this->internal_order_info; }

	/**
	 * Returns the options value
	 *
	 * @return ShopgateOrderItemOption[]
	 */
	public function getOptions() { return $this->options; }
}

class ShopgateOrderItemOption extends ShopgateObject {
	private $name;
	private $value;
	private $additional_amount_with_tax;
	private $value_number;
	private $option_number;

	public function __construct($data = array()) {
		if(!empty($data)) {
			$this->setName($data["name"]);
			$this->setValue($data["value"]);
			$this->setAdditionalUnitAmountWithTax($data["additional_amount_with_tax"]);
			$this->setValueNumber($data["value_number"]);
			$this->setOptionNumber($data["option_number"]);
		}
	}

	/**
	 * Returns the name value
	 *
	 * @return String
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the value value
	 *
	 * @return String
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Returns the additional_unit_amount_with_tax value
	 *
	 * @return int
	 */
	public function getAdditionalUnitAmountWithTax() {
		return $this->additional_unit_amount_with_tax;
	}

	/**
	 * Returns the value_number value
	 *
	 * @return String
	 */
	public function getValueNumber() {
		return $this->value_number;
	}

	/**
	 * Returns the option_number value
	 *
	 * @return String
	 */
	public function getOptionNumber() {
		return $this->option_number;
	}
}

class ShopgateDeliveryNote extends ShopgateObject {
	const DHL = "DHL"; // DHL
	const DHLEXPRESS = "DHLEXPRESS"; // DHLEXPRESS
	const DP = "DP"; // Deutsche Post
	const DPD = "DPD"; // Deutscher Paket Dienst
	const FEDEX = "FEDEX"; // FedEx
	const GLS = "GLS"; // GLS
	const HLG = "HLG"; // Hermes
	const OTHER = "OTHER"; // Anderer Lieferant
	const TNT = "TNT"; // TNT
	const TOF = "TOF"; // Trnas-o-Flex
	const UPS = "UPS"; // UPS

	private $shipping_service_id = ShopgateOrderDeliveryNote::DHL;
	private $tracking_number = "";
	private $shipping_time = null;
	private $delivery_items = array();

	/**
	 * Der Konstruktor der ShopgateDeliveryNote-Klasse.
	 *
	 * @param array $data 	Ein Array mit allen Information über die Lieferung der Bestellung.
	 */
	public function __construct( $data = null ) {
		if( is_array( $data ) ) {
			foreach($data as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Returns the shipping_service_id value
	 *
	 * @return string
	 */
	public function getShippingType() {
		return $this->shipping_type;
	}

	/**
	 * Returns the tracking_number value
	 *
	 * @return string
	 */
	public function getTrackingNumber() {
		return $this->tracking_number;
	}

	/**
	 * Returns the tracking_number value
	 *
	 * @return string
	 */
	public function getShippingTime() {
		return $this->tracking_number;
	}

	/**
	 * Returns the items value
	 *
	 * @return array
	 */
	public function getDeliveryItems() {
		return $this->delivery_items;
	}
}