<?php

class ShopgateMobileRedirect extends ShopgateObject implements ShopgateMobileRedirectInterface {
	
	/**
	 * @var ShopgateMerchantApiInterface
	 */
	protected $merchantApi;
	
	/**
	 * @var ShopgateConfig
	 */
	protected $config;

	/**
	 * @var string[] list of strings that cause redirection if they occur in the client's user agent
	 */
	protected $redirectKeywords = array();

	/**
	 * @var string[] list of strings that deny redirection if they occur in the client's user agent; overrides $this->redirectKeywords
	 */
	protected $skipRedirectKeywords = array('Shopgate');


	/**
	 * @var int (hours)
	 */
	protected $redirectKeywordCacheTime;

	/**
	 * @var bool true in case the website is delivered via HTTPS (this will load the Shopgate javascript via HTTPS as well to avoid browser warnings)
	 */
	protected $useSecureConnection;

	/**
	 * @var string path to the shopgate javascript template
	 */
	protected $jsHeaderTemplatePath;

	/**
	 * @var string expiration date of the cookie as defined in http://www.ietf.org/rfc/rfc2109.txt
	 */
	protected $cookieLife;
	
	/**
	 * @var string redirectCode used for creating a mobile product url
	 */
	protected $redirectCode;
	
	/**
	 * @var string itemNumber used for creating a mobile product url
	 */
	protected $itemNumber;
	
	/**
	 * @var string itemNumberPublic used for creating a mobile product url with a item number public
	 */
	protected $itemNumberPublic;
	
	/**
	 * @var string categoryNumber used for creating a mobile category url / mobile head js
	 */
	protected $categoryNumber;
	
	/**
	 * @var string cmsPage used for creating a mobile cms url / mobile head js
	 */
	protected $cmsPage;
	
	/**
	 * @var string manufactererName used for creating a mobile brand url  / mobile head js
	 */
	protected $manufactererName;
	
	/**
	 * @var string searchQuery used for creating a mobile search url  / mobile head js
	 */
	protected $searchQuery;

	
	
	/**
	 * Instantiates the Shopgate mobile redirector.
	 *
	 * @param string $shopgateConfig An instance of the ShopgateConfig
	 * @param ShopgateMerchantApiInterface $merchantApi An instance of the ShopgateMerchantApi required for keyword updates or null.
	 */
	public function __construct(ShopgateConfig $shopgateConfig, ShopgateMerchantApiInterface $merchantApi = null) {
		$this->merchantApi = $merchantApi;
		$this->config = $shopgateConfig;
		
		$this->redirectKeywordCacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME;
		
		$this->useSecureConnection = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] === "on" || $_SERVER["HTTPS"] == "1") || $this->config->getAlwaysUseSsl();
		
		// mobile header options
		$this->jsHeaderTemplatePath = dirname(__FILE__).'/../assets/js_header.html';
		$this->cookieLife = gmdate('D, d-M-Y H:i:s T', time());
	}

	####################
	# general settings #
	####################

	public function isMobileRequest() {
		// try loading keywords
		$this->updateRedirectKeywords();
		
		// find user agent
		$userAgent = '';
		if(!empty($_SERVER['HTTP_USER_AGENT'])){
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			return false;
		}
		
		// check user agent for redirection keywords and skip redirection keywords and return the result
		return
			(!empty($this->redirectKeywords)     ?  preg_match('/'.implode('|', $this->redirectKeywords).'/', $userAgent)     : false) &&
			(!empty($this->skipRedirectKeywords) ? !preg_match('/'.implode('|', $this->skipRedirectKeywords).'/', $userAgent) : true);
	}

	public function isRedirectAllowed() {
		// if GET parameter is set create cookie and do not redirect
		if (!empty($_GET['shopgate_redirect'])) {
			setcookie(ShopgateMobileRedirectInterface::COOKIE_NAME, 1, time() + 604800, '/'); // expires after 7 days
			return false;
		}
		
		return empty($_COOKIE[ShopgateMobileRedirectInterface::COOKIE_NAME]) ? true : false;
	}

	public function redirect($url, $autoRedirect = true) {
		
		if(!$this->isRedirectAllowed() || !$this->isMobileRequest() || !$autoRedirect){
			return $this->getJsHeader();
		}
		
		// validate url
		if (!preg_match('#^(http|https)\://#', $url)) {
			return $this->getJsHeader();
		}
		
		// perform redirect
		header("Location: ". $url, true, 301);
		exit;
	}
	
	/**
	 * @deprecated
	 */
	public function getMobileHeader() {
		if(!$this->isMobileRequest() || !$this->isRedirectAllowed()){
			return '';
		}
		
		// @deprecated returns for compatibility reasons ''
		return '';
	}

	protected function getJsHeader() {
		if (!file_exists($this->jsHeaderTemplatePath)) {
			return '';
		}
		
		$html = @file_get_contents($this->jsHeaderTemplatePath);
		if (empty($html)) {
			return '';
		}
		
		$mobileRedirectUrl = '';
		$additionalParameters = '';
		$redirectCode = '';
		switch($this->redirectType){
			case 'item':
				if(!isset($this->itemNumber) || $this->itemNumber == ''){
					$this->redirectType = 'start';
					break;
				}
				$redirectCode = 'item';
				$additionalParameters .= '_shopgate.item_number = "'.$this->itemNumber.'";';
				$mobileRedirectUrl = $this->getItemUrl($this->itemNumber);
				break;
			case 'itempublic':
				if(!isset($this->itemNumberPublic) || $this->itemNumberPublic == ''){
					$this->redirectType = 'start';
					break;
				}
				$redirectCode = 'item';
				$additionalParameters .= '_shopgate.item_number_public = "'.$this->itemNumberPublic.'";';
				$mobileRedirectUrl = $this->getItemPublicUrl($this->itemNumberPublic);
				break;
			case 'category':
				if(!isset($this->categoryNumber) || $this->categoryNumber == ''){
					$this->redirectType = 'start';
					break;
				}
				$redirectCode = 'category';
				$additionalParameters .= '_shopgate.category_number = "'.$this->categoryNumber.'";';
				$mobileRedirectUrl = $this->getCategoryUrl($this->categoryNumber);
				break;
			case 'cms':
				if(!isset($this->cmsPage) || $this->cmsPage == ''){
					$this->redirectType = 'start';
					break;
				}
				$redirectCode = 'cms';
				$additionalParameters .= '_shopgate.cms_page = "'.$this->cmsPage .'";';
				$mobileRedirectUrl = $this->getCmsUrl($this->cmsPage);
				break;
			case 'brand':
				if(!isset($this->manufactererName) || $this->manufactererName == ''){
					$this->redirectType = 'start';
					break;
				}
				$redirectCode = 'brand';
				$additionalParameters .= '_shopgate.brand_name = "'.$this->manufactererName.'";';
				$mobileRedirectUrl = $this->getBrandUrl($this->manufactererName);
				break;
			case 'search':
				if(!isset($this->searchQuery) || $this->searchQuery == ''){
					$this->redirectType = 'start';
					break;
				}
				$redirectCode = 'search';
				$additionalParameters .= '_shopgate.search_query = "'.$this->searchQuery.'";';
				$mobileRedirectUrl = $this->getSearchUrl($this->searchQuery);
				break;
			default: case 'start':
				$this->redirectType = 'start';
				break;
		}
		
		if($this->redirectType == 'start'){
			$mobileRedirectUrl = $this->getShopUrl();
			$redirectCode = 'start';
		}
		
		switch($this->config->getServer()){
			case 'pg':
				$sslUrl = 'https://static-ssl.shopgatepg.com';
				$nonSslUrl = 'http://static.shopgatepg.com';
				break;
			case 'custom':
				$sslUrl = 'https://shopgatedev-public.s3.amazonaws.com';
				$nonSslUrl = 'http://shopgatedev-public.s3.amazonaws.com';
				break;
			case 'live': default:
				$sslUrl = 'https://static-ssl.shopgate.com';
				$nonSslUrl = 'http://static.shopgate.com';
				break;
		}
		
		// set parameters
		$html = str_replace('{$mobile_url}', $mobileRedirectUrl, $html);
		$html = str_replace('{$shop_number}', $this->config->getShopNumber(), $html); // TODO
		$html = str_replace('{$redirect_code}', $redirectCode, $html); // TODO
		$html = str_replace('{$additional_parameters}', $additionalParameters, $html); // TODO
		$html = str_replace('{$ssl_url}', $sslUrl, $html); // TODO
		$html = str_replace('{$non_ssl_url}', $nonSslUrl, $html); // TODO

		return $html;
	}

	###############
	### helpers ###
	###############
	
	/**
	 * Generates the root mobile Url for the redirect
	 */
	protected function getMobileUrl(){
		if(($cname = $this->config->getCname()) &&!empty($cname)){
			return $this->config->getCname();
		} elseif(($alias = $this->config->getAlias()) && !empty($alias)){
			return 'https://'.$this->config->getAlias().$this->getShopgateUrl();
		}
	}

	/**
	 * Returns the URL to be appended to the alias of a shop.
	 *
	 * The method determines this by the "server" setting in ShopgateConfig. If it's set to
	 * "custom", localdev.cc will be used for Shopgate local development and testing.
	 *
	 * @return string The URL that can be appended to the alias, e.g. ".shopgate.com"
	 */
	protected function getShopgateUrl() {
		switch ($this->config->getServer()) {
			default: // fall through to "live"
			case 'live':	return ShopgateMobileRedirectInterface::SHOPGATE_LIVE_ALIAS;
			case 'pg':		return ShopgateMobileRedirectInterface::SHOPGATE_PG_ALIAS;
			case 'custom':	return '.localdev.cc/php/shopgate/index.php'; // for Shopgate development & testing
		}
	}

	/**
	 * Updates the (skip) keywords array from cache file or Shopgate Merchant API if enabled.
	 */
	protected function updateRedirectKeywords() {
		// load the keywords
		try {
			$redirectKeywordsFromFile = $this->loadKeywordsFromFile($this->config->getRedirectKeywordCachePath());
			$skipRedirectKeywordsFromFile = $this->loadKeywordsFromFile($this->config->getRedirectSkipKeywordCachePath());
		} catch (ShopgateLibraryException $e) {
			// if reading the files fails DO NOT UPDATE
			return;
		}
		
		// conditions for updating keywords
		$updateDesired = (
			$this->config->getEnableRedirectKeywordUpdate() &&
			(!empty($this->merchantApi)) && (
				(time() - ($redirectKeywordsFromFile['timestamp'] + ($this->redirectKeywordCacheTime * 3600)) > 0) ||
				(time() - ($skipRedirectKeywordsFromFile['timestamp'] + ($this->redirectKeywordCacheTime * 3600)) > 0)
			)
		);
		
		// strip timestamp, it's not needed anymore
		$redirectKeywords = $redirectKeywordsFromFile['keywords'];
		$skipRedirectKeywords = $skipRedirectKeywordsFromFile['keywords'];
		
		// perform update
		if ($updateDesired) {
			try {
				// fetch keywords from Shopgate Merchant API
				$keywordsFromApi = $this->merchantApi->getMobileRedirectUserAgents();
				$redirectKeywords = $keywordsFromApi['keywords'];
				$skipRedirectKeywords = $keywordsFromApi['skip_keywords'];
				
				// save keywords to their files
				$this->saveKeywordsToFile($redirectKeywords, $this->config->getRedirectKeywordCachePath());
				$this->saveKeywordsToFile($skipRedirectKeywords, $this->config->getRedirectSkipKeywordCachePath());
			} catch (Exception $e) { /* do not abort */ }
		}
		
		// set keywords
		if (!empty($redirectKeywords)) $this->redirectKeywords = $redirectKeywords;
		if (!empty($skipRedirectKeywords)) $this->skipRedirectKeywords = $skipRedirectKeywords;
	}
	
	/**
	 * Saves redirect keywords to file.
	 *
	 * @param string[] $keywords The list of keywords to write to the file.
	 * @param string $file The path to the file.
	 */
	protected function saveKeywordsToFile($keywords, $file) {
		array_unshift($keywords, time()); // add timestamp to first line
		if (!@file_put_contents($file, implode("\n", $keywords))) {
			// no logging - this could end up in spamming the logs
			// $this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::FILE_READ_WRITE_ERROR, 'Could not write to "'.$file.'".'));
		}
	}
	
	/**
	 * Reads redirect keywords from file.
	 *
	 * @param string $file The file to read the keywords from.
	 * @return array<'timestamp' => int, 'keywords' => string[])
	 * 			An array with the 'timestamp' of the last update and the list of 'keywords'.
	 * @throws ShopgateLibraryException in case the file cannot be opened.
	 */
	protected function loadKeywordsFromFile($file) {
		$defaultReturn = array(
			'timestamp' => 0,
			'keywords' => array()
		);
		
		$cacheFile = @fopen($file, 'a+');
		if (empty($cacheFile)) {
			// exception without logging
			throw new ShopgateLibraryException(ShopgateLibraryException::FILE_READ_WRITE_ERROR, 'Could not read file "'.$file.'".', false, false);
		}
		
		$keywordsFromFile = explode("\n", @fread($cacheFile, filesize($file)));
		@fclose($cacheFile);
		
		return (empty($keywordsFromFile))
			? $defaultReturn
			: array(
				'timestamp' => (int) array_shift($keywordsFromFile), // strip timestamp in first line
				'keywords' => $keywordsFromFile,
			);
	}

	#############################
	### mobile url generation ###
	#############################

	
	public function buildScriptItem($itemNumber, $autoRedirect = true){
		$this->itemNumber = $itemNumber;
		$this->redirectType = 'item';
		return $this->redirect($this->getItemUrl($itemNumber), $autoRedirect);
	}
	
	public function buildScriptItemPublic($itemNumberPublic, $autoRedirect = true){
		$this->itemNumberPublic = $itemNumberPublic;
		$this->redirectType = 'itempublic';
		return $this->redirect($this->getItemPublicUrl($itemNumberPublic), $autoRedirect);
	}
	
	public function buildScriptCategory($categoryNumber, $autoRedirect = true){
		$this->categoryNumber = $categoryNumber;
		$this->redirectType = 'category';
		return $this->redirect($this->getCategoryUrl($categoryNumber), $autoRedirect);
	}
	
	public function buildScriptShop($autoRedirect = true){
		$this->redirectType = 'start';
		return $this->redirect($this->getShopUrl(), $autoRedirect);
	}
	
	public function buildScriptCms($cmsPage, $autoRedirect = true){
		$this->cmsPage = $cmsPage;
		$this->redirectType = 'cms';
		return $this->redirect($this->getCmsUrl($cmsPage), $autoRedirect);
	}
	
	public function buildScriptBrand($manufacturerName, $autoRedirect = true){
		$this->manufactererName = $manufacturerName;
		$this->redirectType = 'brand';
		return $this->redirect($this->getBrandUrl($manufacturerName), $autoRedirect);
	}
	
	public function buildScriptSearch($searchQuery, $autoRedirect = true){
		$this->searchQuery = $searchQuery;
		$this->redirectType = 'search';
		return $this->redirect($this->getSearchUrl($searchQuery), $autoRedirect);
	}
	
	public function getShopUrl(){
		return $this->getMobileUrl();
	}

	public function getItemUrl($itemNumber){
		return $this->getMobileUrl().'/item/'.bin2hex($itemNumber);
	}

	public function getItemPublicUrl($itemNumberPublic){
		return $this->getMobileUrl().'/itempublic/'.bin2hex($itemNumberPublic);
	}

	public function getCategoryUrl($categoryNumber){
		return $this->getMobileUrl().'/category/'.bin2hex($categoryNumber);
	}

	public function getCmsUrl($cmsPage){
		return $this->getMobileUrl().'/cms/'.$cmsPage;
	}

	public function getBrandUrl($manufacturerName){
		return $this->getMobileUrl().'/brand/?q='.urlencode($manufacturerName);
	}

	public function getSearchUrl($searchQuery){
		return $this->getMobileUrl().'/search/?s='.urlencode($searchQuery);
	}
}


/**
 * Helper class for redirection from shop system to mobile webpage.
 *
 * Provides analyzation of the client's user agent, creation of redirection links for
 * different redirects (e.g. product, category, search), keyword updating and caching,
 * javascript for the "on/off" switch and sending the redirect headers to the client's
 * browser.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
interface ShopgateMobileRedirectInterface {
	const SHOPGATE_STATIC = 'http://static.shopgate.com';
	const SHOPGATE_STATIC_SSL = 'https://static-ssl.shopgate.com';

	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is live
	 */
	const SHOPGATE_LIVE_ALIAS = '.shopgate.com';

	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is on playground
	 */
	const SHOPGATE_PG_ALIAS = '.shopgatepg.com';

	/**
	 * @var string name of the cookie to set in case a customer turns of mobile redirect
	 */
	const COOKIE_NAME = 'SHOPGATE_MOBILE_WEBPAGE';

	/**
	 * @var int (hours) the minimum time that can be set for updating of the cache
	 */
	const MIN_CACHE_TIME = 1;

	/**
	 * @var int (hours) the default time to be set for updating the cache
	 */
	const DEFAULT_CACHE_TIME = 24;

	/**
	 * Detects by redirect keywords (and skip redirect keywords) if a request was sent by a mobile device.
	 *
	 * @return bool true if a mobile device could be detected, false otherwise.
	 */
	public function isMobileRequest();

	/**
	 * Detects whether the customer wants to be redirected.
	 *
	 * @return bool true if the customer wants to be redirected, false otherwise.
	 */
	public function isRedirectAllowed();

	/**
	 * Redirects to a given (valid) URL.
	 *
	 * If the $url parameter is no valid URL the method will simply return false and do nothing else.
	 * Otherwise it will output the necessary redirection headers and stop script execution.
	 *
	 * @param string $url the URL to redirect to
	 * @param bool $setCookie true to set the redirection cookie and activate redirection
	 * @return false if the passed $url parameter is no valid URL
	 */
	public function redirect($url);

	/**
	 * @deprecated
	 * Returns the javascript and HTML for the mobile redirect button
	 *
	 * @return string
	 */
	public function getMobileHeader();
	
	
	/**
	 * Generates a redirect to a item, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $itemNumber the product item number
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptItem($itemNumber, $autoRedirect = true);
	
	/**
	 * Generates a redirect to a item (with item number public), if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $itemNumberPublic the product item number public
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptItemPublic($itemNumberPublic, $autoRedirect = true);
	
	/**
	 * Generates a redirect to a category, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $categoryNumber the category number
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptCategory($categoryNumber, $autoRedirect = true);
	
	/**
	 * Generates a redirect to startmenu, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptShop($autoRedirect = true);
	
	/**
	 * Generates a redirect to cms page, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $cmsPage the cms page key
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptCms($cmsPage, $autoRedirect = true);
	
	/**
	 * Generates a redirect to manufacterer page, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $manufacturerName the manufacterer name
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptBrand($manufacturerName, $autoRedirect = true);
	
	/**
	 * Generates a redirect to a mobile search, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $searchString the search string
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptSearch($searchString, $autoRedirect = true);
	
	/**
	 * Create a mobile-shop-url to the startmenu
	 */
	public function getShopUrl();

	/**
	 * Create a mobile-product-url to a item
	 *
	 * @param string $itemNumber
	 */
	public function getItemUrl($itemNumber);

	/**
	 * Create a mobile-product-url to a item with item_number_public
	 *
	 * @param string $itemNumberPublic
	 */
	public function getItemPublicUrl($itemNumberPublic);

	/**
	 * Create a mobile-category-url to a category
	 *
	 * @param string $categoryNumber
	 */
	public function getCategoryUrl($categoryNumber);

	/**
	 * Create a mobile-cms-url to a cms-page
	 *
	 * @param string $key
	 */
	public function getCmsUrl($cmsPage);

	/**
	 * Create a mobile-brand-url to a page with results for a specific manufacturer
	 *
	 * @param string $manufacturerName
	 */
	public function getBrandUrl($manufacturerName);

	/**
	 * Create a mobile-search-url to a page with search results
	 *
	 * @param string $searchString
	 */
	public function getSearchUrl($searchQuery);
}

