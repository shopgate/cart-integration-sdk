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
 * Time: 21:01
 *
 * File: Category.php
 *
 *  @method         setNumber(int $value)
 *  @method int     getNumber()
 *
 *  @method         setSortOrder(int $value)
 *  @method int     getSortOrder()
 *
 *  @method         setPath(string $value)
 *  @method string  getPath()
 *
 */
class Shopgate_Model_Catalog_Category
    extends Shopgate_Model_Abstract
{

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        $categoryNode = $itemNode->addChild('category');
        $categoryNode->addAttribute('number', $this->getNumber());
        $categoryNode->addAttribute('sort_order', $this->getSortOrder());
        $categoryNode->addChildWithCDATA('path', $this->getPath());

        return $itemNode;
    }
} 