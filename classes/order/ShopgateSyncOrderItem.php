<?php

require_once 'ShopgateOrderBaseItem.php';
//require_once '../core/ShopgateContainerVisitor.php';

class ShopgateSyncOrderItem extends ShopgateOrderBaseItem {

	const STATUS_NEW = 'new';
	const STATUS_DELETED = 'deleted';
	const STATUS_EXISTING = 'existing';

	/**
	 * @var string either one of new, deleted, existing
	 */
	protected $status = null;

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
		$v->visitSyncOrderItem($this);
	}

}
