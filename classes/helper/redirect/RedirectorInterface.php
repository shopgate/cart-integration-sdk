<?php

/**
 * Offers functions to redirect requesting entities to the mobile website, depending on the type.
 */
interface Shopgate_Helper_Redirect_RedirectorInterface
{
	/**
	 * @post If enabled, a location header to the mobile default/fallback page is sent to the requesting entity.
	 */
	public function redirectDefault();
	
	/**
	 * @post A location header to the mobile home page is sent to the requesting entity.
	 */
	public function redirectHome();
	
	/**
	 * @param string $uid
	 *
	 * @post A location header to the mobile category detail page is sent to the requesting entity.
	 */
	public function redirectCategory($uid);
	
	/**
	 * @param string $uid
	 *
	 * @post A location header to the mobile product page is sent to the requesting entity.
	 */
	public function redirectProduct($uid);
	
	/**
	 * @param string $pageName
	 *
	 * @post A location header to the mobile CMS page is sent to the requesting entity.
	 */
	public function redirectCms($pageName);
	
	/**
	 * @param string $brandName
	 *
	 * @post A location header to the mobile brand search is sent to the requesting entity.
	 */
	public function redirectBrand($brandName);
	
	/**
	 * @param string $searchString
	 *
	 * @post A location header to the mobile searchpage is sent to the requesting entity.
	 */
	public function redirectSearch($searchString);
	
	/**
	 * @param string $url      The URL to redirect to.
	 * @param bool   $sendVary True to send the "Vary: User-Agent" header.
	 */
	public function redirect($url, $sendVary = true);
}