<?php

/**
 * @method        setName(string $value)
 * @method string getName()
 *
 * @method                                            setAttributes(array $value)
 * @method Shopgate_Model_Redirect_HtmlTagAttribute[] getAttributes()
 */
class Shopgate_Model_Redirect_HtmlTag extends Shopgate_Model_Abstract
{
	public function __construct()
	{
		$this->allowedMethods = array(
			'Name',
			'Attributes',
		);
	}
}