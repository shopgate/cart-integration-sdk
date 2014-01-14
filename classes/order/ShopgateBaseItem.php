<?php

//require_once '../core/ShopgateContainer.php';

abstract class ShopgateBaseItem extends ShopgateContainer {

	/**
	 * @var string
	 */
	protected $item_number;

	/**
	 * @param string $value
	 */
	public function setItemNumber($value) {
		$this->item_number = $value;
	}

	/**
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}

}
