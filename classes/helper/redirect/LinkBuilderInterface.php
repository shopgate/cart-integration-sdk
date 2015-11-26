<?php

interface Shopgate_Helper_Redirect_LinkBuilderInterface
{
	const LINK_TYPE_DEFAULT  = 'default';
	const LINK_TYPE_HOME     = 'home';
	const LINK_TYPE_PRODUCT  = 'product';
	const LINK_TYPE_CATEGORY = 'category';
	const LINK_TYPE_CMS      = 'cms';
	const LINK_TYPE_BRAND    = 'brand';
	const LINK_TYPE_SEARCH   = 'search';
	
	const FUNCTION_NAME_HEX        = Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_HEX;
	const FUNCTION_NAME_URLENCODED = Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_URLENCODED;
	
	/**
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildDefault(array $parameters = array());
	
	/**
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildHome(array $parameters = array());
	
	/**
	 * @param string                                   $uid
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildProduct($uid, array $parameters = array());
	
	/**
	 * @param string                                   $uid
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildCategory($uid, array $parameters = array());
	
	/**
	 * @param string                                   $pageName
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildCms($pageName, array $parameters = array());
	
	/**
	 * @param string $brandName
	 *
	 * @return string
	 */
	public function buildBrand($brandName);
	
	/**
	 * @param string                                   $searchQuery
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildSearch($searchQuery, array $parameters = array());
	
	/**
	 * @param string                                    $pageType
	 * @param Shopgate_Model_Redirect_HtmlTagVariable[] $variables
	 * @param array                                     $parameters       [string, string]
	 * @param string                                    $overrideTemplate Set to null to not override the default template
	 *
	 * @return string
	 * @throws ShopgateLibraryException
	 */
	public function getUrlFor($pageType, array $variables, array $parameters = array(), $overrideTemplate = null);
}