<?php

/**
 * @method        setName(string $value)
 * @method string getName()
 *
 * @method        setValue(string $value)
 * @method string getValue()
 *
 * @method      setDisabled(bool $value)
 * @method bool getDisabled()
 *
 * @method                                           setVariables($value)
 * @method Shopgate_Model_Redirect_HtmlTagVariable[] getVariables()
 */
class Shopgate_Model_Redirect_DeeplinkSuffixValueDisabled extends Shopgate_Model_Redirect_DeeplinkSuffixValue
{
	public function __construct()
	{
		parent::__construct();
		
		$this->setDisabled(true);
	}
}