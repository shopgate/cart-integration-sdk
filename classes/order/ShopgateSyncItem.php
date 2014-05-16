<?php

//require_once 'ShopgateOrderBaseItem.php'; // TODO: possibly wrong filename, could it be meant to be ShopgateBaseItem.php?
//require_once '../core/ShopgateContainerVisitor.php';

class ShopgateSyncItem extends ShopgateContainer {

	const STATUS_NEW = 'new';
	const STATUS_DELETED = 'deleted';
	const STATUS_EXISTING = 'existing';

	/**
	 * @var string
	 */
	protected $item_number;

	/**
	 * @var string either one of new, deleted, existing
	 */
	protected $status = null;

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
	
	/**
	 * @param string $value
	 */
	public function setStatus($value) {
		if (
			self::STATUS_NEW != $value &&
			self::STATUS_DELETED != $value &&
			self::STATUS_EXISTING != $value
		) {
			$value = null;
		}

		$this->status = $value;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitSyncItem($this);
	}

}
