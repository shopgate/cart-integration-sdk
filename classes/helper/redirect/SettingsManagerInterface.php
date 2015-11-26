<?php

interface Shopgate_Helper_Redirect_SettingsManagerInterface
{
	const DISABLE_REDIRECT_COOKIE_EXPIRATION_1_WEEK = 604800;
	
	const DEFAULT_DISABLE_REDIRECT_GET_PARAMETER_NAME = 'shopgate_redirect';
	const DEFAULT_DISABLE_REDIRECT_COOKIE_NAME        = 'SHOPGATE_MOBILE_WEBPAGE';
	
	const SHOPGATE_STATIC     = 'http://static.shopgate.com';
	const SHOPGATE_STATIC_SSL = 'https://static-ssl.shopgate.com';
	
	const SHOPGATE_PG_STATIC     = 'http://static.shopgatepg.com';
	const SHOPGATE_PG_STATIC_SSL = 'https://static-ssl.shopgatepg.com';
	
	const SHOPGATE_SL_STATIC     = 'http://static.shopgatesl.com';
	const SHOPGATE_SL_STATIC_SSL = 'https://static-ssl.shopgatesl.com';
	
	const SHOPGATE_LIVE_ALIAS = '.shopgate.com';
	const SHOPGATE_SL_ALIAS   = '.shopgatesl.com';
	const SHOPGATE_PG_ALIAS   = '.shopgatepg.com';
	
	/**
	 * @return bool
	 */
	public function isDefaultRedirectDisabled();
	
	/**
	 * @return bool
	 */
	public function isRedirectDisabled();
	
	/**
	 * @return bool
	 */
	public function isMobileHeaderDisabled();
	
	/**
	 * Sends the cookie to disable the mobile redirect is sent to the requesting entity.
	 *
	 * @param int $startTime Pass null to use time().
	 */
	public function setCookie($startTime = null);
	
	/**
	 * Generates the root mobile Url for the redirect without trailing slashes.
	 */
	public function getMobileUrl();
	
	/**
	 * Filters the GET parameters that should be passed in the redirect and builds the new query string.
	 *
	 * @return string
	 */
	public function getRedirectableGetParameters();
	
	/**
	 * @return string
	 * @throws ShopgateLibraryException in case the configuration value is null.
	 */
	public function getHtmlTags();
	
	/**
	 * @return array [string, string]
	 */
	public function getDefaultTemplatesByPageType();
	
	/**
	 * @return array [string, string] An array with indices 'ssl_url' and 'non_ssl_url' and the corresponding URLs.
	 */
	public function getShopgateStaticUrl();
}