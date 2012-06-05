<?php
if (!defined('DS')) define('DS', '/');

// Library
require_once(dirname(__FILE__).DS.'classes'.DS.'core.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'customers.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'orders.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'items.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'redirect.php');

// Shopgate-Vendors
require_once(dirname(__FILE__).DS.'vendors'.DS.'2d_is.php');
require_once(dirname(__FILE__).DS.'vendors'.DS.'mobile_redirect.class.php');

include_once dirname(__FILE__).DS.'vendors'.DS.'shopgate_phpqrcode'.DS.'qrlib.php';
include_once dirname(__FILE__).DS.'vendors'.DS.'qr_code_manager.class.php';

// External-Vendors
if(!class_exists("Services_JSON")) include_once(dirname(__FILE__).DS.'vendors'.DS.'JSON.php');
