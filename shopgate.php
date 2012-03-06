<?php
if (!defined('DS')) define('DS', '/');

require_once(dirname(__FILE__).DS.'classes'.DS.'core.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'customers.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'orders.php');

require_once(dirname(__FILE__).DS.'vendors'.DS.'2d_is.php');
require_once(dirname(__FILE__).DS.'vendors'.DS.'JSON.php');
require_once(dirname(__FILE__).DS.'vendors'.DS.'mobile_redirect.class.php');

include_once dirname(__FILE__).DS.'vendors'.DS.'shopgate_qr_encoder'.DS.'qr_encoder.php';

try {
	$config = ShopgateConfig::validateAndReturnConfig();

	$plugin = $config["plugin"];

	if(file_exists(SHOPGATE_BASE_DIR.'/plugins/'.$plugin)
	&& file_exists(SHOPGATE_BASE_DIR.'/plugins/'.$plugin.'/includes.php')) {
		include_once SHOPGATE_BASE_DIR.'/plugins/'.$plugin.'/includes.php';
	}

} catch (Exception $e) {
	var_dump($e->getMessage());
}