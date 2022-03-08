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
 * @class Shopgate_Model_Catalog_Product
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method                                               setUid(string $value)
 * @method string|null                                   getUid()
 *
 * @method                                               setLastUpdate(string $value)
 * @method string|null                                   getLastUpdate()
 *
 * @method                                               setName(string $value)
 * @method string|null                                   getName()
 *
 * @method                                               setTaxPercent(float $value)
 * @method float|null                                    getTaxPercent()
 *
 * @method                                               setTaxClass(string $value)
 * @method string|null                                   getTaxClass()
 *
 * @method                                               setCurrency(string $value)
 * @method string|null                                   getCurrency()
 *
 * @method                                               setDescription(string $value)
 * @method string|null                                   getDescription()
 *
 * @method                                               setDeeplink(string $value)
 * @method string|null                                   getDeeplink()
 *
 * @method                                               setPromotionSortOrder(int $value)
 * @method int|null                                      getPromotionSortOrder()
 *
 * @method                                               setInternalOrderInfo(string $value)
 * @method string|null                                   getInternalOrderInfo()
 *
 * @method                                               setAgeRating(int $value)
 * @method int|null                                      getAgeRating()
 *
 * @method                                               setPrice(Shopgate_Model_Catalog_Price $value)
 * @method Shopgate_Model_Catalog_Price|null             getPrice()
 *
 * @method                                               setWeight(float $value)
 * @method float|null                                    getWeight()
 *
 * @method                                               setWeightUnit(string $value)
 * @method string|null                                   getWeightUnit()
 *
 * @method                                               setImages(array $value)
 * @method Shopgate_Model_Media_Image[]|null             getImages()
 *
 * @method                                               setCategoryPaths(array $value)
 * @method Shopgate_Model_Catalog_CategoryPath[]|null    getCategoryPaths()
 *
 * @method                                               setShipping(Shopgate_Model_Catalog_Shipping $value)
 * @method Shopgate_Model_Catalog_Shipping|null          getShipping()
 *
 * @method                                               setManufacturer(Shopgate_Model_Catalog_Manufacturer $value)
 * @method Shopgate_Model_Catalog_Manufacturer|null      getManufacturer()
 *
 * @method                                               setVisibility(Shopgate_Model_Catalog_Visibility $value)
 * @method Shopgate_Model_Catalog_Visibility|null        getVisibility()
 *
 * @method                                               setProperties(array $value)
 * @method Shopgate_Model_Catalog_Property[]|null        getProperties()
 *
 * @method                                               setStock(Shopgate_Model_Catalog_Stock $value)
 * @method Shopgate_Model_Catalog_Stock|null             getStock()
 *
 * @method                                               setIdentifiers(array $value)
 * @method Shopgate_Model_Catalog_Identifier[]|null      getIdentifiers()
 *
 * @method                                               setTags(array $value)
 * @method Shopgate_Model_Catalog_Tag[]|null             getTags()
 *
 * @method                                               setRelations(array $value)
 * @method Shopgate_Model_Catalog_Relation[]|null        getRelations()
 *
 * @method                                               setAttributeGroups(array $value)
 * @method Shopgate_Model_Catalog_AttributeGroup[]|null  getAttributeGroups()
 *
 * @method                                               setAttributes(array $value)
 * @method Shopgate_Model_Catalog_Attribute[]|null       getAttributes()
 *
 * @method                                               setInputs(array $value)
 * @method Shopgate_Model_Catalog_Input[]|null           getInputs()
 *
 * @method                                               setAttachments(array $value)
 * @method Shopgate_Model_Media_Attachment[]|null        getAttachments()
 *
 * @method                                               setIsDefaultChild(bool $value)
 * @method bool|null                                     getIsDefaultChild()
 *
 * @method                                               setChildren(Shopgate_Model_Catalog_Product[] $value)
 *
 * @method                                               setDisplayType(string $value)
 * @method string|null                                   getDisplayType()
 *
 */
class Shopgate_Model_Catalog_Product extends Shopgate_Model_AbstractExport
{
    /**
     * define identifier uid
     */
    const DEFAULT_IDENTIFIER_UID = 'uid';
    /**
     * define default item identifier
     */
    const DEFAULT_ITEM_IDENTIFIER = 'item';
    /**
     * weight units
     */
    const DEFAULT_WEIGHT_UNIT_KG      = 'kg';
    const DEFAULT_WEIGHT_UNIT_OUNCE   = 'oz';
    const DEFAULT_WEIGHT_UNIT_GRAM    = 'g';
    const DEFAULT_WEIGHT_UNIT_POUND   = 'lb';
    const DEFAULT_WEIGHT_UNIT_DEFAULT = self::DEFAULT_WEIGHT_UNIT_GRAM;
    /**
     * @deprecated use Shopgate_Model_Catalog_Product::DEFAULT_WEIGHT_UNIT_GRAM
     */
    const DEFAULT_WEIGHT_UNIT_GRAMM = self::DEFAULT_WEIGHT_UNIT_GRAM;
    /**
     * tax
     */
    const DEFAULT_NO_TAXABLE_CLASS_NAME = 'no tax class';
    /**
     * display_type
     */
    const DISPLAY_TYPE_DEFAULT = 'default';
    const DISPLAY_TYPE_SIMPLE  = 'simple';
    const DISPLAY_TYPE_SELECT  = 'select';
    const DISPLAY_TYPE_LIST    = 'list';

    /**
     * @var string
     */
    protected $itemNodeIdentifier = '<items></items>';

    /**
     * @var string
     */
    protected $identifier = 'items';

    /**
     * define xsd file location
     *
     * @var string
     */
    protected $xsdFileLocation = 'catalog/products.xsd';

    /**
     * @var bool
     */
    protected $isChild = false;

    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'LastUpdate',
        'Name',
        'TaxPercent',
        'TaxClass',
        'Currency',
        'Description',
        'Deeplink',
        'PromotionSortOrder',
        'InternalOrderInfo',
        'Price',
        'Weight',
        'WeightUnit',
        'Images',
        'CategoryPaths',
        'Shipping',
        'Manufacturer',
        'Visibility',
        'Properties',
        'Stock',
        'Identifiers',
        'Tags',
        'Relations',
        'AttributeGroups',
        'Attributes',
        'Inputs',
        'Attachments',
        'IsDefaultChild',
        'Children',
        'AgeRating',
        'DisplayType',
    );

    /**
     * @var array
     */
    protected $fireMethods = array(
        'setLastUpdate',
        'setUid',
        'setName',
        'setTaxPercent',
        'setTaxClass',
        'setCurrency',
        'setDescription',
        'setDeeplink',
        'setPromotionSortOrder',
        'setInternalOrderInfo',
        'setAgeRating',
        'setWeight',
        'setWeightUnit',
        'setPrice',
        'setShipping',
        'setManufacturer',
        'setVisibility',
        'setStock',
        'setImages',
        'setCategoryPaths',
        'setProperties',
        'setIdentifiers',
        'setTags',
        'setRelations',
        'setAttributeGroups',
        'setInputs',
        'setAttachments',
        'setChildren',
        'setDisplayType',
    );

    /**
     * init default object
     */
    public function __construct()
    {
        $this->setData(
            array(
                'price'            => new Shopgate_Model_Catalog_Price(),
                'shipping'         => new Shopgate_Model_Catalog_Shipping(),
                'manufacturer'     => new Shopgate_Model_Catalog_Manufacturer(),
                'visibility'       => new Shopgate_Model_Catalog_Visibility(),
                'stock'            => new Shopgate_Model_Catalog_Stock(),
                'inputs'           => array(),
                'children'         => array(),
                'attribute_groups' => array(),
                'relations'        => array(),
                'tags'             => array(),
                'identifiers'      => array(),
                'properties'       => array(),
                'category_paths'   => array(),
                'images'           => array(),
                'attachments'      => array(),
                'attributes'       => array(),
            )
        );
    }

    /**
     * get is child
     *
     * @return bool
     */
    public function getIsChild()
    {
        return $this->isChild;
    }

    /**
     * set is child
     *
     * @param bool $value
     */
    public function setIsChild($value)
    {
        $this->isChild = $value;
    }

    /**
     * generate xml result object
     *
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /** @var Shopgate_Model_XmlResultObject $itemNode */
        $itemNode = $itemNode->addChild(self::DEFAULT_ITEM_IDENTIFIER);

        $itemNode->addAttribute('uid', $this->getUid());
        $itemNode->addAttribute('last_update', $this->getLastUpdate());
        $itemNode->addChildWithCDATA('name', $this->getName());
        $itemNode->addChild('tax_percent', $this->getTaxPercent(), null, false);
        $itemNode->addChildWithCDATA('tax_class', $this->getTaxClass(), false);
        $itemNode->addChild('currency', $this->getCurrency());
        $itemNode->addChildWithCDATA('description', $this->getDescription());
        $itemNode->addChildWithCDATA('deeplink', $this->getDeeplink());
        $itemNode->addChild('promotion')
            ->addAttribute(
                'sort_order',
                $this->getPromotionSortOrder() !== null ? (string)$this->getPromotionSortOrder() : null
            );
        $itemNode->addChildWithCDATA('internal_order_info', $this->getInternalOrderInfo());
        $itemNode->addChild('age_rating', $this->getAgeRating(), null, false);
        $itemNode->addChild('weight', $this->getWeight())->addAttribute('unit', $this->getWeightUnit());

        /**
         * is default child
         */
        if ($this->getIsChild() && $this->getIsDefaultChild()) {
            $itemNode->addAttribute('default_child', '1');
        }

        /**
         * prices / tier prices
         */
        $this->getPrice()->asXml($itemNode);

        /** @var Shopgate_Model_XmlResultObject $imagesNode */
        $imagesNode = $itemNode->addChild('images');
        foreach ($this->getImages() as $imageItem) {
            $imageItem->asXml($imagesNode);
        }

        /** @var Shopgate_Model_XmlResultObject $categoryPathNode */
        $categoryPathNode = $itemNode->addChild('categories');
        foreach ($this->getCategoryPaths() as $categoryPathItem) {
            $categoryPathItem->asXml($categoryPathNode);
        }

        /**
         * shipping
         */
        $this->getShipping()->asXml($itemNode);

        /**
         * manufacture
         */
        $this->getManufacturer()->asXml($itemNode);

        /**
         * visibility
         */
        $this->getVisibility()->asXml($itemNode);

        /** @var Shopgate_Model_XmlResultObject $propertiesNode */
        $propertiesNode = $itemNode->addChild('properties');
        foreach ($this->getProperties() as $propertyItem) {
            $propertyItem->asXml($propertiesNode);
        }

        /**
         * stock
         */
        $this->getStock()->asXml($itemNode);

        /** @var Shopgate_Model_XmlResultObject $identifiersNode */
        $identifiersNode = $itemNode->addChild('identifiers');
        foreach ($this->getIdentifiers() as $identifierItem) {
            $identifierItem->asXml($identifiersNode);
        }

        /** @var Shopgate_Model_XmlResultObject $tagsNode */
        $tagsNode = $itemNode->addChild('tags');
        foreach ($this->getTags() as $tagItem) {
            $tagItem->asXml($tagsNode);
        }

        /** @var Shopgate_Model_XmlResultObject $relationsNode */
        $relationsNode = $itemNode->addChild('relations');
        foreach ($this->getRelations() as $relationItem) {
            $relationItem->asXml($relationsNode);
        }

        if ($this->getIsChild()) {
            /** @var Shopgate_Model_XmlResultObject $attributesNode */
            $attributesNode = $itemNode->addChild('attributes');
            foreach ($this->getAttributes() as $attributeItem) {
                $attributeItem->asXml($attributesNode);
            }
        } else {
            /** @var Shopgate_Model_XmlResultObject $attributeGroupsNode */
            $attributeGroupsNode = $itemNode->addChild('attribute_groups');
            foreach ($this->getAttributeGroups() as $attributeGroupItem) {
                $attributeGroupItem->asXml($attributeGroupsNode);
            }
        }

        /** @var Shopgate_Model_XmlResultObject $inputsNode */
        $inputsNode = $itemNode->addChild('inputs');
        foreach ($this->getInputs() as $inputItem) {
            $inputItem->asXml($inputsNode);
        }

        $itemNode->addChild('display_type', $this->getDisplayType());

        /**
         * children
         */
        if (!$this->getIsChild()) {
            /** @var Shopgate_Model_XmlResultObject $childrenNode */
            $childrenNode = $itemNode->addChild('children');
            foreach ($this->getChildren() as $child) {
                $child->asXml($childrenNode);
            }
            /**
             * remove empty nodes
             */
            if (count($this->getChildren()) > 0) {
                foreach ($itemNode->children as $childXml) {
                    $itemNode->replaceChild($this->removeEmptyNodes($childXml), $itemNode->children);
                }
            }
        }

        return $itemNode;
    }

    /**
     * add image
     *
     * @param Shopgate_Model_Media_Image $image
     */
    public function addImage(Shopgate_Model_Media_Image $image)
    {
        $images = $this->getImages();
        $images[] = $image;
        $this->setImages($images);
    }

    /**
     * add child
     *
     * @param Shopgate_Model_Catalog_Product $child
     */
    public function addChild($child)
    {
        $children = $this->getChildren();
        $children[] = $child;
        $this->setChildren($children);
    }

    /**
     * @return Shopgate_Model_Catalog_Product[]
     */
    public function getChildren()
    {
        $children = (array)parent::getData('children');

        // cleaning of child products happens implicitly by reference on the objects passed ($this, $child)
        foreach ($children as $child) {
            $this->cleanChildData($this, $child);
        }

        return $children;
    }

    /**
     * add category
     *
     * @param Shopgate_Model_Catalog_CategoryPath $categoryPath
     */
    public function addCategoryPath(Shopgate_Model_Catalog_CategoryPath $categoryPath)
    {
        $categoryPaths = $this->getCategoryPaths();
        $categoryPaths[] = $categoryPath;
        $this->setCategoryPaths($categoryPaths);
    }

    /**
     * add attribute group
     *
     * @param Shopgate_Model_Catalog_AttributeGroup $attributeGroup
     */
    public function addAttributeGroup($attributeGroup)
    {
        $attributesGroups = $this->getAttributeGroups();
        $attributesGroups[] = $attributeGroup;
        $this->setAttributeGroups($attributesGroups);
    }

    /**
     * add property
     *
     * @param Shopgate_Model_Catalog_Property $property
     */
    public function addProperty($property)
    {
        $properties = $this->getProperties();
        $properties[] = $property;
        $this->setProperties($properties);
    }

    /**
     * add identifier
     *
     * @param Shopgate_Model_Catalog_Identifier $identifier
     */
    public function addIdentifier($identifier)
    {
        $identifiers = $this->getIdentifiers();
        $identifiers[] = $identifier;
        $this->setIdentifiers($identifiers);
    }

    /**
     * add tag
     *
     * @param Shopgate_Model_Catalog_Tag $tag
     */
    public function addTag($tag)
    {
        $tags = $this->getTags();
        $tags[] = $tag;
        $this->setTags($tags);
    }

    /**
     * add relation
     *
     * @param Shopgate_Model_Catalog_Relation $relation
     */
    public function addRelation($relation)
    {
        $relations = $this->getRelations();
        $relations[] = $relation;
        $this->setRelations($relations);
    }

    /**
     * add input
     *
     * @param Shopgate_Model_Catalog_Input $input
     */
    public function addInput($input)
    {
        $inputs = $this->getInputs();
        $inputs[] = $input;
        $this->setInputs($inputs);
    }

    /**
     * add attribute option
     *
     * @param Shopgate_Model_Catalog_Attribute $attribute
     */
    public function addAttribute($attribute)
    {
        $attributes = $this->getAttributes();
        $attributes[] = $attribute;
        $this->setAttributes($attributes);
    }

    /**
     * @param Shopgate_Model_XmlResultObject $childItem
     *
     * @return SimpleXMLElement
     */
    public function removeEmptyNodes($childItem)
    {
        $doc                     = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($childItem->asXML());

        $xpath   = new DOMXPath($doc);
        $xpQuery = '//*[not(@forceEmpty) and not(descendant::*[@forceEmpty]) and normalize-space() = ""]';

        /** @var DOMElement $node */
        foreach ($xpath->query($xpQuery) as $node) {
            $node->parentNode->removeChild($node);
        }

        foreach ($xpath->query('//*[@forceEmpty]') as $node) {
            $node->removeAttribute('forceEmpty');
        }

        return simplexml_import_dom($doc);
    }

    /**
     * generate json result object
     *
     * @return array
     */
    public function asArray()
    {
        $productResult = new Shopgate_Model_Abstract();

        $productResult->setData('uid', $this->getUid());
        $productResult->setData('last_update', $this->getLastUpdate());
        $productResult->setData('name', $this->getName());
        $productResult->setData('tax_percent', $this->getTaxPercent());
        $productResult->setData('tax_class', $this->getTaxClass());
        $productResult->setData('currency', $this->getCurrency());
        $productResult->setData('description', $this->getDescription());
        $productResult->setData('deeplink', $this->getDeeplink());
        $productResult->setData('promotion_sort_order', $this->getPromotionSortOrder());
        $productResult->setData('internal_order_info', $this->getInternalOrderInfo());
        $productResult->setData('age_rating', $this->getAgeRating());
        $productResult->setData('weight', $this->getWeight());
        $productResult->setData('weight_unit', $this->getWeightUnit());
        $productResult->setData('display_type', $this->getDisplayType());

        $imagesData = array();
        foreach ($this->getImages() as $image) {
            $imagesData[] = $image->asArray();
        }
        $productResult->setData('images', $imagesData);

        $categoryPathsData = array();
        foreach ($this->getCategoryPaths() as $categoryPath) {
            $categoryPathsData[] = $categoryPath->asArray();
        }
        $productResult->setData('categories', $categoryPathsData);

        $productResult->setData('shipping', $this->getShipping()->asArray());
        $productResult->setData('manufacturer', $this->getManufacturer()->asArray());
        $productResult->setData('visibility', $this->getVisibility()->asArray());

        $propertiesData = array();
        foreach ($this->getProperties() as $property) {
            $propertiesData[] = $property->asArray();
        }
        $productResult->setData('properties', $propertiesData);

        $productResult->setData('stock', $this->getStock()->asArray());

        $identifiersData = array();
        foreach ($this->getIdentifiers() as $identifier) {
            $identifiersData[] = $identifier->asArray();
        }
        $productResult->setData('identifiers', $identifiersData);

        $tagsData = array();
        foreach ($this->getTags() as $tag) {
            $tagsData[] = $tag->asArray();
        }
        $productResult->setData('tags', $tagsData);

        return $productResult->getData();
    }

    /**
     * generate csv result object
     */
    public function asCsv()
    {
    }

    /**
     * @param Shopgate_Model_Abstract $parentItem
     * @param Shopgate_Model_Abstract $childItem
     */
    protected function cleanChildData($parentItem, $childItem)
    {
        foreach ($childItem->getData() as $childKey => $childValue) {
            if (is_array($childValue) || $childValue instanceof Shopgate_Model_Abstract) {
                /**
                 * array or object
                 */
                if (is_array($childValue) && count($childValue) > 0) {
                    /**
                     * array
                     */
                    if ($childValue == $parentItem->getData($childKey)) {
                        $childItem->setData($childKey, array());
                    }
                } elseif ($childValue instanceof Shopgate_Model_Abstract) {
                    /**
                     * object - but we check only data array
                     */
                    $parentAttribute = $parentItem->getData($childKey);

                    if ($parentAttribute instanceof Shopgate_Model_Abstract
                        && $childValue->getData() == $parentAttribute->getData()
                    ) {
                        $childItem->setData($childKey, new Shopgate_Model_Catalog_XmlEmptyObject());
                    }
                }
            } else {
                /**
                 * string
                 */
                if ($childValue == $parentItem->getData($childKey)) {
                    $childItem->setData($childKey, null);
                }
            }
        }
    }

    /**
     * @param array $data
     * @param int   $uid
     *
     * @return mixed
     */
    protected function getItemByUid($data, $uid)
    {
        /* @var Shopgate_Model_Abstract $item */
        foreach ($data as $item) {
            if ($item->getData(self::DEFAULT_IDENTIFIER_UID) == $uid) {
                return $item;
            }
        }

        return false;
    }
}
