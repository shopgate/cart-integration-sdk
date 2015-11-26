<?php

/**
 * @method        setName(string $value)
 * @method string getName()
 *
 * @method        setFunctionName(string $value)
 * @method string getFunctionName()
 */
class Shopgate_Model_Redirect_HtmlTagVariable extends Shopgate_Model_Abstract
{
	public function __construct()
	{
		$this->allowedMethods = array(
			'Name',
			'FunctionName',
		);
	}
}