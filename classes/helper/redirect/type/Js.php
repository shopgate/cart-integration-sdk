<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class Shopgate_Helper_Redirect_Type_Js implements Shopgate_Helper_Redirect_Type_TypeInterface
{
    /** @var Shopgate_Helper_Redirect_JsScriptBuilder */
    private $jsBuilder;

    /**
     * @param Shopgate_Helper_Redirect_JsScriptBuilder $jsBuilder
     */
    public function __construct(Shopgate_Helper_Redirect_JsScriptBuilder $jsBuilder)
    {
        $this->jsBuilder = $jsBuilder;
    }

    /**
     * @inheritdoc
     */
    public function runBrandScript($manufacturer)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND,
            ['brand_name' => $manufacturer]
        );
    }

    /**
     * @inheritdoc
     */
    public function runCmsScript($cmsPage)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS,
            ['page_uid' => $cmsPage]
        );
    }

    /**
     * @inheritdoc
     */
    public function runCategoryScript($categoryId)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY,
            ['category_uid' => $categoryId]
        );
    }

    /**
     * @inheritdoc
     */
    public function runDefaultScript()
    {
        return $this->jsBuilder->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function runHomeScript()
    {
        return $this->jsBuilder->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME);
    }

    /**
     * @inheritdoc
     */
    public function runProductScript($productId)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT,
            ['product_uid' => $productId]
        );
    }

    /**
     * @inheritdoc
     */
    public function runSearchScript($query)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH,
            ['search_query' => $query]
        );
    }
}
