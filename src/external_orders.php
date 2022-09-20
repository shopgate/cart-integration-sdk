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
class ShopgateExternalOrder extends ShopgateContainer
{
    protected $order_number;

    protected $external_order_number;

    protected $external_order_id;

    protected $status_name;

    protected $status_color;

    protected $created_time;

    protected $mail;

    protected $phone;

    protected $mobile;

    protected $custom_fields;

    protected $invoice_address;

    protected $delivery_address;

    protected $currency;

    protected $amount_items_gross;

    protected $amount_items_net;

    protected $amount_complete_gross;

    protected $amount_complete_net;

    protected $amount_complete;

    protected $is_paid;

    protected $payment_method;

    protected $payment_time;

    protected $payment_transaction_number;

    protected $is_shipping_completed;

    protected $shipping_completed_time;

    protected $delivery_notes;

    protected $order_taxes;

    protected $extra_costs;

    protected $external_coupons;

    protected $items;

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
    public function setStatusName($value)
    {
        $this->status_name = $value;
    }

    /**
     * @param string $value
     */
    public function setStatusColor($value)
    {
        $this->status_color = $value;
    }

    /**
     * @param string $value
     */
    public function setCreatedTime($value)
    {
        $this->created_time = $value;
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
     * @param ShopgateOrderCustomField[]|array<string, mixed>[] $value
     */
    public function setCustomFields($value)
    {
        $this->custom_fields = $this->convertArrayToSubEntityList($value, 'ShopgateOrderCustomField');
    }

    /**
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    /**
     * @param ShopgateAddress|array<string, mixed> $value
     */
    public function setInvoiceAddress($value)
    {
        $value = $this->convertArrayToSubentity($value, 'ShopgateAddress');

        if ($value !== null) {
            $value->setIsDeliveryAddress(false);
            $value->setIsInvoiceAddress(true);
        }

        $this->invoice_address = $value;
    }

    /**
     * @param ShopgateAddress|array<string, mixed> $value
     */
    public function setDeliveryAddress($value)
    {
        $value = $this->convertArrayToSubentity($value, 'ShopgateAddress');

        if ($value !== null) {
            $value->setIsDeliveryAddress(true);
            $value->setIsInvoiceAddress(false);
        }

        $this->delivery_address = $value;
    }

    /**
     * Before v2.9.90 $value was expected to be a ShopgateExternalCoupon. This is still working but deprecated, as the
     * object differs vastly from what the documentation for the get_orders response requires. It is strongly
     * recommended to switch to ShopgateExternalOrderExternalCoupon objects instead.
     *
     * @param ShopgateExternalOrderExternalCoupon[]|array<string, mixed>[] $value
     */
    public function setExternalCoupons($value)
    {
        $this->external_coupons = $this->convertArrayToSubEntityList($value, array('ShopgateExternalOrderExternalCoupon', 'ShopgateExternalCoupon'));
    }

    /**
     * @deprecated since version 2.9.26; use setAmountCompleteGross
     *
     * @param float $value
     */
    public function setAmountComplete($value)
    {
        $this->amount_complete = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountItemsGross($value)
    {
        $this->amount_items_gross = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountItemsNet($value)
    {
        $this->amount_items_net = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountCompleteGross($value)
    {
        $this->amount_complete_gross = $value;
    }

    /**
     * @param float $value
     */
    public function setAmountCompleteNet($value)
    {
        $this->amount_complete_net = $value;
    }

    /**
     * @param int $value
     */
    public function setIsShippingCompleted($value)
    {
        $this->is_shipping_completed = $value;
    }

    /**
     * @param string $value
     */
    public function setShippingCompletedTime($value)
    {
        $this->shipping_completed_time = $value;
    }

    /**
     * @param int $value
     */
    public function setIsPaid($value)
    {
        $this->is_paid = $value;
    }

    /**
     * @param string $value
     */
    public function setPaymentMethod($value)
    {
        $this->payment_method = $value;
    }

    /**
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
     * @param ShopgateDeliveryNote[]|array<string, mixed>[] $value
     */
    public function setDeliveryNotes($value)
    {
        $this->delivery_notes = $this->convertArrayToSubEntityList($value, 'ShopgateDeliveryNote');
    }

    /**
     * @param ShopgateExternalOrderTax[]|array<string, mixed>[] $value
     */
    public function setOrderTaxes($value)
    {
        $this->order_taxes = $this->convertArrayToSubEntityList($value, 'ShopgateExternalOrderTax');
    }

    /**
     * @param ShopgateExternalOrderExtraCost[]|array<string, mixed>[] $value
     */
    public function setExtraCosts($value)
    {
        $this->extra_costs = $this->convertArrayToSubEntityList($value, 'ShopgateExternalOrderExtraCost');
    }

    /**
     * @param ShopgateExternalOrderItem[]|array<string, mixed>[] $value
     */
    public function setItems($value)
    {
        $this->items = $this->convertArrayToSubEntityList($value, 'ShopgateExternalOrderItem');
    }

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
    public function getStatusName()
    {
        return $this->status_name;
    }

    /**
     * @return string
     */
    public function getStatusColor()
    {
        return $this->status_color;
    }

    /**
     * @return string
     */
    public function getCreatedTime()
    {
        return $this->created_time;
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
     * @return ShopgateExternalOrderExternalCoupon[]
     */
    public function getExternalCoupons()
    {
        return $this->external_coupons;
    }

    /**
     * @deprecated since version 2.9.26; use getAmountCompleteGross
     *
     * @return float
     */
    public function getAmountComplete()
    {
        return $this->amount_complete;
    }

    /**
     * @return float
     */
    public function getAmountItemsGross()
    {
        return $this->amount_items_gross;
    }

    /**
     * @return float
     */
    public function getAmountItemsNet()
    {
        return $this->amount_items_net;
    }

    /**
     * @return float
     */
    public function getAmountCompleteGross()
    {
        return $this->amount_complete_gross;
    }

    /**
     * @return float
     */
    public function getAmountCompleteNet()
    {
        return $this->amount_complete_net;
    }

    /**
     * @return int
     */
    public function getIsShippingCompleted()
    {
        return (int)$this->is_shipping_completed;
    }

    /**
     * @return string
     */
    public function getShippingCompletedTime()
    {
        return $this->shipping_completed_time;
    }

    /**
     * @return int
     */
    public function getIsPaid()
    {
        return (int)$this->is_paid;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @return string
     */
    public function getPaymentTime()
    {
        return $this->payment_time;
    }

    /**
     * @return string
     */
    public function getPaymentTransactionNumber()
    {
        return $this->payment_transaction_number;
    }

    /**
     * @return ShopgateDeliveryNote[]
     */
    public function getDeliveryNotes()
    {
        return $this->delivery_notes;
    }

    /**
     * @return ShopgateExternalOrderTax[]
     */
    public function getOrderTaxes()
    {
        return $this->order_taxes;
    }

    /**
     * @return ShopgateExternalOrderExtraCost[]
     */
    public function getExtraCosts()
    {
        return $this->extra_costs;
    }

    /**
     * @return ShopgateExternalOrderItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $options
     *
     * @return ShopgateExternalOrderItem
     */
    protected function getOrderItem(array $options)
    {
        return new ShopgateExternalOrderItem($options);
    }

    /**
     * @param ShopgateContainerVisitor $v
     *
     * @see ShopgateContainer::accept()
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitExternalOrder($this);
    }
}

class ShopgateExternalOrderItem extends ShopgateContainer
{
    protected $item_number;

    protected $item_number_public;

    protected $quantity;

    protected $name;

    protected $unit_amount;

    protected $unit_amount_with_tax;

    protected $tax_percent;

    protected $currency;

    protected $description;

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

    public function setDescription($value)
    {
        $this->description = $value;
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
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
    }

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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
    public function getCurrency()
    {
        return $this->currency;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitExternalOrderItem($this);
    }
}

class ShopgateExternalOrderExtraCost extends ShopgateContainer
{
    const TYPE_SHIPPING = 'shipping';
    const TYPE_PAYMENT  = 'payment';
    const TYPE_MISC     = 'misc';

    protected $type;

    protected $tax_percent;

    protected $amount;

    protected $label;

    /**
     * @param string $value
     */
    public function setType($value)
    {
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
    public function setTaxPercent($value)
    {
        $this->tax_percent = $value;
    }

    /**
     * @param float $value
     */
    public function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
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
    public function getTaxPercent()
    {
        return $this->tax_percent;
    }

    /**
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param ShopgateContainerVisitor $v
     *
     * @see ShopgateContainer::accept()
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitExternalOrderExtraCost($this);
    }
}

class ShopgateExternalOrderTax extends ShopgateContainer
{
    protected $label;

    protected $tax_percent;

    protected $amount;

    /**
     *
     * @param null|string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
    }

    /**
     *
     * @param float $value
     */
    public function setTaxPercent($value)
    {
        $this->tax_percent = $value;
    }

    /**
     *
     * @param float $value
     */
    public function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     *
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->tax_percent;
    }

    /**
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param ShopgateContainerVisitor $v
     *
     * @see ShopgateContainer::accept()
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitExternalOrderTax($this);
    }
}

class ShopgateExternalOrderExternalCoupon extends ShopgateContainer
{
    protected $code;
    protected $order_index;
    protected $name;
    protected $description;
    protected $amount;
    protected $currency;
    protected $is_free_shipping;
    protected $internal_info;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->order_index;
    }

    /**
     * @param int $order_index
     */
    public function setOrderIndex($order_index)
    {
        $this->order_index = $order_index;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return bool
     */
    public function getIsFreeShipping()
    {
        return $this->is_free_shipping;
    }

    /**
     * @param bool $is_free_shipping
     */
    public function setIsFreeShipping($is_free_shipping)
    {
        $this->is_free_shipping = $is_free_shipping;
    }

    /**
     * @return string
     */
    public function getInternalInfo()
    {
        return $this->internal_info;
    }

    /**
     * @param string $internal_info
     */
    public function setInternalInfo($internal_info)
    {
        $this->internal_info = $internal_info;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitExternalOrderExternalCoupon($this);
    }
}
