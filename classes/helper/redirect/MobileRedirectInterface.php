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
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
interface Shopgate_Helper_Redirect_MobileRedirectInterface
{
	/**
	 * @param string $name One of the Shopgate_Helper_Redirect_TagsGeneratorInterface::SITE_PARAMETER_* constants.
	 * @param string $value
	 */
	public function addSiteParameter($name, $value);
	
	/**
	 * @param string $url
	 * @param bool   $autoRedirect
	 * @param bool   $sendVary
	 *
	 * @return
	 * @post ends script execution in case of http redirect
	 */
	public function redirect($url, $autoRedirect = true, $sendVary = true);
	
	/**
	 * @param bool $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptDefault($autoRedirect = true);
	
	/**
	 * @param bool $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptShop($autoRedirect = true);
	
	/**
	 * @param string $itemNumber
	 * @param bool   $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptItem($itemNumber, $autoRedirect = true);
	
	/**
	 * @param string $itemNumberPublic
	 * @param bool   $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptItemPublic($itemNumberPublic, $autoRedirect = true);
	
	/**
	 * @param string $categoryNumber
	 * @param bool   $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptCategory($categoryNumber, $autoRedirect = true);
	
	/**
	 * @param string $cmsPage
	 * @param bool   $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptCms($cmsPage, $autoRedirect = true);
	
	/**
	 * @param string $manufacturerName
	 * @param bool   $autoRedirect
	 *
	 * @return mixed
	 */
	public function buildScriptBrand($manufacturerName, $autoRedirect = true);
	
	/**
	 * @param string $searchQuery
	 * @param bool   $autoRedirect
	 *
	 * @return string
	 */
	public function buildScriptSearch($searchQuery, $autoRedirect = true);
}