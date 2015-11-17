<?php

class Shopgate_Helper_Redirect_LinkBuilder implements Shopgate_Helper_Redirect_LinkBuilderInterface
{
	const LINK_TYPE_DEFAULT  = 'default';
	const LINK_TYPE_HOME     = 'home';
	const LINK_TYPE_PRODUCT  = 'product';
	const LINK_TYPE_CATEGORY = 'category';
	const LINK_TYPE_CMS      = 'cms';
	const LINK_TYPE_BRAND    = 'brand';
	const LINK_TYPE_SEARCH   = 'search';
	
	/** @var string[] [string, string] A list of paths indexed by their type. */
	protected $paths;
	
	/** @var Shopgate_Helper_Redirect_SettingsManagerInterface */
	protected $settingsManager;
	
	/**
	 * @param Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager
	 */
	public function __construct(Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager)
	{
		$this->settingsManager = $settingsManager;
		
		$this->paths = array(
			self::LINK_TYPE_PRODUCT  => 'item',
			self::LINK_TYPE_CATEGORY => 'category',
			self::LINK_TYPE_CMS      => 'cms',
			self::LINK_TYPE_BRAND    => 'brand',
			self::LINK_TYPE_SEARCH   => 'search',
		);
	}
	
	public function buildDefault()
	{
		return $this->appendRedirectableGetParameters($this->getBaseUrlFor(self::LINK_TYPE_DEFAULT) . '/');
	}
	
	public function buildHome()
	{
		return $this->appendRedirectableGetParameters($this->getBaseUrlFor(self::LINK_TYPE_HOME) . '/');
	}
	
	public function buildProduct($uid)
	{
		return
			$this->appendRedirectableGetParameters(
				$this->getBaseUrlFor(self::LINK_TYPE_PRODUCT) . '/' . $this->bin2hex($uid)
			);
	}
	
	public function buildCategory($uid)
	{
		return
			$this->appendRedirectableGetParameters(
				$this->getBaseUrlFor(self::LINK_TYPE_CATEGORY) . '/' . $this->bin2hex($uid)
			);
	}
	
	public function buildCms($pageName)
	{
		return
			$this->appendRedirectableGetParameters(
				$this->getBaseUrlFor(self::LINK_TYPE_CMS) . '/' . $pageName
			);
	}
	
	public function buildBrand($brandName)
	{
		return
			$this->appendRedirectableGetParameters(
				$this->getBaseUrlFor(self::LINK_TYPE_BRAND) . '?q=' . $this->urlencode($brandName),
				'&'
			);
	}
	
	public function buildSearch($searchString)
	{
		return
			$this->appendRedirectableGetParameters(
				$this->getBaseUrlFor(self::LINK_TYPE_SEARCH) . '?s=' . $this->urlencode($searchString),
				'&'
			);
	}
	
	/**
	 * @param string $type
	 *
	 * @return string
	 */
	protected function getBaseUrlFor($type)
	{
		$path = empty($this->paths[$type])
			? ''
			: $this->paths[$type];
		
		return $this->settingsManager->getMobileUrl() . '/' . $path;
	}
	
	/**
	 * @param string $url
	 * @param string $concat
	 *
	 * @return string
	 */
	protected function appendRedirectableGetParameters($url, $concat = '?')
	{
		return
			$url . (
			($this->settingsManager->getRedirectableGetParameters())
				? $concat . $this->settingsManager->getRedirectableGetParameters()
				: ''
			);
	}
	
	/**
	 * @param string $string
	 *
	 * @return string
	 */
	protected function bin2hex($string)
	{
		return bin2hex($string);
	}
	
	/**
	 * @param string $string
	 *
	 * @return string
	 */
	protected function urlencode($string)
	{
		return urlencode($string);
	}
}