<?php

class Shopgate_Helper_Redirect_HelperRedirect_MobileRedirect
	extends ShopgateObject
	implements Shopgate_Helper_Redirect_MobileRedirectInterface
{
	/** @var Shopgate_Helper_Redirect_RedirectorInterface */
	protected $redirector;
	
	/** @var Shopgate_Helper_Redirect_TagsGeneratorInterface */
	protected $tagsGenerator;
	
	/** @var Shopgate_Helper_Redirect_SettingsManagerInterface */
	protected $settingsManager;
	
	/** @var Shopgate_Helper_Redirect_TemplateParserInterface */
	protected $templateParser;
	
	/** @var string */
	protected $jsTemplateFilePath;
	
	/** @var string */
	protected $shopNumber;
	
	/**
	 * @var array [string, string] An array with the page names as indices and the "old" JS redirect types as values, if different.
	 */
	protected $pageTypeToRedirectMapping;
	
	/**
	 * ShopgateMobileRedirect constructor.
	 *
	 * @param Shopgate_Helper_Redirect_RedirectorInterface      $redirector
	 * @param Shopgate_Helper_Redirect_TagsGeneratorInterface   $tagsGenerator
	 * @param Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager
	 * @param Shopgate_Helper_Redirect_TemplateParserInterface  $templateParser
	 * @param string                                            $jsTemplateFilePath
	 * @param string                                            $shopNumber
	 */
	public function __construct(
		Shopgate_Helper_Redirect_RedirectorInterface $redirector,
		Shopgate_Helper_Redirect_TagsGeneratorInterface $tagsGenerator,
		Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager,
		Shopgate_Helper_Redirect_TemplateParserInterface $templateParser,
		$jsTemplateFilePath,
		$shopNumber
	) {
		$this->redirector         = $redirector;
		$this->tagsGenerator      = $tagsGenerator;
		$this->settingsManager    = $settingsManager;
		$this->templateParser     = $templateParser;
		$this->jsTemplateFilePath = $jsTemplateFilePath;
		$this->shopNumber         = $shopNumber;
		
		$this->pageTypeToRedirectMapping = array(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME    => 'start',
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT => 'item',
		);
		
		try {
			$htmlTags = $this->settingsManager->getHtmlTags();
			$this->tagsGenerator->setHtmlTagsFromJson($htmlTags);
		} catch (ShopgateLibraryException $e) {
			$this->tagsGenerator->setHtmlTagsFromJson($this->getFallBackTags());
		}
	}
	
	public function redirect($url, $autoRedirect = true, $sendVary = true)
	{
		if (!$autoRedirect) {
			return;
		}
		
		$this->redirector->redirect($url, $sendVary);
	}
	
	public function buildScriptDefault($autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectDefault();
		}
		
		return $this->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_DEFAULT);
	}
	
	public function buildScriptShop($autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectHome();
		}
		
		return $this->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME);
	}
	
	public function buildScriptItem($itemNumber, $autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectProduct($itemNumber);
		}
		
		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT,
			array('product_uid' => $itemNumber)
		);
	}
	
	public function buildScriptItemPublic($itemNumberPublic, $autoRedirect = true)
	{
		return $this->buildScriptItem($itemNumberPublic, $autoRedirect);
	}
	
	public function buildScriptCategory($categoryNumber, $autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectCategory($categoryNumber);
		}
		
		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY,
			array('category_uid' => $categoryNumber)
		);
	}
	
	public function buildScriptCms($cmsPage, $autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectCms($cmsPage);
		}
		
		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS,
			array('page_name' => $cmsPage)
		);
	}
	
	public function buildScriptBrand($manufacturerName, $autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectBrand($manufacturerName);
		}
		
		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND,
			array('brand_name' => $manufacturerName)
		);
	}
	
	public function buildScriptSearch($searchQuery, $autoRedirect = true)
	{
		if ($autoRedirect) {
			$this->redirector->redirectSearch($searchQuery);
		}
		
		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH,
			array('search_query' => $searchQuery)
		);
	}
	
	/**
	 * @param string $pageType
	 * @param array  $parameters [string, string]
	 *
	 * @return string
	 */
	protected function buildTags($pageType, $parameters = array())
	{
		$parameters['link_tags']     = $this->tagsGenerator->getTagsFor($pageType, $parameters);
		$parameters['redirect_code'] = $this->getRedirectCode($pageType);
		$parameters['shop_number']   = $this->shopNumber;
		$parameters += $this->settingsManager->getShopgateStaticUrl();
		
		// pre-process the additional parameters to add in the correct JS variables
		$variable = new Shopgate_Model_Redirect_HtmlTagVariable();
		$variable->setName('additional_parameters');
		$jsTemplate = $this->templateParser->process(
			@file_get_contents($this->jsTemplateFilePath),
			$variable,
			$this->getAdditionalParameters($pageType)
		);
		
		// process all variables left in the template (and those added with the processing of 'additional_parameters'.
		$variables = $this->templateParser->getVariables($jsTemplate);
		foreach ($variables as $variable) {
			$replacement = !empty($parameters[$variable->getName()])
				? $parameters[$variable->getName()]
				: '';
			
			$jsTemplate = $this->templateParser->process($jsTemplate, $variable, $replacement);
		}
		
		return $jsTemplate;
	}
	
	/**
	 * @return string
	 */
	protected function getFallBackTags()
	{
		return $this->jsonEncode(
			array(
				'html_tags' => array(
					array(
						'name'       => 'link',
						'attributes' => array(
							array(
								'name'  => 'rel',
								'value' => 'alternate',
							),
							array(
								'name'  => 'media',
								'value' => 'only screen and (max-width: 640px)',
							),
							array(
								'name'            => 'href',
								'value'           => '{deeplink_suffix}',
								'deeplink_suffix' => $this->settingsManager->getDefaultTemplatesByPageType(),
							),
						),
					),
				),
			)
		);
	}
	
	protected function getAdditionalParameters($pageType)
	{
		$defaultRedirect      = ($this->settingsManager->isDefaultRedirectDisabled() ? 'false' : 'true');
		$additionalParameters = "_shopgate.is_default_redirect_disabled = {$defaultRedirect};\n";
		
		switch ($pageType) {
			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY:
				$additionalParameters .= '_shopgate.category_number = "{category_uid}";';
				break;
			
			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT:
				$additionalParameters .= '_shopgate.item_number = "{product_uid}";';
				break;
			
			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS:
				$additionalParameters .= '_shopgate.cms_page = "{page_uid}";';
				break;
			
			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND:
				$additionalParameters .= '_shopgate.brand_name = "{brand_name}";';
				break;
			
			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH:
				$additionalParameters .= '_shopgate.search_query = "{search_query:escaped}";';
				break;
		}
		
		return $additionalParameters . "\n";
	}
	
	protected function getRedirectCode($pageType)
	{
		return isset($this->pageTypeToRedirectMapping[$pageType])
			? $this->pageTypeToRedirectMapping[$pageType]
			: $pageType;
	}
}