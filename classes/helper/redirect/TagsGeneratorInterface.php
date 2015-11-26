<?php

interface Shopgate_Helper_Redirect_TagsGeneratorInterface
{
	const PAGE_TYPE_DEFAULT  = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_DEFAULT;
	const PAGE_TYPE_HOME     = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_HOME;
	const PAGE_TYPE_PRODUCT  = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_PRODUCT;
	const PAGE_TYPE_CATEGORY = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CATEGORY;
	const PAGE_TYPE_CMS      = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CMS;
	const PAGE_TYPE_BRAND    = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_BRAND;
	const PAGE_TYPE_SEARCH   = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_SEARCH;
	
	/**
	 * @param Shopgate_Model_Redirect_HtmlTag[] $htmlTags
	 */
	public function setHtmlTags(array $htmlTags);
	
	/**
	 * @param string $htmlTags A JSON encoded string containing the HTML tags.
	 */
	public function setHtmlTagsFromJson($htmlTags);
	
	/**
	 * @param string $pageType
	 * @param array  $parameters [string, string]
	 *
	 * @return string
	 * @throws ShopgateLibraryException
	 */
	public function getTagsFor($pageType, array $parameters = array());
}