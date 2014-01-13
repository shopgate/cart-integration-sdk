<?php

//require_once '../core/ShopgateContainer.php';

abstract class ShopgateOrderBaseItem extends ShopgateContainer {

	/**
	 * @var string
	 */
	protected $item_number = null;

	/**
	 * @var string
	 */
	protected $item_number_public = null;

	/**
	 * @param string $value
	 */
	public function setItemNumber($value) {
		$this->item_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setItemNumberPublic($value) {
		$this->item_number_public = $value;
	}

	/**
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}

	/**
	 * return string
	 */
	public function getItemNumberPublic() {
		return $this->item_number_public;
	}

}
