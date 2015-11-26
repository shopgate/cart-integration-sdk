<?php

interface Shopgate_Helper_Redirect_KeywordsManagerInterface
{
	/**
	 * @var int (hours) the default time to be set for updating the cache
	 */
	const DEFAULT_CACHE_TIME = 24;
	
	/**
	 * Returns a regular expression matching everything on the whitelist and not matching anything on the black list.
	 *
	 * @return string
	 */
	public function toRegEx();
	
	/**
	 * @return string[] A list of keywords that identify a smartphone user.
	 */
	public function getWhitelist();
	
	/**
	 * @return string[] A list keywords that identify a smartphone user but should be ignored in the redirect.
	 */
	public function getBlacklist();
	
	/**
	 * Updates the keyword cache from the merchant API regardless of expiry etc.
	 *
	 * @throws ShopgateLibraryException in case the request to the Shopgate Merchant API fails.
	 */
	public function update();
}