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
class Shopgate_Model_Redirect_DeeplinkSuffixValue extends Shopgate_Model_Abstract
{
	public function __construct()
	{
		$this->allowedMethods = array(
			'Name',
			'Value',
			'Disabled',
			'Variables',
		);
		
		$this->setVariables(array());
	}
}