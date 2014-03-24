<?php

/**
 * Shopgate GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file AFL_license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to interfaces@shopgate.com so we can send you a copy immediately.
 *
 * @author     Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright  Shopgate GmbH
 * @license    http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 14.03.14
 * Time: 19:31
 *
 * File: GroupItemObject.php
 *
 * @method          setCustomerGroupId(int $value)
 * @method int      getCustomerGroupId()
 *
 * @method          setTrierPrices(array $value);
 * @method array    getTrierPrices();
 *
 */

class Shopgate_Model_Customer_Group
    extends Shopgate_Model_Abstract
{

    /**
     * init default objects
     */
    public function __construct()
    {
        $this->setTrierPrices(array());
    }

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject   $customerGroupNode
         * @var Shopgate_Model_Catalog_TierPrice $tierPriceItem
         */
        $customerGroupNode = $itemNode->addChild('customer_group');
        $customerGroupNode->addAttribute('number', $this->getCustomerGroupId());

        foreach ($this->getTrierPrices() as $tierPriceItem) {
            $tierPriceItem->asXml($customerGroupNode);
        }

        return $itemNode;
    }


    /**
     * add tier price
     *
     * @param Shopgate_Model_Catalog_TierPrice $tierPrice
     */
    public function addTierPrice(Shopgate_Model_Catalog_TierPrice $tierPrice)
    {
        $tierPrices = $this->getTrierPrices();
        array_push($tierPrices, $tierPrice);
        $this->setTrierPrices($tierPrices);
    }
} 