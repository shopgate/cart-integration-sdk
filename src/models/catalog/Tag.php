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

/**
 * @class Shopgate_Model_Catalog_Tag
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method               setUid(string $value)
 * @method string|null   getUid()
 *
 * @method               setValue(string $value)
 * @method string|null   getValue()
 *
 */
class Shopgate_Model_Catalog_Tag extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'Value',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $tagNode
         */
        $tagNode = $itemNode->addChildWithCDATA('tag', $this->getValue());
        $tagNode->addAttribute('uid', $this->getUid());

        return $itemNode;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        $tagsResult = new Shopgate_Model_Abstract();

        $tagsResult->setData('uid', $this->getUid());
        $tagsResult->setData('tag', $this->getValue());

        return $tagsResult->getData();
    }
}
