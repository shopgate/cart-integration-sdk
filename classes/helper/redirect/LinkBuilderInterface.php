<?php

interface Shopgate_Helper_Redirect_LinkBuilderInterface
{
	/**
	 * @return string
	 */
	public function buildDefault();
	
	/**
	 * @return string
	 */
	public function buildHome();
	
	/**
	 * @param string $uid
	 *
	 * @return string
	 */
	public function buildProduct($uid);
	
	/**
	 * @param string $uid
	 *
	 * @return string
	 */
	public function buildCategory($uid);
	
	/**
	 * @param string $pageName
	 *
	 * @return string
	 */
	public function buildCms($pageName);
	
	/**
	 * @param string $brandName
	 *
	 * @return string
	 */
	public function buildBrand($brandName);
	
	/**
	 * @param string $searchString
	 *
	 * @return string
	 */
	public function buildSearch($searchString);
}