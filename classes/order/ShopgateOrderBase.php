<?php

require_once 'ShopgateCartBase.php';
//require_once 'ShopgateDeliveryNote.php';

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
