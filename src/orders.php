<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
abstract class ShopgateCartBase extends ShopgateContainer
{
    const SHOPGATE = 'SHOPGATE';
    // Prepayment
    const PREPAY     = 'PREPAY';
    const PAYONE_PRP = 'PAYONE_PRP';
    const SG_PREPAY  = 'SG_PREPAY';
    // Debit
    const DEBIT      = 'DEBIT';
    const PAYMRW_DBT = 'PAYMRW_DBT';
    const PAYONE_DBT = 'PAYONE_DBT';
    // Cash On Delivery
    const COD        = 'COD';
    const COLL_STORE = 'COLL_STORE';
    // Installment
    const ACCRD_INS = 'ACCRD_INS';
    const PAYOL_INS = 'PAYOL_INS';
    // Invoice
    const INVOICE    = 'INVOICE';
    const ACCRD_INV  = 'ACCRD_INV';
    const KLARNA_INV = 'KLARNA_INV';
    const BILLSAFE   = 'BILLSAFE';
    const MSTPAY_INV = 'MSTPAY_INV';
    const PAYMRW_INV = 'PAYMRW_INV';
    const PAYONE_INV = 'PAYONE_INV';
    const SG_INVOICE = 'SG_INVOICE';
    const WCARD_INV  = 'WCARD_INV';
    const PAYONE_KLV = 'PAYONE_KLV';
    const PAYOL_INV  = 'PAYOL_INV';
    // Paypal
    const PAYPAL     = 'PAYPAL';
    const PPAL_PLUS  = 'PPAL_PLUS';
    const BRAINTR_PP = 'BRAINTR_PP';
    const CMPTOP_PP  = 'CMPTOP_PP';
    const MASTPAY_PP = 'MASTPAY_PP';
    const PAYONE_PP  = 'PAYONE_PP';
    const SAGEPAY_PP = 'SAGEPAY_PP';
    const SG_PAYPAL  = 'SG_PAYPAL';
    const SIX_PP     = 'SIX_PP';
    const WCARD_PP   = 'WCARD_PP';
    // Credit Card
    const CC         = 'CC';
    const AUTHN_CC   = 'AUTHN_CC';
    const BCLEPDQ_CC = 'BCLEPDQ_CC';
    const BNSTRM_CC  = 'BNSTRM_CC';
    const BRAINTR_CC = 'BRAINTR_CC';
    const CHASE_CC   = 'CHASE_CC';
    const CMPTOP_CC  = 'CMPTOP_CC';
    const CONCAR_CC  = 'CONCAR_CC';
    const CRDSTRM_CC = 'CRDSTRM_CC';
    const CREDITCARD = 'CREDITCARD';
    const CYBRSRC_CC = 'CYBRSRC_CC';
    const DRCPAY_CC  = 'DRCPAY_CC';
    const DTCASH_CC  = 'DTCASH_CC';
    const DT_CC      = 'DT_CC';
    const EFSNET_CC  = 'EFSNET_CC';
    const ELAVON_CC  = 'ELAVON_CC';
    const EPAY_CC    = 'EPAY_CC';
    const EWAY_CC    = 'EWAY_CC';
    const EXACT_CC   = 'EXACT_CC';
    const FRSTDAT_CC = 'FRSTDAT_CC';
    const GAMEDAY_CC = 'GAMEDAY_CC';
    const GARANTI_CC = 'GARANTI_CC';
    const GESTPAY_CC = 'GESTPAY_CC';
    const HDLPAY_CC  = 'HDLPAY_CC';
    const HIPAY      = 'HIPAY';
    const HITRUST_CC = 'HITRUST_CC';
    const INSPIRE_CC = 'INSPIRE_CC';
    const INSTAP_CC  = 'INSTAP_CC';
    const INTUIT_CC  = 'INTUIT_CC';
    const IRIDIUM_CC = 'IRIDIUM_CC';
    const LITLE_CC   = 'LITLE_CC';
    const MASTPAY_CC = 'MASTPAY_CC';
    const MERESOL_CC = 'MERESOL_CC';
    const MERWARE_CC = 'MERWARE_CC';
    const MODRPAY_CC = 'MODRPAY_CC';
    const MONERIS_CC = 'MONERIS_CC';
    const MSTPAY_CC  = 'MSTPAY_CC';
    const NELTRAX_CC = 'NELTRAX_CC';
    const NETBILL_CC = 'NETBILL_CC';
    const NETREGS_CC = 'NETREGS_CC';
    const NOCHEX_CC  = 'NOCHEX_CC';
    const OGONE_CC   = 'OGONE_CC';
    const OPTIMAL_CC = 'OPTIMAL_CC';
    const PAY4ONE_CC = 'PAY4ONE_CC';
    const PAYBOX_CC  = 'PAYBOX_CC';
    const PAYEXPR_CC = 'PAYEXPR_CC';
    const PAYFAST_CC = 'PAYFAST_CC';
    const PAYFLOW_CC = 'PAYFLOW_CC';
    const PAYJUNC_CC = 'PAYJUNC_CC';
    const PAYONE_CC  = 'PAYONE_CC';
    const PAYZEN_CC  = 'PAYZEN_CC';
    const PLUGNPL_CC = 'PLUGNPL_CC';
    const PP_WSPP_CC = 'PP_WSPP_CC';
    const PSIGATE_CC = 'PSIGATE_CC';
    const PSL_CC     = 'PSL_CC';
    const PXPAY_CC   = 'PXPAY_CC';
    const QUIKPAY_CC = 'QUIKPAY_CC';
    const REALEX_CC  = 'REALEX_CC';
    const SAGEPAY_CC = 'SAGEPAY_CC';
    const SAGE_CC    = 'SAGE_CC';
    const SAMURAI_CC = 'SAMURAI_CC';
    const SCPTECH_CC = 'SCPTECH_CC';
    const SCP_AU_CC  = 'SCP_AU_CC';
    const SECPAY_CC  = 'SECPAY_CC';
    const SG_CC      = 'SG_CC';
    const SIX_CC     = 'SIX_CC';
    const SKIPJCK_CC = 'SKIPJCK_CC';
    const SKRILL_CC  = 'SKRILL_CC';
    const STRIPE_CC  = 'STRIPE_CC';
    const TELECSH_CC = 'TELECSH_CC';
    const TRNSFST_CC = 'TRNSFST_CC';
    const TRUSTCM_CC = 'TRUSTCM_CC';
    const USAEPAY_CC = 'USAEPAY_CC';
    const VALITOR_CC = 'VALITOR_CC';
    const VERIFI_CC  = 'VERIFI_CC';
    const VIAKLIX_CC = 'VIAKLIX_CC';
    const WCARDS_CC  = 'WCARDS_CC';
    const WIRECRD_CC = 'WIRECRD_CC';
    const WLDPDIR_CC = 'WLDPDIR_CC';
    const WLDPOFF_CC = 'WLDPOFF_CC';
    // ClickandBuy
    const CNB        = 'CNB';
    const SG_CNB     = 'SG_CNB';
    const MCM        = 'MCM';
    const UPAID_MCM  = 'UPAID_MCM';
    const PAYU       = 'PAYU';
    const REDIRECTCC = 'REDIRECTCC';
    const WORLDLINE  = 'WORLDLINE';
    // SOFORT Überweisung
    const SUE        = 'SUE';
    const HDLPAY_SUE = 'HDLPAY_SUE';
    const MSTPAY_SUE = 'MSTPAY_SUE';
    const PAYONE_SUE = 'PAYONE_SUE';
    const SG_SUE     = 'SG_SUE';
    const SKRILL_SUE = 'SKRILL_SUE';
    const WCARD_SUE  = 'WCARD_SUE';
    // Giropay
    const PAYONE_GP = 'PAYONE_GP';
    // iDEAL
    const PAYONE_IDL     = 'PAYONE_IDL';
    const SIX_IDEAL      = 'SIX_IDEAL';
    const SKRILL_IDL     = 'SKRILL_IDL';
    const AMAZON_PAYMENT = 'MWS';
    // MERCHANT_PAYMENT
    const MERCH_PM   = 'MERCH_PM';
    const MERCH_PM_2 = 'MERCH_PM_2';
    const MERCH_PM_3 = 'MERCH_PM_3';

    protected $customer_number;

    protected $customer_ip;

    protected $external_order_number;

    protected $external_order_id;

    protected $external_customer_number;

    protected $external_customer_id;

    protected $external_customer_group_id;

    protected $mail;

    protected $phone;

    protected $mobile;

    protected $client;

    protected $custom_fields;

    protected $shipping_group;

    protected $shipping_type;

    protected $shipping_infos;

    protected $payment_method;

    protected $payment_group;

    protected $amount_items;

    protected $amount_shipping;

    protected $amount_shop_payment;

    protected $payment_tax_percent;

    protected $amount_shopgate_payment;

    protected $amount_complete;

    protected $currency;

    protected $invoice_address;

    protected $delivery_address;

    protected $external_coupons = array();

    protected $shopgate_coupons = array();

    protected $items = array();

    protected $tracking_get_parameters = array();

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setCustomerNumber($value)
    {
        $this->customer_number = $value;
    }

    /**
     * @param $ip - ip of the customer
     */
    public function setCustomerIp($ip)
    {
        $this->customer_ip = $ip;
    }

    /**
     * @param string $value
     */
    public function setExternalOrderNumber($value)
    {
        $this->external_order_number = $value;
    }

    /**
     * @param string $value
     */
    public function setExternalOrderId($value)
    {
        $this->external_order_id = $value;
    }

    /**
     * @param string $value
     */
    public function setExternalCustomerNumber($value)
    {
        $this->external_customer_number = $value;
    }

    /**
     * @param string $value
     */
    public function setExternalCustomerId($value)
    {
        $this->external_customer_id = $value;
    }

    /**
     * @param string $value
     */
    public function setMail($value)
    {
        $this->mail = $value;
    }

    /**
     * @param string $value
     */
    public function setPhone($value)
    {
        $this->phone = $value;
    }

    /**
     * @param string $value
     */
    public function setMobile($value)
    {
        $this->mobile = $value;
    }

    /**
     * @param ShopgateClient $value
     */
    public function setClient($value)
    {
        if (!($value instanceof ShopgateClient) && !is_array($value)) {
            $this->client = null;

            return;
        }

        if (is_array($value)) {
            $value = new ShopgateClient($value);
        }

        $this->client = $value;
    }

    /**
     * @param string $value
     */
    public function setExternalCustomerGroupId($value)
    {
        $this->external_customer_group_id = $value;
    }

    /**
     * @param ShopgateOrderCustomField[] $value
     */
    public function setCustomFields($value)
    {
        if (!is_array($value)) {
            $this->custom_fields = array();
        }

        foreach ($value as $index => &$element) {
            if ((!is_object($element) || !($element instanceof ShopgateOrderCustomField)) && !is_array($element)) {
                unset($value[$index]);
                continue;
            }

            if (is_array($element)) {
                $element = new ShopgateOrderCustomField($element);
            }
        }

        $this->custom_fields = $value;
    }

    /**
     *
     * @param string $value
     */
    public function setShippingGroup($value)
    {
        $this->shipping_group = $value;
    }

    /**
     *
     * @param string $value
     */
    public function setShippingType($value)
    {
        $this->shipping_type = $value;
    }

    /**
     *
     * @param ShopgateShippingInfo $value
     */
    public function setShippingInfos($value)
    {
        if (!is_object($value) && !($value instanceof ShopgateShippingInfo) && !is_array($value)) {
            $this->shipping_infos = null;

            return;
        }

        if (is_array($value)) {
            $value = new ShopgateShippingInfo($value);
        }

        $this->shipping_infos = $value;
    }

    /**
     * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
     *
     * @param string $value
     */
    public function setPaymentGroup($value)
    {
        $this->payment_group = $value;
    }

    /**
     * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
     *
     * @param string $value
     */
    public function setPaymentMethod($value)
    {
        $this->payment_method = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountItems($value)
    {
        $this->amount_items = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountShipping($value)
    {
        $this->amount_shipping = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountShopPayment($value)
    {
        $this->amount_shop_payment = $value;
    }

    /**
     * @param float $value
     */
    public function setPaymentTaxPercent($value)
    {
        $this->payment_tax_percent = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountShopgatePayment($value)
    {
        $this->amount_shopgate_payment = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountComplete($value)
    {
        $this->amount_complete = $value;
    }

    /**
     * @see http://de.wikipedia.org/wiki/ISO_4217
     *
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    /**
     * @param ShopgateAddress|mixed[] $value
     */
    public function setInvoiceAddress($value)
    {
        if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
            $this->invoice_address = null;

            return;
        }

        if (is_array($value)) {
            $value = new ShopgateAddress($value);
            $value->setIsDeliveryAddress(false);
            $value->setIsInvoiceAddress(true);
        }

        $this->invoice_address = $value;
    }

    /**
     * @param ShopgateAddress|mixed[] $value
     */
    public function setDeliveryAddress($value)
    {
        if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
            $this->delivery_address = null;

            return;
        }

        if (is_array($value)) {
            $value = new ShopgateAddress($value);
            $value->setIsDeliveryAddress(true);
            $value->setIsInvoiceAddress(false);
        }

        $this->delivery_address = $value;
    }

    /**
     * @param ShopgateExternalCoupon[] $value
     */
    public function setExternalCoupons($value)
    {
        if (!is_array($value)) {
            $this->external_coupons = null;

            return;
        }

        foreach ($value as $index => &$element) {
            if ((!is_object($element) || !($element instanceof ShopgateExternalCoupon)) && !is_array($element)) {
                unset($value[$index]);
                continue;
            }

            if (is_array($element)) {
                $element = new ShopgateExternalCoupon($element);
            }
        }

        $this->external_coupons = $value;
    }

    /**
     * @param ShopgateShopgateCoupon[] $value
     */
    public function setShopgateCoupons($value)
    {
        if (!is_array($value)) {
            $this->shopgate_coupons = null;

            return;
        }

        foreach ($value as $index => &$element) {
            if ((!is_object($element) || !($element instanceof ShopgateShopgateCoupon)) && !is_array($element)) {
                unset($value[$index]);
                continue;
            }

            if (is_array($element)) {
                $element = new ShopgateShopgateCoupon($element);
            }
        }

        $this->shopgate_coupons = $value;
    }

    /**
     * @param ShopgateOrderItem []|mixed[][] $value
     */
    public function setItems($value)
    {
        if (!is_array($value)) {
            $this->items = null;

            return;
        }

        foreach ($value as $index => &$element) {
            if ((!is_object($element) || !($element instanceof ShopgateOrderItem)) && !is_array($element)) {
                unset($value[$index]);
                continue;
            }

            if (is_array($element)) {
                $element = new ShopgateOrderItem($element);
            }
        }

        $this->items = $value;
    }

    /**
     * @param array $value
     */
    public function setTrackingGetParameters($value)
    {
        $this->tracking_get_parameters = (array)$value;
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getCustomerNumber()
    {
        return $this->customer_number;
    }

    /**
     * @return string|null
     */
    public function getCustomerIp()
    {
        return $this->customer_ip;
    }

    /**
     * @return string
     */
    public function getExternalCustomerNumber()
    {
        return $this->external_customer_number;
    }

    /**
     * @return string
     */
    public function getExternalCustomerId()
    {
        return $this->external_customer_id;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @return ShopgateClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getExternalCustomerGroupId()
    {
        return $this->external_customer_group_id;
    }

    /**
     * @return ShopgateOrderCustomField[]
     */
    public function getCustomFields()
    {
        if (!is_array($this->custom_fields)) {
            $this->custom_fields = array();
        }

        return $this->custom_fields;
    }

    /**
     *
     * @return string
     */
    public function getShippingGroup()
    {
        return $this->shipping_group;
    }

    /**
     *
     * @return string
     */
    public function getShippingType()
    {
        return $this->shipping_type;
    }

    /**
     *
     * @return ShopgateShippingInfo
     */
    public function getShippingInfos()
    {
        return $this->shipping_infos;
    }

    /**
     * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
     * @return string
     */
    public function getPaymentGroup()
    {
        return $this->payment_group;
    }

    /**
     * @return float
     */
    public function getAmountItems()
    {
        return $this->amount_items;
    }

    /**
     * @return float
     */
    public function getAmountShipping()
    {
        return $this->amount_shipping;
    }

    /**
     * @return float
     */
    public function getAmountShopPayment()
    {
        return $this->amount_shop_payment;
    }

    /**
     * @return float
     */
    public function getPaymentTaxPercent()
    {
        return $this->payment_tax_percent;
    }

    /**
     * @return float
     */
    public function getAmountShopgatePayment()
    {
        return $this->amount_shopgate_payment;
    }

    /**
     * @return float
     */
    public function getAmountComplete()
    {
        return $this->amount_complete;
    }

    /**
     * @see http://de.wikipedia.org/wiki/ISO_4217
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return ShopgateAddress
     */
    public function getInvoiceAddress()
    {
        return $this->invoice_address;
    }

    /**
     * @return ShopgateAddress
     */
    public function getDeliveryAddress()
    {
        return $this->delivery_address;
    }

    /**
     * @return ShopgateExternalCoupon[]
     */
    public function getExternalCoupons()
    {
        return $this->external_coupons;
    }

    /**
     * @return ShopgateShopgateCoupon[]
     */
    public function getShopgateCoupons()
    {
        return $this->shopgate_coupons;
    }

    /**
     * @return ShopgateOrderItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getTrackingGetParameters()
    {
        return $this->tracking_get_parameters;
    }
}

class ShopgateCart extends ShopgateCartBase
{
    protected $internal_cart_info;

    /**
     * @return string
     */
    public function getInternalCartInfo()
    {
        return $this->internal_cart_info;
    }

    /**
     * @param string $value
     */
    public function setInternalCartInfo($value)
    {
        $this->internal_cart_info = $value;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitCart($this);
    }
}

class ShopgateOrder extends ShopgateCartBase
{
    protected $order_number;

    protected $confirm_shipping_url;

    protected $created_time;

    protected $is_paid;

    protected $payment_time;

    protected $payment_transaction_number;

    protected $payment_infos;

    protected $is_shipping_blocked;

    protected $is_shipping_completed;

    protected $shipping_completed_time;

    protected $shipping_tax_percent;

    protected $is_test;

    protected $is_storno;

    protected $is_customer_invoice_blocked;

    protected $update_shipping = 0;

    protected $update_payment = 0;

    protected $delivery_notes = array();

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrder($this);
    }

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setOrderNumber($value)
    {
        $this->order_number = $value;
    }

    /**
     * @param string $value
     */
    public function setConfirmShippingUrl($value)
    {
        $this->confirm_shipping_url = $value;
    }

    /**
     * @see http://www.php.net/manual/de/function.date.php
     * @see http://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string $value
     */
    public function setCreatedTime($value)
    {
        $this->created_time = $value;
    }

    /**
     * @param int $value
     */
    public function setIsPaid($value)
    {
        $this->is_paid = $value;
    }

    /**
     * @see http://www.php.net/manual/de/function.date.php
     * @see http://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string $value
     */
    public function setPaymentTime($value)
    {
        $this->payment_time = $value;
    }

    /**
     * @param string $value
     */
    public function setPaymentTransactionNumber($value)
    {
        $this->payment_transaction_number = $value;
    }

    /**
     * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
     *
     * @param mixed[] $value An array of additional information about the payment depending on the payment type
     */
    public function setPaymentInfos($value)
    {
        $this->payment_infos = $value;
    }

    /**
     * @param int $value
     */
    public function setIsShippingBlocked($value)
    {
        $this->is_shipping_blocked = $value;
    }

    /**
     * @param int $value
     */
    public function setIsShippingCompleted($value)
    {
        $this->is_shipping_completed = $value;
    }

    /**
     * @see http://www.php.net/manual/de/function.date.php
     * @see http://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string $value
     */
    public function setShippingCompletedTime($value)
    {
        $this->shipping_completed_time = $value;
    }

    /**
     * @param float $value
     */
    public function setShippingTaxPercent($value)
    {
        $this->shipping_tax_percent = $value;
    }

    /**
     * @param int $value
     */
    public function setIsTest($value)
    {
        $this->is_test = $value;
    }

    /**
     * @param int $value
     */
    public function setIsStorno($value)
    {
        $this->is_storno = $value;
    }

    /**
     * @param int $value
     */
    public function setIsCustomerInvoiceBlocked($value)
    {
        $this->is_customer_invoice_blocked = $value;
    }

    /**
     * @param int $value
     */
    public function setUpdatePayment($value)
    {
        $this->update_payment = $value;
    }

    /**
     * @param int $value
     */
    public function setUpdateShipping($value)
    {
        $this->update_shipping = $value;
    }

    /**
     * @param ShopgateDeliveryNote []|mixed[][] $value
     */
    public function setDeliveryNotes($value)
    {
        if (empty($value)) {
            $this->delivery_notes = null;

            return;
        }

        if (!is_array($value)) {
            $this->delivery_notes = null;

            return;
        }

        foreach ($value as $index => &$element) {
            if ((!is_object($element) || !($element instanceof ShopgateDeliveryNote)) && !is_array($element)) {
                unset($value[$index]);
                continue;
            }

            if (is_array($element)) {
                $element = new ShopgateDeliveryNote($element);
            }
        }

        $this->delivery_notes = $value;
    }




    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * @return string
     */
    public function getExternalOrderNumber()
    {
        return $this->external_order_number;
    }

    /**
     * @return string
     */
    public function getExternalOrderId()
    {
        return $this->external_order_id;
    }

    /**
     * @return string
     */
    public function getConfirmShippingUrl()
    {
        return $this->confirm_shipping_url;
    }

    /**
     * @see http://www.php.net/manual/de/function.date.php
     * @see http://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string $format
     *
     * @return string
     */
    public function getCreatedTime($format = "")
    {
        $time = $this->created_time;
        if (!empty($format)) {
            $timestamp = strtotime($time);
            $time      = date($format, $timestamp);
        }

        return $time;
    }

    /**
     * @return int
     */
    public function getIsPaid()
    {
        return (int)$this->is_paid;
    }

    /**
     * @see http://www.php.net/manual/de/function.date.php
     * @see http://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string $format
     *
     * @return string
     */
    public function getPaymentTime($format = "")
    {
        $time = $this->payment_time;
        if (!empty($format)) {
            $timestamp = strtotime($time);
            $time      = date($format, $timestamp);
        }

        return $time;
    }

    /**
     * @return string
     */
    public function getPaymentTransactionNumber()
    {
        return $this->payment_transaction_number;
    }

    /**
     * @return mixed[]
     */
    public function getPaymentInfos()
    {
        return $this->payment_infos;
    }

    /**
     * @return int
     */
    public function getIsShippingBlocked()
    {
        return (int)$this->is_shipping_blocked;
    }

    /**
     * @return int
     */
    public function getIsShippingCompleted()
    {
        return (int)$this->is_shipping_completed;
    }

    /**
     * @see http://www.php.net/manual/de/function.date.php
     * @see http://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string $format
     *
     * @return string
     */
    public function getShippingCompletedTime($format = '')
    {
        $time = $this->shipping_completed_time;
        if (!empty($format)) {
            $timestamp = strtotime($time);
            $time      = date($format, $timestamp);
        }

        return $time;
    }

    /**
     * @return float
     */
    public function getShippingTaxPercent()
    {
        return $this->shipping_tax_percent;
    }

    /**
     * @return int
     */
    public function getIsTest()
    {
        return (int)$this->is_test;
    }

    /**
     * @return int
     */
    public function getIsStorno()
    {
        return (int)$this->is_storno;
    }

    /**
     * @return int
     */
    public function getIsCustomerInvoiceBlocked()
    {
        return (int)$this->is_customer_invoice_blocked;
    }

    /**
     * @return int
     */
    public function getUpdatePayment()
    {
        return (int)$this->update_payment;
    }

    /**
     * @return int
     */
    public function getUpdateShipping()
    {
        return (int)$this->update_shipping;
    }

    /**
     * @return ShopgateDeliveryNote[]
     */
    public function getDeliveryNotes()
    {
        return $this->delivery_notes;
    }
}

class ShopgateOrderItem extends ShopgateContainer
{
    const TYPE_ITEM            = 'item';
    const TYPE_PRODUCT         = 'item';
    const TYPE_PAYMENT         = 'payment';
    const TYPE_SHOPGATE_COUPON = 'sg_coupon';

    /** @var string */
    protected $item_number;

    /** @var string */
    protected $item_number_public;

    /** @var string */
    protected $parent_item_number;

    /** @var int */
    protected $order_item_id;

    /** @var string */
    protected $type;

    /** @var int */
    protected $quantity;

    /** @var string */
    protected $name;

    /** @var float */
    protected $unit_amount;

    /** @var float */
    protected $unit_amount_with_tax;

    /** @var float */
    protected $tax_percent;

    /** @var string */
    protected $tax_class_key;

    /** @var string */
    protected $tax_class_id;

    /** @var string */
    protected $currency;

    /** @var string */
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
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @param string $value
     */
    public function setItemNumber($value)
    {
        $this->item_number = $value;
    }

    /**
     * @param string $value
     */
    public function setItemNumberPublic($value)
    {
        $this->item_number_public = $value;
    }

    public function setParentItemNumber($value)
    {
        $this->parent_item_number = $value;
    }

    /**
     * @param int $value
     */
    public function setOrderItemId($value)
    {
        $this->order_item_id = $value;
    }

    /**
     * @param string $value
     */
    public function setType($value)
    {
        $this->type = $value;
    }

    /**
     * @param float $value
     */
    public function setUnitAmount($value)
    {
        $this->unit_amount = $value;
    }

    /**
     * @param float $value
     */
    public function setUnitAmountWithTax($value)
    {
        $this->unit_amount_with_tax = $value;
    }

    /**
     * @param int $value
     */
    public function setQuantity($value)
    {
        $this->quantity = $value;
    }

    /**
     * @param float $value
     */
    public function setTaxPercent($value)
    {
        $this->tax_percent = $value;
    }

    /**
     * Sets the tax_class_key value
     *
     * @param string $value
     */
    public function setTaxClassKey($value)
    {
        $this->tax_class_key = $value;
    }

    /**
     * Sets the tax_class_id
     *
     * @param string $value
     */
    public function setTaxClassId($value)
    {
        $this->tax_class_id = $value;
    }

    /**
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    /**
     * @param string $value
     */
    public function setInternalOrderInfo($value)
    {
        $this->internal_order_info = $value;
    }

    /**
     * @param ShopgateOrderItemOption []|mixed[][] $value
     */
    public function setOptions($value)
    {
        if (empty($value) || !is_array($value)) {
            $this->options = array();

            return;
        }

        $options = array();
        foreach ($value as $index => $element) {
            if (!($element instanceof ShopgateOrderItemOption) && !is_array($element)) {
                continue;
            }

            if (is_array($element)) {
                $options[] = new ShopgateOrderItemOption($element);
            } else {
                $options[] = $element;
            }
        }

        $this->options = $options;
    }

    /**
     * @param ShopgateOrderItemInput []|mixed[][] $value
     */
    public function setInputs($value)
    {
        if (empty($value) || !is_array($value)) {
            $this->inputs = array();

            return;
        }

        $inputs = array();
        foreach ($value as $index => $element) {
            if (!($element instanceof ShopgateOrderItemInput) && !is_array($element)) {
                continue;
            }

            if (is_array(($element))) {
                $inputs[] = new ShopgateOrderItemInput($element);
            } else {
                $inputs[] = $element;
            }
        }

        $this->inputs = $inputs;
    }

    /**
     * @param ShopgateOrderItemAttribute []|mixed[][] $value
     */
    public function setAttributes($value)
    {
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getItemNumber()
    {
        return $this->item_number;
    }

    /**
     * @return string
     */
    public function getItemNumberPublic()
    {
        return $this->item_number_public;
    }

    public function getParentItemNumber()
    {
        return $this->parent_item_number;
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->order_item_id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getUnitAmount()
    {
        return $this->unit_amount;
    }

    /**
     * @return float
     */
    public function getUnitAmountWithTax()
    {
        return $this->unit_amount_with_tax;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->tax_percent;
    }

    /**
     * @return string
     */
    public function getTaxClassKey()
    {
        return $this->tax_class_key;
    }

    /**
     * Returns the tax_class_id
     *
     * @return string
     */
    public function getTaxClassId()
    {
        return $this->tax_class_id;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getInternalOrderInfo()
    {
        return $this->internal_order_info;
    }

    /**
     * @return ShopgateOrderItemOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return ShopgateOrderItemInput[]
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @return ShopgateOrderItemAttribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrderItem($this);
    }

    /**
     * @return bool
     */
    public function isItem()
    {
        return ($this->type == self::TYPE_ITEM);
    }

    /**
     * @return bool
     */
    public function isSgCoupon()
    {
        return ($this->type == self::TYPE_SHOPGATE_COUPON);
    }

    /**
     * @return bool
     */
    public function isPayment()
    {
        return ($this->type == self::TYPE_PAYMENT);
    }
}

class ShopgateOrderItemOption extends ShopgateContainer
{
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
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     */
    public function setAdditionalAmountWithTax($value)
    {
        $this->additional_amount_with_tax = $value;
    }

    /**
     * @param string $value
     */
    public function setValueNumber($value)
    {
        $this->value_number = $value;
    }

    /**
     * @param string $value
     */
    public function setOptionNumber($value)
    {
        $this->option_number = $value;
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getAdditionalAmountWithTax()
    {
        return $this->additional_amount_with_tax;
    }

    /**
     * @return string
     */
    public function getValueNumber()
    {
        return $this->value_number;
    }

    /**
     * @return string
     */
    public function getOptionNumber()
    {
        return $this->option_number;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrderItemOption($this);
    }
}

class ShopgateOrderItemInput extends ShopgateContainer
{
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
    public function setInputNumber($value)
    {
        $this->input_number = $value;
    }

    /**
     * @param string $value "text"|"image"
     */
    public function setType($value)
    {
        $this->type = $value;
    }

    /**
     * @param float $value
     */
    public function setAdditionalAmountWithTax($value)
    {
        $this->additional_amount_with_tax = $value;
    }

    /**
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
    }

    /**
     * @param string $value
     */
    public function setUserInput($value)
    {
        $this->user_input = $value;
    }

    /**
     * @param string $value
     */
    public function setInfoText($value)
    {
        $this->info_text = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getInputNumber()
    {
        return $this->input_number;
    }

    /**
     * @return string "text"|"image"
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getAdditionalAmountWithTax()
    {
        return $this->additional_amount_with_tax;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getUserInput()
    {
        return $this->user_input;
    }

    /**
     * @return string
     */
    public function getInfoText()
    {
        return $this->info_text;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrderItemInput($this);
    }
}

class ShopgateOrderItemAttribute extends ShopgateContainer
{
    protected $name;

    protected $value;

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrderItemAttribute($this);
    }
}

class ShopgateShippingInfo extends ShopgateContainer
{
    protected $name;

    protected $display_name;

    protected $description;

    protected $amount;

    protected $amount_net;

    protected $amount_gross;

    protected $weight;

    protected $api_response;

    protected $internal_shipping_info;

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitShippingInfo($this);
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param string $value
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     *
     * @param string $value
     */
    public function setDisplayName($value)
    {
        $this->display_name = $value;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param string $value
     */
    public function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     *
     * @return float
     *
     * @deprecated use getAmountNet or getAmountGross
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @param float $value
     *
     * @deprecated use setAmountNet or setAmountGross
     */
    public function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     *
     * @return float
     */
    public function getAmountNet()
    {
        return $this->amount_net;
    }

    /**
     *
     * @param float $value
     */
    public function setAmountNet($value)
    {
        $this->amount_net = $value;
    }

    /**
     *
     * @return float
     */
    public function getAmountGross()
    {
        return $this->amount_gross;
    }

    /**
     *
     * @param float $value
     */
    public function setAmountGross($value)
    {
        $this->amount_gross = $value;
    }

    /**
     *
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     *
     * @param int $value
     */
    public function setWeight($value)
    {
        $this->weight = $value;
    }

    /**
     *
     * @return mixed[]
     */
    public function getApiResponse()
    {
        return $this->api_response;
    }

    /**
     *
     * @param string|mixed[] $value
     */
    public function setApiResponse($value)
    {
        $this->api_response = $value;
    }

    /**
     * @return string
     */
    public function getInternalShippingInfo()
    {
        return $this->internal_shipping_info;
    }

    /**
     * @param string $value
     */
    public function setInternalShippingInfo($value)
    {
        $this->internal_shipping_info = $value;
    }
}

class ShopgateDeliveryNote extends ShopgateContainer
{
    // shipping groups
    const DHL        = "DHL";        // DHL
    const DHLEXPRESS = "DHLEXPRESS"; // DHLEXPRESS
    const DP         = "DP";         // Deutsche Post
    const DPD        = "DPD";        // Deutscher Paket Dienst
    const FEDEX      = "FEDEX";      // FedEx
    const GLS        = "GLS";        // GLS
    const HLG        = "HLG";        // Hermes
    const OTHER      = "OTHER";      // Anderer Lieferant
    const TNT        = "TNT";        // TNT
    const TOF        = "TOF";        // Trnas-o-Flex
    const UPS        = "UPS";        // UPS
    const USPS       = "USPS";       // USPS
    // shipping types
    const MANUAL      = "MANUAL";
    const USPS_API_V1 = "USPS_API_V1";
    const UPS_API_V1  = "UPS_API_V1";

    protected $shipping_service_id = null;

    protected $shipping_service_name = "";

    protected $tracking_number = "";

    protected $shipping_time = null;

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setShippingServiceId($value)
    {
        $this->shipping_service_id = $value;
    }

    /**
     * @param string $value
     */
    public function setShippingServiceName($value)
    {
        $this->shipping_service_name = $value;
    }

    /**
     * @param string $value
     */
    public function setTrackingNumber($value)
    {
        $this->tracking_number = $value;
    }

    /**
     * @param string $value
     */
    public function setShippingTime($value)
    {
        $this->shipping_time = $value;
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getShippingServiceId()
    {
        return $this->shipping_service_id;
    }

    /**
     * @return string
     */
    public function getShippingServiceName()
    {
        return $this->shipping_service_name;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->tracking_number;
    }

    /**
     * @return string
     */
    public function getShippingTime()
    {
        return $this->shipping_time;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrderDeliveryNote($this);
    }
}

abstract class ShopgateCoupon extends ShopgateContainer
{
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
    public function setOrderIndex($value)
    {
        $this->order_index = $value;
    }

    /**
     * @param string $value
     */
    public function setCode($value)
    {
        $this->code = $value;
    }

    /**
     * @param string $value
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     *
     * @param string $value
     */
    public function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     * @param float $value
     *
     * @deprecated
     */
    public function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountNet($value)
    {
        $this->amount_net = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountGross($value)
    {
        $this->amount_gross = $value;
    }

    /**
     * @param string $value
     */
    public function setTaxType($value)
    {
        $this->tax_type = $value;
    }

    /**
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    /**
     * @param int $value
     */
    public function setIsFreeShipping($value)
    {
        $this->is_free_shipping = $value;
    }

    /**
     * @param string $value
     */
    public function setInternalInfo($value)
    {
        $this->internal_info = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->order_index;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return float
     * @deprecated
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getAmountNet()
    {
        return $this->amount_net;
    }

    /**
     * @return float
     */
    public function getAmountGross()
    {
        return $this->amount_gross;
    }

    /**
     * @return string
     */
    public function getTaxType()
    {
        return $this->tax_type;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function getIsFreeShipping()
    {
        return $this->is_free_shipping;
    }

    /**
     * @return string
     */
    public function getInternalInfo()
    {
        return $this->internal_info;
    }
}

class ShopgateExternalCoupon extends ShopgateCoupon
{
    protected $is_valid;

    protected $not_valid_message;

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitExternalCoupon($this);
    }

    ##########
    # Setter #
    ##########

    /**
     * @param bool $value
     */
    public function setIsValid($value)
    {
        $this->is_valid = $value;
    }

    /**
     * @param string $value
     */
    public function setNotValidMessage($value)
    {
        $this->not_valid_message = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return bool
     */
    public function getIsValid()
    {
        return $this->is_valid;
    }

    /**
     * @return string
     */
    public function getNotValidMessage()
    {
        return $this->not_valid_message;
    }
}

class ShopgateShopgateCoupon extends ShopgateCoupon
{
    protected $error;

    protected $error_text;

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitShopgateCoupon($this);
    }

    ##########
    # Setter #
    ##########

    /**
     * @param int $value
     */
    public function setError($value)
    {
        $this->error = $value;
    }

    /**
     * @param string $value
     */
    public function setErrorText($value)
    {
        $this->error_text = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getErrorText()
    {
        return $this->error_text;
    }
}

class ShopgateOrderCustomField extends ShopgateContainer
{
    protected $label;

    protected $internal_field_name;

    protected $value;

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
    }

    /**
     * @param string $value
     */
    public function setInternalFieldName($value)
    {
        $this->internal_field_name = $value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getInternalFieldName()
    {
        return $this->internal_field_name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitOrderCustomField($this);
    }
}

class ShopgateShippingMethod extends ShopgateContainer
{
    protected $id;

    protected $title;

    protected $shipping_group;

    protected $description;

    protected $sort_order;

    protected $amount;

    protected $amount_with_tax;

    protected $tax_class;

    protected $tax_percent;

    protected $internal_shipping_info;

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * @param string $value
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * @param string $value
     */
    public function setShippingGroup($value)
    {
        $this->shipping_group = $value;
    }

    /**
     * @param string $value
     */
    public function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     * @param int $value
     */
    public function setSortOrder($value)
    {
        $this->sort_order = $value;
    }

    /**
     * @param float $value
     */
    public function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountWithTax($value)
    {
        $this->amount_with_tax = $value;
    }

    /**
     * @param string $value
     */
    public function setTaxClass($value)
    {
        $this->tax_class = $value;
    }

    /**
     * @param string $value
     */
    public function setTaxPercent($value)
    {
        $this->tax_percent = $value;
    }

    /**
     * @param string $value
     */
    public function setInternalShippingInfo($value)
    {
        $this->internal_shipping_info = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getShippingGroup()
    {
        return $this->shipping_group;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getAmountWithTax()
    {
        return $this->amount_with_tax;
    }

    /**
     * @return string
     */
    public function getTaxClass()
    {
        return $this->tax_class;
    }

    /**
     * @return string
     */
    public function getTaxPercent()
    {
        return $this->tax_percent;
    }

    /**
     * @return string
     */
    public function getInternalShippingInfo()
    {
        return $this->internal_shipping_info;
    }

    /**
     * @param ShopgateContainerVisitor $v
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitShippingMethod($this);
    }
}

/**
 * Class ShopgatePaymentMethod
 */
class ShopgatePaymentMethod extends ShopgateContainer
{
    protected $id;

    protected $amount;

    protected $amount_with_tax;

    protected $tax_class;

    protected $tax_percent;

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * @param float $value
     */
    public function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountWithTax($value)
    {
        $this->amount_with_tax = $value;
    }

    /**
     * @param string $value
     */
    public function setTaxClass($value)
    {
        $this->tax_class = $value;
    }

    /**
     * @param string $value
     */
    public function setTaxPercent($value)
    {
        $this->tax_percent = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getAmountWithTax()
    {
        return $this->amount_with_tax;
    }

    /**
     * @return string
     */
    public function getTaxClass()
    {
        return $this->tax_class;
    }

    /**
     * @return string
     */
    public function getTaxPercent()
    {
        return $this->tax_percent;
    }

    /**
     * @param ShopgateContainerVisitor $v
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitPaymentMethod($this);
    }
}

class ShopgateCartItem extends ShopgateContainer
{
    protected $item_number;

    protected $is_buyable;

    protected $qty_buyable;

    protected $stock_quantity;

    protected $unit_amount;

    protected $unit_amount_with_tax;

    protected $error;

    protected $error_text;

    protected $options = array();

    protected $inputs = array();

    protected $attributes = array();

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setItemNumber($value)
    {
        $this->item_number = $value;
    }

    /**
     * @param int $value
     */
    public function setIsBuyable($value)
    {
        $this->is_buyable = $value;
    }

    /**
     * @param int $value
     */
    public function setQtyBuyable($value)
    {
        $this->qty_buyable = $value;
    }

    /**
     * @param int $value
     */
    public function setStockQuantity($value)
    {
        $this->stock_quantity = $value;
    }

    /**
     * @param float $value
     */
    public function setUnitAmount($value)
    {
        $this->unit_amount = $value;
    }

    /**
     * @param float $value
     */
    public function setUnitAmountWithTax($value)
    {
        $this->unit_amount_with_tax = $value;
    }

    /**
     * @param int $value
     */
    public function setError($value)
    {
        $this->error = $value;
    }

    /**
     * @param string $value
     */
    public function setErrorText($value)
    {
        $this->error_text = $value;
    }

    /**
     * @param ShopgateOrderItemOption []|mixed[][] $value
     */
    public function setOptions($value)
    {
        if (empty($value) || !is_array($value)) {
            $this->options = array();

            return;
        }

        $options = array();
        foreach ($value as $index => $element) {
            if (!($element instanceof ShopgateOrderItemOption) && !is_array($element)) {
                continue;
            }

            if (is_array($element)) {
                $options[] = new ShopgateOrderItemOption($element);
            } else {
                $options[] = $element;
            }
        }

        $this->options = $options;
    }

    /**
     * @param ShopgateOrderItemInput []|mixed[][] $value
     */
    public function setInputs($value)
    {
        if (empty($value) || !is_array($value)) {
            $this->inputs = array();

            return;
        }

        $inputs = array();
        foreach ($value as $index => $element) {
            if (!($element instanceof ShopgateOrderItemInput) && !is_array($element)) {
                continue;
            }

            if (is_array($element)) {
                $inputs[] = new ShopgateOrderItemInput($element);
            } else {
                $inputs[] = $element;
            }
        }

        $this->inputs = $inputs;
    }

    /**
     * @param ShopgateOrderItemAttribute []|mixed[][] $value
     */
    public function setAttributes($value)
    {
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
    public function getItemNumber()
    {
        return $this->item_number;
    }

    /**
     * @return int
     */
    public function getIsBuyable()
    {
        return (int)$this->is_buyable;
    }

    /**
     * @return int
     */
    public function getQtyBuyable()
    {
        return $this->qty_buyable;
    }

    /**
     * @return int
     */
    public function getStockQuantity()
    {
        return $this->stock_quantity;
    }

    /**
     * @return float
     */
    public function getUnitAmount()
    {
        return $this->unit_amount;
    }

    /**
     * @return float
     */
    public function getUnitAmountWithTax()
    {
        return $this->unit_amount_with_tax;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getErrorText()
    {
        return $this->error_text;
    }

    /**
     * @return ShopgateOrderItemOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return ShopgateOrderItemInput[]
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @return ShopgateOrderItemAttribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param ShopgateContainerVisitor $v
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitCartItem($this);
    }
}

class ShopgateCartCustomer extends ShopgateContainer
{
    protected $customer_tax_class_key;

    protected $customer_groups = array();

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setCustomerTaxClassKey($value)
    {
        $this->customer_tax_class_key = $value;
    }

    /**
     * @param ShopgateCartCustomerGroup[] $value
     */
    public function setCustomerGroups($value)
    {
        $this->customer_groups = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return string $value
     */
    public function getCustomerTaxClassKey()
    {
        return $this->customer_tax_class_key;
    }

    /**
     * @return ShopgateCartCustomerGroup[]
     */
    public function getCustomerGroups()
    {
        return $this->customer_groups;
    }

    /**
     * @param ShopgateContainerVisitor $v
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitCartCustomer($this);
    }
}

class ShopgateCartCustomerGroup extends ShopgateContainer
{
    protected $id;

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ShopgateContainerVisitor $v
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitPlainObject($this);
    }
}
