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
class ShopgateCategory extends ShopgateContainer
{
    protected $category_number;

    protected $name;

    protected $parent_category_number;

    protected $url_image;

    protected $order_index;

    protected $is_active;

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitCategory($this);
    }


    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setCategoryNumber($value)
    {
        $this->category_number = $value;
    }

    /**
     * @param string $value
     */
    public function setParentCategoryNumber($value)
    {
        $this->parent_category_number = $value;
    }

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
    public function setUrlImage($value)
    {
        $this->url_image = $value;
    }

    /**
     * @param int $value Use this like "priority". Highest value gets displayed closest to the top.
     */
    public function setOrderIndex($value)
    {
        $this->order_index = $value;
    }

    /**
     * @param int $value
     */
    public function setIsActive($value)
    {
        $this->is_active = $value;
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getCategoryNumber()
    {
        return $this->category_number;
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
    public function getParentCategoryNumber()
    {
        return $this->parent_category_number;
    }

    /**
     * @return string
     */
    public function getUrlImage()
    {
        return $this->url_image;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->order_index;
    }

    /**
     * @return int
     */
    public function getIsActive()
    {
        return (int)$this->is_active;
    }
}

class ShopgateItem extends ShopgateContainer
{
    protected $item_number;

    protected $name;

    protected $currency;

    protected $tax_percent;

    protected $unit_amount_with_tax;

    protected $tax_class_key;

    protected $tax_class_id;

    protected $old_unit_amount_with_tax;

    protected $category_numbers;

    protected $item_number_public;

    protected $parent_item_number;

    protected $manufacturer;

    protected $manufacturer_number;

    protected $description;

    protected $shipping_costs_per_order;

    protected $shipping_costs_per_unit;

    protected $is_free_shipping;

    protected $msrp;

    protected $tags;

    protected $age_rating;

    protected $weight;

    protected $ean;

    protected $isbn;

    protected $pzn;

    protected $amount_info_text;

    protected $internal_order_info;

    protected $use_stock;

    protected $stock_quantity;

    protected $is_highlight;

    protected $highlight_order_index;

    protected $is_available;

    protected $available_text;

    protected $has_image;

    protected $image_count;

    protected $is_not_orderable;

    protected $is_marketplace;

    protected $is_active;

    protected $is_auto_update;

    protected $attribute_1;

    protected $attribute_2;

    protected $attribute_3;

    protected $attribute_4;

    protected $attribute_5;

    protected $attribute_6;

    protected $attribute_7;

    protected $attribute_8;

    protected $attribute_9;

    protected $attribute_10;

    protected $properties;

    protected $deeplink_onlineshop;

    protected $related_item_numbers;

    protected $options;

    protected $inputs;

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
     * @param string $value
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    /**
     * @param float $value
     */
    public function setTaxPercent($value)
    {
        $this->tax_percent = $value;
    }

    /**
     * @deprecated
     *
     * @param float $value
     */
    public function setUnitAmountWithTax($value)
    {
        $this->unit_amount_with_tax = $value;
    }

    /**
     *
     * @param string $value
     */
    public function setTaxClassKey($value)
    {
        $this->tax_class_key = $value;
    }

    /**
     *
     * @param string $value
     */
    public function setTaxClassId($value)
    {
        $this->tax_class_id = $value;
    }

    /**
     * @param float $value
     */
    public function setOldUnitAmountWithTax($value)
    {
        $this->old_unit_amount_with_tax = $value;
    }

    /**
     * @param string[] $value
     */
    public function setCategoryNumbers($value)
    {
        $this->category_numbers = $value;
    }

    /**
     * @param string $value
     */
    public function setItemNumberPublic($value)
    {
        $this->item_number_public = $value;
    }

    /**
     * @param string $value
     */
    public function setParentItemNumber($value)
    {
        $this->parent_item_number = $value;
    }

    /**
     * @param string $value
     */
    public function setManufacturer($value)
    {
        $this->manufacturer = $value;
    }

    /**
     * @param string $value
     */
    public function setManufacturerNumber($value)
    {
        $this->manufacturer_number = $value;
    }

    /**
     * @param string $value
     */
    public function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     * @param float $value
     */
    public function setShippingCostsPerOrder($value)
    {
        $this->shipping_costs_per_order = $value;
    }

    /**
     * @param float $value
     */
    public function setShippingCostsPerUnit($value)
    {
        $this->shipping_costs_per_unit = $value;
    }

    /**
     * @param int $value
     */
    public function setIsFreeShipping($value)
    {
        $this->is_free_shipping = $value;
    }

    /**
     * @param float $value
     */
    public function setMsrp($value)
    {
        $this->msrp = $value;
    }

    /**
     * @param string $value
     */
    public function setTags($value)
    {
        $this->tags = $value;
    }

    /**
     * @param int $value
     */
    public function setAgeRating($value)
    {
        $this->age_rating = $value;
    }

    /**
     * @param int $value
     */
    public function setWeight($value)
    {
        $this->weight = $value;
    }

    /**
     * @param string $value
     */
    public function setEan($value)
    {
        $this->ean = $value;
    }

    /**
     * @param string $value
     */
    public function setIsbn($value)
    {
        $this->isbn = $value;
    }

    /**
     * @param string $value
     */
    public function setPzn($value)
    {
        $this->pzn = $value;
    }

    /**
     * @param string $value
     */
    public function setAmountInfoText($value)
    {
        $this->amount_info_text = $value;
    }

    /**
     * @param string $value
     */
    public function setInternalOrderInfo($value)
    {
        $this->internal_order_info = $value;
    }

    /**
     * @param int $value
     */
    public function setUseStock($value)
    {
        $this->use_stock = $value;
    }

    /**
     * @param int $value
     */
    public function setStockQuantity($value)
    {
        $this->stock_quantity = $value;
    }

    /**
     * @param int $value
     */
    public function setIsHighlight($value)
    {
        $this->is_highlight = $value;
    }

    /**
     * @param int $value
     */
    public function setHighlightOrderIndex($value)
    {
        $this->highlight_order_index = $value;
    }

    /**
     * @param int $value
     */
    public function setIsAvailable($value)
    {
        $this->is_available = $value;
    }

    /**
     * @param string $value
     */
    public function setAvailableText($value)
    {
        $this->available_text = $value;
    }

    /**
     * @param int $value
     */
    public function setHasImage($value)
    {
        $this->has_image = $value;
    }

    /**
     * @param int $value
     */
    public function setImageCount($value)
    {
        $this->image_count = $value;
    }

    /**
     * @param int $value
     */
    public function setIsNotOrderable($value)
    {
        $this->is_not_orderable = $value;
    }

    /**
     * @param int $value
     */
    public function setIsMarketplace($value)
    {
        $this->is_marketplace = $value;
    }

    /**
     * @param int $value
     */
    public function setIsActive($value)
    {
        $this->is_active = $value;
    }

    /**
     * @param int $value
     */
    public function setIsAutoUpdate($value)
    {
        $this->is_auto_update = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute1($value)
    {
        $this->attribute_1 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute2($value)
    {
        $this->attribute_2 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute3($value)
    {
        $this->attribute_3 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute4($value)
    {
        $this->attribute_4 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute5($value)
    {
        $this->attribute_5 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute6($value)
    {
        $this->attribute_6 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute7($value)
    {
        $this->attribute_7 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute8($value)
    {
        $this->attribute_8 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute9($value)
    {
        $this->attribute_9 = $value;
    }

    /**
     * @param string $value
     */
    public function setAttribute10($value)
    {
        $this->attribute_10 = $value;
    }

    /**
     * @param array <string, string> $value Array with key-value-pairs.
     */
    public function setProperties($value)
    {
        $this->properties = $value;
    }

    /**
     * @param string $value
     */
    public function setDeeplinkOnlineshop($value)
    {
        $this->deeplink_onlineshop = $value;
    }

    /**
     * @param string[] $value
     */
    public function setRelatedItemNumbers($value)
    {
        $this->related_item_numbers = $value;
    }

    /**
     * @param ShopgateItemOption[]|array<string, mixed>[] $value
     */
    public function setOptions($value)
    {
        $this->options = $this->convertArrayToSubentityList($value, 'ShopgateItemOption');
    }

    /**
     * @param ShopgateItemInput[]|array<string, mixed>[] $value
     */
    public function setInputs($value)
    {
        $this->inputs = $this->convertArrayToSubentityList($value, 'ShopgateItemInput');
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @deprecated
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
     * @return string
     */
    public function getTaxClassId()
    {
        return $this->tax_class_id;
    }

    /**
     * @return float
     */
    public function getUnitAmountWithTax()
    {
        return $this->unit_amount_with_tax;
    }

    /**
     * @return float
     */
    public function getOldUnitAmountWithTax()
    {
        return $this->old_unit_amount_with_tax;
    }

    /**
     * @return string[]
     */
    public function getCategoryNumbers()
    {
        return (!empty($this->category_numbers))
            ? $this->category_numbers
            : array();
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
    public function getParentItemNumber()
    {
        return $this->parent_item_number;
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @return string
     */
    public function getManufacturerNumber()
    {
        return $this->manufacturer_number;
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
    public function getShippingCostsPerOrder()
    {
        return $this->shipping_costs_per_order;
    }

    /**
     * @return float
     */
    public function getShippingCostsPerUnit()
    {
        return $this->shipping_costs_per_unit;
    }

    /**
     * @return int
     */
    public function getIsFreeShipping()
    {
        return $this->is_free_shipping;
    }

    /**
     * @return float
     */
    public function getMsrp()
    {
        return $this->msrp;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return int
     */
    public function getAgeRating()
    {
        return $this->age_rating;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @return string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * @return string
     */
    public function getPzn()
    {
        return $this->pzn;
    }

    /**
     * @return string
     */
    public function getAmountInfoText()
    {
        return $this->amount_info_text;
    }

    /**
     * @return string
     */
    public function getInternalOrderInfo()
    {
        return $this->internal_order_info;
    }

    /**
     * @return int
     */
    public function getUseStock()
    {
        return (int)$this->use_stock;
    }

    /**
     * @return int
     */
    public function getStockQuantity()
    {
        return $this->stock_quantity;
    }

    /**
     * @return int
     */
    public function getIsHighlight()
    {
        return (int)$this->is_highlight;
    }

    /**
     * @return int
     */
    public function getHighlightOrderIndex()
    {
        return $this->highlight_order_index;
    }

    /**
     * @return int
     */
    public function getIsAvailable()
    {
        return (int)$this->is_available;
    }

    /**
     * @return string
     */
    public function getAvailableText()
    {
        return $this->available_text;
    }

    /**
     * @return int
     */
    public function getHasImage()
    {
        return (int)$this->has_image;
    }

    /**
     * @return int
     */
    public function getImageCount()
    {
        return $this->image_count;
    }

    /**
     * @return int
     */
    public function getIsNotOrderable()
    {
        return (int)$this->is_not_orderable;
    }

    /**
     * @return int
     */
    public function getIsMarketplace()
    {
        return (int)$this->is_marketplace;
    }

    /**
     * @return int
     */
    public function getIsActive()
    {
        return (int)$this->is_active;
    }

    /**
     * @return int
     */
    public function getIsAutoUpdate()
    {
        return (int)$this->is_auto_update;
    }

    /**
     * @return string
     */
    public function getAttribute1()
    {
        return $this->attribute_1;
    }

    /**
     * @return string
     */
    public function getAttribute2()
    {
        return $this->attribute_2;
    }

    /**
     * @return string
     */
    public function getAttribute3()
    {
        return $this->attribute_3;
    }

    /**
     * @return string
     */
    public function getAttribute4()
    {
        return $this->attribute_4;
    }

    /**
     * @return string
     */
    public function getAttribute5()
    {
        return $this->attribute_5;
    }

    /**
     * @return string
     */
    public function getAttribute6()
    {
        return $this->attribute_6;
    }

    /**
     * @return string
     */
    public function getAttribute7()
    {
        return $this->attribute_7;
    }

    /**
     * @return string
     */
    public function getAttribute8()
    {
        return $this->attribute_8;
    }

    /**
     * @return string
     */
    public function getAttribute9()
    {
        return $this->attribute_9;
    }

    /**
     * @return string
     */
    public function getAttribute10()
    {
        return $this->attribute_10;
    }

    /**
     * @return string[]
     */
    public function getProperties()
    {
        return (!empty($this->properties))
            ? $this->properties
            : array();
    }

    /**
     * @return string
     */
    public function getDeeplinkOnlineshop()
    {
        return $this->deeplink_onlineshop;
    }

    /**
     * @return string[]
     */
    public function getRelatedItemNumbers()
    {
        return (!empty($this->related_item_numbers))
            ? $this->related_item_numbers
            : array();
    }

    /**
     * @return ShopgateItemOption[]
     */
    public function getOptions()
    {
        return (!empty($this->options))
            ? $this->options
            : array();
    }

    /**
     * @return ShopgateItemInput[]
     */
    public function getInputs()
    {
        return (!empty($this->inputs))
            ? $this->inputs
            : array();
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitItem($this);
    }
}

class ShopgateItemOption extends ShopgateContainer
{
    protected $option_number;

    protected $name;

    protected $order_index;

    protected $option_values = array();

    ##########
    # Setter #
    ##########

    /**
     * @param string $value
     */
    public function setOptionNumber($value)
    {
        $this->option_number = $value;
    }

    /**
     * @param string $value
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @param int $value
     */
    public function setOrderIndex($value)
    {
        $this->order_index = $value;
    }

    /**
     * @param ShopgateItemOptionValue[]|array<string, mixed>[] $value
     */
    public function setOptionValues($value)
    {
        $this->option_values = $this->convertArrayToSubentityList($value, 'ShopgateItemOptionValue');
    }


    ##########
    # Getter #
    ##########

    /**
     * @return string
     */
    public function getOptionNumber()
    {
        return $this->option_number;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->order_index;
    }

    /**
     * @return ShopgateItemOptionValue[]
     */
    public function getOptionValues()
    {
        return (!empty($this->option_values))
            ? $this->option_values
            : array();
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitItemOption($this);
    }
}

class ShopgateItemOptionValue extends ShopgateContainer
{
    protected $value_number;

    protected $value;

    protected $order_index;

    protected $additional_amount_with_tax;

    ##########
    # Setter #
    ##########

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
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param int $value
     */
    public function setOrderIndex($value)
    {
        $this->order_index = $value;
    }

    /**
     * @param float $value
     */
    public function setAdditionalAmountWithTax($value)
    {
        $this->additional_amount_with_tax = $value;
    }


    ##########
    # Getter #
    ##########

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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->order_index;
    }

    /**
     * @return float
     */
    public function getAdditionalAmountWithTax()
    {
        return $this->additional_amount_with_tax;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitItemOptionValue($this);
    }
}

class ShopgateItemInput extends ShopgateContainer
{
    const INPUT_TYPE_TEXT  = "text";
    const INPUT_TYPE_IMAGE = "image";

    protected $input_number;

    protected $type;

    protected $additional_amount_with_tax;

    protected $label;

    protected $info_text;

    protected $is_required;

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
     * @param string $value Must be "text" or "image".
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
    public function setInfoText($value)
    {
        $this->info_text = $value;
    }

    /**
     * @param int $value
     */
    public function setIsRequired($value)
    {
        $this->is_required = $value;
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
     * @return string
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
    public function getInfoText()
    {
        return $this->info_text;
    }

    /**
     * @return int
     */
    public function getIsRequired()
    {
        return (int)$this->is_required;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitItemInput($this);
    }
}

class ShopgateSyncItem extends ShopgateContainer
{
    const STATUS_NEW      = 'new';
    const STATUS_DELETED  = 'deleted';
    const STATUS_EXISTING = 'existing';

    /**
     * @var string
     */
    protected $item_number;

    /**
     * @var string either one of new, deleted, existing
     */
    protected $status = null;

    /**
     * @param string $value
     */
    public function setItemNumber($value)
    {
        $this->item_number = $value;
    }

    /**
     * @return string
     */
    public function getItemNumber()
    {
        return $this->item_number;
    }

    /**
     * @param string $value
     */
    public function setStatus($value)
    {
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @see ShopgateContainer::accept()
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitSyncItem($this);
    }
}
