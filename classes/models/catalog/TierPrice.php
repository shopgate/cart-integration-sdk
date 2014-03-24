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
 * Time: 17:20
 *
 * File: TrierPriceItemObject.php
 *
 * @method          setFromQuantity(int $value)
 * @method int      getFromQuantity()
 *
 * @method          setReductionType(string $value)
 * @method string   getReductionType()
 *
 * @method          setReduction(float $value)
 * @method float    getReduction()
 *
 */

class Shopgate_Model_Catalog_TierPrice
    extends Shopgate_Model_Abstract
{
    const DEFAULT_TRIER_PRICE_TYPE_PERCENT    = 'percent';
    const DEFAULT_TRIER_PRICE_TYPE_FIXED      = 'fixed';
    const DEFAULT_TRIER_PRICE_TYPE_DIFFERENCE = 'difference';

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $tierPriceNode
         */
        $tierPriceNode = $itemNode->addChild('tier_price', $this->getReduction());
        $tierPriceNode->addAttribute('treshold', $this->getFromQuantity());
        $tierPriceNode->addAttribute('type', $this->getReductionType());

        return $itemNode;
    }
}