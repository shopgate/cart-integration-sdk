<?php

class Shopgate_Model_Redirect_DeeplinkSuffix extends Shopgate_Model_Abstract
{
	/** @var Shopgate_Model_Redirect_DeeplinkSuffixValue [string, Shopgate_Model_Redirect_DeeplinkSuffixValue] */
	protected $valuesByType;
	
	/**
	 * @param string                                      $type
	 * @param Shopgate_Model_Redirect_DeeplinkSuffixValue $value
	 */
	public function addValue($type, Shopgate_Model_Redirect_DeeplinkSuffixValue $value)
	{
		$this->valuesByType[$type] = $value;
	}
	
	/**
	 * @param string $type
	 *
	 * @return Shopgate_Model_Redirect_DeeplinkSuffixValue
	 */
	public function getValue($type)
	{
		return empty($this->valuesByType[$type])
			? new Shopgate_Model_Redirect_DeeplinkSuffixValueDisabled()
			: $this->valuesByType[$type];
	}
}