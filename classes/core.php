<?php
class ShopgateException extends Exception {
	
}

class ShopgateObject {
	
}

class ShopgateLibraryCore extends ShopgateObject {
	private static $singleton;
	
	private function __construct() { }
	
	public static function &getInstance() {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}
		
		return self::$singleton;
	}
}

class ShopgateConfig extends ShopgateObject {

}

class ShopgateMerchantApi extends ShopgateObject {
	
}

class ShopgatePluginApi extends ShopgateObject {
	/**
	 * @var ShopgateLibraryCore
	 */
	protected $core;
	
	public function __construct() {
		$this->core = ShopgateLibraryCore::getInstance();
	}
	
	public function handleRequest($data = array()) {
		
	}
}