<?php

require_once 'ShopgateOrderItem.php';
// require_once 'ShopgateContainerVisitor.php';

class ShopgateExternalOrderItem extends ShopgateOrderItem {

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->description = $value;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function visit(ShopgateContainerVisitor $v) {
		$v->visitExternalOrderItem($this);
	}

}
