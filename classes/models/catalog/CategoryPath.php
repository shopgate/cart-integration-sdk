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
 * File: CategoryPath.php
 *
 *  @method                             setUid(int $value)
 *  @method int                         getUid()
 *
 *  @method                             setSortOrder(int $value)
 *  @method int                         getSortOrder()
 *
 *  @method                             setItems(array $value)
 *  @method array                       getItems()
 *
 *  @method                             setParentUid(int $value)
 *  @method int                         getParentUid()
 *
 *  @method                             setImage(Shopgate_Model_Media_Image $value)
 *  @method Shopgate_Model_Media_Image  getImage()
 *
 *  @method                             setIsActive(bool $value)
 *  @method bool                        getIsActive()
 *
 *  @method                             setDeeplink(string $value)
 *  @method string                      getDeeplink()
 *
 */

class Shopgate_Model_Catalog_CategoryPath
    extends Shopgate_Model_Abstract
{

    /**
     * init default object
     */
    public function __construct()
    {
        $this->setItems(array());
    }

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $categoryPathNode
         * @var Shopgate_Model_XmlResultObject $itemsNode
         * @var Shopgate_Model_Abstract $item
         */
        $categoryPathNode = $itemNode->addChild('category');
        $categoryPathNode->addAttribute('uid', $this->getUid());
        $categoryPathNode->addAttribute('sort_order', $this->getSortOrder());
        $itemsNode = $categoryPathNode->addChild('items');
        foreach ($this->getItems() as $item) {
            $itemsNode->addChildWithCDATA('item', $item->getPath())->addAttribute('level', $item->getLevel());
        }
        return $itemNode;
    }

    /**
     * add category path
     *
     * @param int $level
     * @param string $path
     */
    public function addItem($level, $path)
    {
        $items = $this->getItems();
        $item = new Shopgate_Model_Abstract();
        $item->setLevel($level);
        $item->setPath($path);
        array_push($items, $item);
        $this->setItems($items);
    }
} 