<?php

###################################################################################
# define constants
###################################################################################
define('SHOPGATE_LIBRARY_VERSION', "2.0.0");
define('SHOPGATE_BASE_DIR', realpath(dirname(__FILE__).'/../'));
define('SHOPGATE_ITUNES_URL', 'http://itunes.apple.com/de/app/shopgate-eine-app-alle-shops/id365287459?mt=8');

## QR-Code Config - Start
if (!defined('QR_CACHEABLE'))			define('QR_CACHEABLE', false);
if (!defined('QR_CACHE_DIR'))			define('QR_CACHE_DIR', false);
if (!defined('QR_LOG_DIR'))				define('QR_LOG_DIR', dirname(__FILE__).'/../temp/');
if (!defined('QR_FIND_BEST_MASK'))		define('QR_FIND_BEST_MASK', true);
if (!defined('QR_FIND_FROM_RANDOM'))	define('QR_FIND_FROM_RANDOM', 2);
if (!defined('QR_DEFAULT_MASK'))		define('QR_DEFAULT_MASK', 2);
if (!defined('QR_PNG_MAXIMUM_SIZE'))	define('QR_PNG_MAXIMUM_SIZE',  1024);
## QR-Code Config - End

/**
 * Error handler for PHP errors.
 *
 * To use the Shopgate error handler it must be activated in your configuration.
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @see http://php.net/manual/en/function.set-error-handler.php
 */
function ShopgateErrorHandler($errno, $errstr, $errfile, $errline) {
	// make no difference between exceptions and E_WARNING
	$msg = "Fatal PHP Error [Nr. $errno : $errfile / $errline] ";
	$msg .= "$errstr";
	$msg .= "\n". print_r(debug_backtrace(false), true);

	ShopgateObject::logWrite($msg);

	return true;
}


/**
 * Exception type for errors within the Shopgate Library.
 *
 * This is used by the Shopgate Library and should be used by plugins and their components. Predefined error
 * codes and messages should be used. If not suitable, a custom message can be passed which results in error
 * code 999 (unknown error code) with the message appended. Error code, message, time, additional information
 * and part of the stack trace will be logged automatically on construction of a ShopgateLibraryException.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateLibraryException extends Exception {
	/**
	 * @var string
	 */
	private $additionalInformation;

	// Initizialization / instantiation of plugin failure
	//const INIT_EMPTY_CONFIG = 1;
	const INIT_LOGFILE_OPEN_ERROR = 2;

	// Configuration failure
	const CONFIG_INVALID_VALUE = 10;
	const CONFIG_READ_WRITE_ERROR = 11;

	// Plugin API errors
	const PLUGIN_API_NO_ACTION = 20;
	const PLUGIN_API_UNKNOWN_ACTION = 21;
	const PLUGIN_API_DISABLED_ACTION = 22;
	const PLUGIN_API_WRONG_RESPONSE_FORMAT = 23;

	const PLUGIN_API_NO_ORDER_NUMBER = 30;
	const PLUGIN_API_NO_USER = 35;
	const PLUGIN_API_NO_PASS = 36;
	const PLUGIN_API_NO_PAYMENT = 37;

	const PLUGIN_API_UNKNOWN_LOGTYPE = 38;

	// Plugin errors
	const PLUGIN_DUPLICATE_ORDER = 60;
	const PLUGIN_ORDER_NOT_FOUND = 61;

	const PLUGIN_NO_ADDRESSES_FOUND = 70;
	const PLUGIN_WRONG_USERNAME_OR_PASSWORD = 71;

	const PLUGIN_FILE_NOT_FOUND = 80;
	const PLUGIN_FILE_OPEN_ERROR = 81;
	const PLUGIN_FILE_EMPTY_BUFFER = 82;
	const PLUGIN_DATABASE_ERROR = 83;
	const PLUGIN_UNKNOWN_COUNTRY_CODE = 84;
	const PLUGIN_UNKNOWN_STATE_CODE = 85;

	// Merchant API errors
	const MERCHANT_API_NO_CONNECTION = 100;
	const MERCHANT_API_INVALID_RESPONSE = 101;
	const MERCHANT_API_ERROR_RECEIVED = 102;

	// Authentification errors
	const AUTHENTIFICATION_FAILED = 120;

	// Unknown error code (the value passed as code gets to be the message)
	const UNKNOWN_ERROR_CODE = 999;

	protected static $errorMessages = array(
		// Initizialization / instantiation of plugin failure
		//self::INIT_EMPTY_CONFIG => 'empty configuration',
		self::INIT_LOGFILE_OPEN_ERROR => 'cannot open/create logfile(s)',

		// Configuration failure
		self::CONFIG_INVALID_VALUE => 'invalid value in configuration',
		self::CONFIG_READ_WRITE_ERROR => 'error reading or writing configuration',

		// Plugin API errors
		self::PLUGIN_API_NO_ACTION => 'no action specified',
		self::PLUGIN_API_UNKNOWN_ACTION  => 'unkown action requested',
		self::PLUGIN_API_DISABLED_ACTION => 'disabled action requested',
		self::PLUGIN_API_WRONG_RESPONSE_FORMAT => 'wrong response format',

		self::PLUGIN_API_NO_ORDER_NUMBER => 'parameter "order_number" missing',
		self::PLUGIN_API_NO_USER => 'parameter "user" missing',
		self::PLUGIN_API_NO_PASS => 'parameter "pass" missing',
		self::PLUGIN_API_NO_PAYMENT => 'parameter "payment" missing',
		self::PLUGIN_API_UNKNOWN_LOGTYPE => 'unknown logtype',

		// Plugin errors
		self::PLUGIN_DUPLICATE_ORDER => 'duplicate order',
		self::PLUGIN_ORDER_NOT_FOUND => 'order not found',

		self::PLUGIN_NO_ADDRESSES_FOUND => 'no addresses found for customer',
		self::PLUGIN_WRONG_USERNAME_OR_PASSWORD => 'wrong username or password',

		self::PLUGIN_FILE_NOT_FOUND => 'file not found',
		self::PLUGIN_FILE_OPEN_ERROR => 'cannot open file',
		self::PLUGIN_FILE_EMPTY_BUFFER => 'buffer is empty',
		self::PLUGIN_DATABASE_ERROR => 'database error',
		self::PLUGIN_UNKNOWN_COUNTRY_CODE => 'unknown country code',
		self::PLUGIN_UNKNOWN_STATE_CODE => 'unknown state code',

		// Merchant API errors
		self::MERCHANT_API_NO_CONNECTION => 'no connection to server',
		self::MERCHANT_API_INVALID_RESPONSE => 'error parsing response',
		self::MERCHANT_API_ERROR_RECEIVED => 'error code received',

		// Authentification errors
		self::AUTHENTIFICATION_FAILED => 'authentification failed',
	);


	/**
	 * Exception type for errors within the Shopgate plugin and library.
	 *
	 * The general exception message is determined by the error code, the additionalInformation
	 * argument, if set, is appended.<br />
	 * <br />
	 * For compatiblity reasons, if an unknown error code is passed, the value is used as message
	 * and the code 999 (Unknown error code) is assigned. This should not be used anymore, though.
	 *
	 * @param int $code One of the constants defined in ShopgateLibraryException.
	 * @param string $additionalInformation More detailed information on what exactly went wrong.
	 * @param boolean $appendAdditionalInformationOnMessage Set true to output the additional information to the response. Set false to log it silently.
	 */
	public function __construct($code, $additionalInformation = null, $appendAdditionalInformationOnMessage = false) {
		// Set code and message
		$logMessage = self::buildLogMessageFor($code, $additionalInformation);
		if (isset(self::$errorMessages[$code])) {
			$message = self::$errorMessages[$code];
		} else {
			$message = 'Unknown error code: "'.$code.'"';
			$code = self::UNKNOWN_ERROR_CODE;
		}

		if($appendAdditionalInformationOnMessage){
			$message .= ': '.$additionalInformation;
		}

		// Save additional information
		$this->additionalInformation = $additionalInformation;

		// Log the error
		if(ShopgateObject::logWrite($code.' - '.$logMessage) === false){
			$message .= ' (unable to log)';
		}

		// Call default Exception class constructor
		parent::__construct($message, $code);
	}

	/**
	 * Returns the saved additional information.
	 *
	 * @return string
	 */
	public function getAdditionalInformation(){
		return (!is_null($this->additionalInformation) ? $this->additionalInformation : '');
	}

	/**
	 * Gets the error message for an error code.
	 *
	 * @param int $code One of the constants in this class.
	 */
	public static function getMessageFor($code) {
		if (isset(self::$errorMessages[$code])) {
			$message = self::$errorMessages[$code];
		} else {
			$message = 'Unknown error code: "'.$code.'"';
		}

		return $message;
	}

	/**
	 * Builds the message that would be logged if a ShopgateLibraryException was thrown with the same parameters and returns it.
	 *
	 * This is a convenience method for cases where logging is desired but the script should not abort. By using this function an empty
	 * try-catch-statement can be avoided. Just pass the returned string to ShopgateObject::logWrite().
	 *
	 * @param int $code One of the constants defined in ShopgateLibraryException.
	 * @param string $additionalInformation More detailed information on what exactly went wrong.
	 */
	public static function buildLogMessageFor($code, $additionalInformation) {
		$logMessage = self::getMessageFor($code);

		// Set additional information
		if (!empty($additionalInformation)) {
			$logMessage .= ' - Additional information: "'.$additionalInformation.'"';
		}

		// Add tracing information to the message
		$btrace = debug_backtrace();
		$btrace = $btrace[2];
		$logMessage = (isset($btrace["class"])
			? $btrace["class"]."::"
			: "")
		.$btrace["function"]."():".$btrace["line"]." - " . print_r($logMessage, true);

		return $logMessage;
	}
}


/**
 * Einstellungen für das Framework
 *
 * @author Daniel Aigner
 * @version 1.0.0
 */
class ShopgateConfig extends ShopgateObject {

	/**
	 * Die Standardeinstellungen.
	 *
	 * Die hier festgelegten Einstellungen werden aus der Datei
	 * config.php bzw. myconfig.php überschrieben und erweitert
	 *
	 * - api_url -> Die URL zum Shopgate-Server.
	 * - customer_number -> Die Kundennummer des Händleraccounts
	 * - apikey -> Der API-Key des Händlers. Dieser muss nach änderung angepasst werden.
	 * - shop_number -> Die Nummer des Shops.
	 * - server -> An welchen Server die Daten gesendet werden.
	 * - plugin -> Das PlugIn, welches verwendet werden soll.
	 * - plugin_language -> Spracheinstellung für das Plugin. Zur Zeit nur DE.
	 * - plugin_currency -> Währungseinstellung für das Plugin. Zur Zeit nur EUR.
	 * - plugin_root_dir -> Das Basisverzeichniss für das PlugIn.
	 * - enable_ping -> Ping erlaubt.
	 * - enable_get_shop_info -> Infos ueber das Shopsystem abholen
	 * - enable_add_order -> Übergeben von bestelldaten erlaubt.
	 * - enable_update_order -> Übergeben von bestelldaten erlaubt.
	 * - enable_connect -> Shopgate Connect erlaubt.
	 * - enable_get_items_csv -> Abholen der Produkt-CSV erlaubt.
	 * - enable_get_reviews_csv -> Abholen der Review-CSV erlaubt.
	 * - enable_get_pages_csv -> Abholen der Pages-CSV erlaubt.
	 * - enable_get_log_file -> Abholen der Log-Files erlaubt
	 * - generate_items_csv_on_the_fly -> Die CSV direkt beim Download erstellen
	 *
	 * @var array
	 */
	private static $config =  array(
		'api_url' => 'https://api.shopgate.com/merchant/',
		'customer_number' => 'THE_CUSTOMER_NUMBER',
		'shop_number' => 'THE_SHOP_NUMBER',
		'apikey' => 'THE_API_KEY',
		'server' => 'live',
		'plugin' => 'example',
		'plugin_language' => 'DE',
		'plugin_currency' => 'EUR',
		'plugin_root_dir' => "",
		'enable_ping' => true,
		'enable_add_order' => true,
		'enable_update_order' => true,
		'enable_get_customer' => true,
		'enable_get_categories_csv' => true,
		'enable_get_orders' => true,
		'enable_get_items_csv' => true,
		'enable_get_reviews_csv' => true,
		'enable_get_pages_csv' => true,
		'enable_get_log_file' => true,
		'enable_mobile_website' => true,
		'generate_items_csv_on_the_fly' => true,
		'max_attributes' => 50,
		'use_custom_error_handler' => false,
	);

	/**
	 * Übergeben und überprüfen der Einstellungen.
	 *
	 * @param array $newConfig
	 */
	public static final function setConfig(array $newConfig, $validate = true) {
		if($validate) {
			self::validateConfig($newConfig);
		}
		self::$config = array_merge(self::$config, $newConfig);
	}

	/**
	 * Gibt das Konfigurations-Array zurück.
	 */
	public static final function validateAndReturnConfig() {
		try {
			self::validateConfig(self::$config);
		} catch (ShopgateLibraryException $e) { throw $e; }

		return self::getConfig();
	}

	/**
	 *
	 * Returnd the configuration without validating
	 * @return array
	 */
	public static function getConfig() {
		return self::$config;
	}

	public static function getConfigField($field) {
		if(isset(self::$config[$field])) return self::$config[$field];
		else return null;
	}

	public static final function getPluginName() {
		return self::$config["plugin"];
	}

	/**
	 * Gibt den Pfad zur Error-Log-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 */
	public static final function getLogFilePath($type="error") {
		if($type==="access") {
			if(isset(self::$config['path_to_access_log_file'])) {
				return self::$config['path_to_access_log_file'];
			} else {
				return SHOPGATE_BASE_DIR.'/temp/logs/access.log';
			}
		} else {
			if(isset(self::$config['path_to_error_log_file'])) {
				return self::$config['path_to_error_log_file'];
			} else {
				return SHOPGATE_BASE_DIR.'/temp/logs/error.log';
			}
		}
	}

	/**
	 * Gibt den Pfad zur items-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 */
	public static final function getItemsCsvFilePath() {
		if(isset(self::$config['path_to_items_csv_file'])) {
			return self::$config['path_to_items_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/items.csv';
		}
	}

	public static final function getCategoriesCsvFilePath() {
		if(isset(self::$config['path_to_categories_csv_file'])) {
			return self::$config['path_to_categories_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/categories.csv';
		}
	}

	/**
	 * Gibt den Pfad zur review-csv-Datei zurück
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden
	 */
	public static final function getReviewsCsvFilePath() {
		if(isset(self::$config['path_to_reviews_csv_file'])) {
			return self::$config['path_to_reviews_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/reviews.csv';
		}
	}

	/**
	 * Gibt den Pfad zur pages-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 */
	public static final function getPagesCsvFilePath() {
		if(isset(self::$config['path_to_pages_csv_file'])) {
			return self::$config['path_to_pages_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/pages.csv';
		}
	}

	/**
	 * Prüft, ob alle Pflichtfelder gesetzt sind und setzt die api_url.
	 *
	 * @param array $newConfig
	 * @throws ShopgateLibraryException
	 */
	private static function validateConfig(array $newConfig) {
		//Pflichtfelder überprüfen
		if (!preg_match("/^\S+/", $newConfig['apikey'])) {
			throw new ShopgateLibraryException(
				ShopgateLibraryException::CONFIG_INVALID_VALUE,
				"Field 'apikey' contains invalid value '{$newConfig['apikey']}'."
			);
		}
		if(!preg_match("/^\d{5,}$/", $newConfig['customer_number'])){
			throw new ShopgateLibraryException(
				ShopgateLibraryException::CONFIG_INVALID_VALUE,
				"Field 'customer_number' contains invalid value '{$newConfig['customer_number']}'."
			);
		}
		if (!preg_match("/^\d{5,}$/", $newConfig['shop_number'])) {
			throw new ShopgateLibraryException(
				ShopgateLibraryException::CONFIG_INVALID_VALUE,
				"Field 'shop_number' contains invalid value '{$newConfig['shop_number']}'."
			);
		}

		////////////////////////////////////////////////////////////////////////
		// Server URL setzen
		////////////////////////////////////////////////////////////////////////
		if(!empty($newConfig["server"]) && $newConfig["server"] === "pg") {
			// Playground?
			self::$config["api_url"] = "https://api.shopgatepg.com/merchant/";
		} else if(!empty($newConfig["server"]) && $newConfig["server"] === "custom"
		&& !empty($newConfig["server_custom_url"])) {
			// Eigener Test-Server?
			self::$config["api_url"] = $newConfig["server_custom_url"];
		} else {
			// Live-Server?
			self::$config["api_url"] = "https://api.shopgate.com/merchant/";
		}
	}

	public static function saveConfig() {
		$config = self::getConfig();

		$returnString  = "<?php"."\r\n";

		$returnString .= "\$shopgate_config = array();\r\n";

		foreach($config as $key => $field)
		{
			if($key != 'save')
			{
				if(is_bool($field) || $field === "true" || $field === "false") {
					if($field === "true") $field = true;
					if($field === "false") $field = false;

					$returnString .= '$shopgate_config["'.$key.'"] = '.($field?'true':'false').';'."\r\n";
				}
				else if(is_numeric($field)) {
					$returnString .= '$shopgate_config["'.$key.'"] = '.$field.';'."\r\n";
				}
				else {
					$returnString .= '$shopgate_config["'.$key.'"] = "'.$field.'";'."\r\n";
				}
			}
		}

		$message = "";
		$handle = @fopen(dirname(__FILE__).'/../config/myconfig.php', 'w+');
		if($handle == false){
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR);
			fclose($handle);
		}else{
			if(!fwrite($handle, $returnString))
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR);
		}

		fclose($handle);
	}
}

###################################################################################
# Config Datei
###################################################################################

if(!isset($shopgate_config)) {
	if (file_exists(SHOPGATE_BASE_DIR.DS.'config'.DS.'myconfig.php')) {
		require_once SHOPGATE_BASE_DIR.DS.'config'.DS.'myconfig.php';
	} else if (file_exists(SHOPGATE_BASE_DIR.DS.'config'.DS.'config.php')) {
		require_once SHOPGATE_BASE_DIR.DS.'config'.DS.'config.php';
	}
}

if (file_exists(SHOPGATE_BASE_DIR.DS.'config'.DS.'/devconfig.php')) {
	require_once SHOPGATE_BASE_DIR.DS.'config'.DS.'/devconfig.php';
}

if (isset($shopgate_config) && is_array($shopgate_config)) {
	try {
		ShopgateConfig::setConfig($shopgate_config, false);
	} catch (Exception $e) {
		$response = array(
			"error"=>true,
			"error_text"=>$e->getMessage(),
		);
		// TODO: echo sg_json_encode($response);
		exit;
	}
}

/**
 * ShopgateObject acts as root class of the Shopgate Library.
 *
 * It provides basic functionality like logging, camelization of strings, JSON de- and encoding etc.<br />
 * <br />
 * All classes of the ShopgateLibrary except ShopgateLibraryException are derived from this class.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgateObject {
	const LOGTYPE_ACCESS = 'access';
	const LOGTYPE_ERROR = 'error';

	/**
	 * @var resource[]
	 */
	private static $fileHandles = array(
		self::LOGTYPE_ACCESS => null,
		self::LOGTYPE_ERROR => null,
	);

	/**
	 * @var int
	 */
	private static $instanceCount = 0;

	/**
	 * Takes care of file handle initialization and the instance count of ShopgateObjects.
	 *
	 * This cannot be overridden. Subclasses that are part of the Shopgate Library should implement initLibrary() for initialization
	 * stuff. Take a look at the ShopgatePlugin class for the very similar startup() construct that plugin implementations can use.<br />
	 * <br />
	 * All parameters this method is called with are passed in the same way to initLibrary().
	 */
	public final function __construct() {
		self::$instanceCount++;
		self::init();

		// call the initLibrary() callback and pass arguments
		$args = func_get_args();
		call_user_func_array(array($this, 'initLibrary'), $args);
	}

	/**
	 * Takes care of the instance count of ShopgateObjects and uninitializes the file handles on destruction of the last object.
	 */
	public final function __destruct() {
		self::$instanceCount--;
		self::unInit();
	}

	/**
	 * Callback function for initialization by subclasses.
	 */
	protected function initLibrary() {
		// does nothing here but should not be abstract to avoid empty methods in sub classes that don't need it
	}

	/**
	 * Initializes the file handles for logging if necessary.
	 */
	protected static function init() {
		// initialize file handlers if neccessary
		foreach (self::$fileHandles as $type => $handle) {
			if (empty($handle)) {
				$path = ShopgateConfig::getLogFilePath($type);
				$newHandle = @fopen($path, 'a+');

				// if log files are not writeable continue silently to the next handler
				// TODO: This seems a bit too silent... How could we get notice of the error?
				if ($newHandle === false) continue;

				// set the file handler
				self::$fileHandles[$type] = $newHandle;
			}
		}
	}

	/**
	 * Unsets the file handles for logging if set and no instance of ShopgateObject exists anymore.
	 */
	protected static function unInit() {
		if (self::$instanceCount > 0) return;

		// close file handles on destruction of the last object
		foreach (self::$fileHandles as $type => $handle) {
			if (!empty($handle)) {
				fclose($handle);
				self::$fileHandles[$type] = null;
			}
		}
	}

	/**
	 * Convenience method for logging with $this
	 *
	 * This just passes the argument to the static ShopgateObject::logWrite() method.
	 *
	 * @see ShopgateObject::logWrite($msg, $type)
	 * @param string $msg The error message.
	 * @param string $type The log type, that would be one of the ShopgateObject::LOGTYPE_* constants.
	 * @return bool True on success, false on error.
	 */
	public function log($msg, $type = self::LOGTYPE_ERROR) {
		return self::logWrite($msg, $type);
	}

	/**
	 * Logs a message to the according log file.
	 *
	 * This produces a log entry of the form<br />
	 * <br />
	 * [date] [time]: [message]\n<br />
	 * <br />
	 * to the selected log file. If an unknown log type is passed the message will be logged to the error log file.
	 *
	 * @param string $msg The error message.
	 * @param string $type The log type, that would be one of the ShopgateObject::LOGTYPE_* constants.
	 * @return bool True on success, false on error.
	 */
	public static function logWrite($msg, $type = self::LOGTYPE_ERROR) {
		// initialize if neccessary
		self::init();

		// build log message
		$msg = gmdate('d-m-Y H:i:s: ').$msg."\n";

		// determine log file type and append message
		switch (strtolower($type)) {
			// write to error log if type is unknown
			default: $type = self::LOGTYPE_ERROR;

			// allowed types:
			case self::LOGTYPE_ERROR:
			case self::LOGTYPE_ACCESS:
		}

		// try to log
		$success = false;
		if (!empty(self::$fileHandles[$type])) {
			if (fwrite(self::$fileHandles[$type], $msg) !== false) {
				$success = true;
			}
		}

		// uninitialize if neccessary
		self::unInit();

		return $success;
	}

	/**
	 * Converts a an underscored string to a camelized one.
	 *
	 * e.g.:<br />
	 * $this->camelize("get_categories_csv") returns "getCategoriesCsv"<br />
	 * $this->camelize("shopgate_library", true) returns "ShopgateLibrary"<br />
	 *
	 * @param string $str The underscored string.
	 * @param bool $capitalizeFirst Set true to capitalize the first letter (e.g. for class names). Default: false.
	 * @return string The camelized string.
	 */
	public function camelize($str, $capitalizeFirst = false) {
		if($capitalizeFirst) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z0-9])/', $func, $str);
	}

	/**
	 * Creates a JSON string from any passed value.
	 *
	 * If json_encode() exists it's done by that, otherwise an external class provided with the Shopgate Library is used.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function jsonEncode($value) {
		// if json_encode exists use that
		if (function_exists("json_encode")) {
			return $string = json_encode($value);
		}

		// if not check if external class is loaded
		if (!class_exists("Services_JSON")) {
			require_once dirname(__FILE__).'/../vendors/JSON.php';
		}

		// encode via external class
		$jsonService = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $jsonClass->encode($value);
	}

	/**
	 * Creates a variable, array or object from any passed JSON string.
	 *
	 * If json_encode() exists it's done by that, otherwise an external class provided with the Shopgate Library is used.
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function jsonDecode($json, $assoc = false) {
		// if json_decode exists use that
		if (function_exists("json_decode")) {
			return json_decode($json, $assoc);
		}

		// if not check if external class is loaded
		if (!class_exists("Services_JSON")) {
			require_once dirname(__FILE__).'/../vendors/JSON.php';
		}

		// decode via external class
		$jsonService = new Services_JSON(($assoc) ? SERVICES_JSON_LOOSE_TYPE : SERVICES_JSON_IN_OBJ);
		return $jsonService->decode($json);
	}

	/**
	 * Returns the requested number of lines of the requested log file's end.
	 *
	 * @param string $type The log file to be read
	 * @param int $lines Number of lines to return
	 * @return string The requested log file content
	 *
	 * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
	 */
	protected function tail($type = self::LOGTYPE_ERROR, $lines = 20) {
		if (!isset(self::$fileHandles[$type])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE, 'Type: '.$type);
		}

		if (empty($lines)) $lines = 20;

		$handle = self::$fileHandles[$type];
		$lineCounter = $lines;
		$pos = -2;
		$beginning = false;
		$text = array();

		while ($lineCounter > 0) {
			$t = '';
			while ($t !== "\n") {
				if (fseek($handle, $pos, SEEK_END) == -1) {
					$beginning = true;
					break;
				}
				$t = fgetc($handle);
				$pos--;
			}

			$lineCounter--;
			if ($beginning) rewind($handle);
			$text[] = fgets($handle);
			if ($beginning) break;
		}

		return implode('', array_reverse($text));
	}
}

/**
 * This class provides basic functionality for the Shopgate Library's container objects.
 *
 * It provides initialization with an array, conversion to an array, utf-8 decoding of the container's properties etc.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgateContainer extends ShopgateObject {
	/**
	 * Initializes the object with the passed data.
	 *
	 * If no data is passed, an empty object is created. The passed data must be an array, it's indices must be the un-camelized,
	 * underscored names of the set* methods of the created object.
	 *
	 * @param array $data The data the container should be initialized with.
	 */
	protected final function initLibrary($data = array()) {
		if (is_array($data)) {
			$methods = get_class_methods($this);
			foreach ($data as $key => $value) {
				$setter = 'set'.$this->camelize($key, true);
				if (!in_array($setter, $methods)) {
					continue;
				}
				$this->$setter($value);
			}
		}
	}

	/**
	 * Converts the Container object recursively to an associative array.
	 *
	 * @return mixed[]
	 */
	public function toArray() {
 		$visitor = new ShopgateContainerToArrayVisitor();
 		$visitor->visitContainer($this);
 		return $visitor->getArray();
	}

	/**
	 * Creates a new object of the same type with every value recursively utf-8 encoded.
	 *
	 * @return ShopgateContainer The new object with utf-8 encoded values.
	 */
	public function utf8Encode() {
		$visitor = new ShopgateUtf8Visitor(ShopgateUtf8Visitor::MODE_ENCODE);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates a new object of the same type with every value recursively utf-8 decoded.
	 *
	 * @return ShopgateContainer The new object with utf-8 decoded values.
	 */
	public function utf8Decode() {
		$visitor = new ShopgateUtf8Visitor(ShopgateUtf8Visitor::MODE_DECODE);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates an array of all properties that have getters.
	 *
	 * @return mixed[]
	 */
	public function buildProperties() {
		$properties = get_object_vars($this);
		$filteredProperties = array();

		// only properties that have getters should be extracted
		foreach ($properties as $property => $value) {
			$getter = 'get'.$this->camelize($property, true);
			$filteredProperties[$property] = $this->{$getter}();
		}

		return $filteredProperties;
	}

	/**
	 * @param ShopgateContainerVisitor $v
	 */
	public abstract function accept(ShopgateContainerVisitor $v);
}

/**
 * This class represents the Shopgate Plugin API as described in our wiki.
 *
 * It provides all available actions and calls the plugin implementation's callback methods for data retrieval if necessary. It acts
 * as singleton.
 *
 * @see http://wiki.shopgate.com/Shopgate_Plugin_API/de
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApi extends ShopgateObject {
	const OBFUSCATION_STRING = 'XXXXXXXX';
	
	/**
	 * @var ShopgatePluginApi
	 */
	private static $singleton;

	/**
	 * @var mixed[]
	 */
	protected $config;

	/**
	 * @var ShopgatePlugin
	 */
	private $plugin;

	/**
	 * Parameters passed along the action (usually per POST)
	 *
	 * @var mixed[]
	 */
	private $params;

	/**
	 * @var string[]
	 */
	private  $actionWhitelist;

	/**
	 * @var mixed[]
	 */
	private $response;

	/**
	 * @return ShopgatePluginApi
	 */
	public static function &getInstance() {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	protected final function initLibrary() {
		// initialize action whitelist
		$this->actionWhitelist = array(
			'ping',
			'add_order',
			'update_order',
			'get_customer',
			'get_items_csv',
			'get_categories_csv',
			'get_reviews_csv',
			'get_pages_csv',
			'get_log_file',
			'check_coupon',
			'redeem_coupon'
		);

		// prepare the response
		$this->response = array(
			'error' => 0,
			'error_text' => null,
			'version' => SHOPGATE_LIBRARY_VERSION,
			'trace_id' => null,
		);
	}

	/**
	 * Registers the current ShopgatePlugin instance for callbacks.
	 *
	 * This is usually done by ShopgatePlugin::initLibrary() as soon as you instantiate your plugin implementation.
	 * The registered instance is the one whose callback methods (e.g. ShopgatePlugin::addOrder()) get called on incoming
	 * requests.
	 *
	 * @param ShopgatePlugin $shopgatePlugin
	 */
	public function setPlugin(ShopgatePlugin $shopgatePlugin) {
		$this->plugin = $shopgatePlugin;
	}

	/**
	 * Initializes the plugin configuration.
	 *
	 * @param ShopgateConfig $config
	 */
	public function setConfig(ShopgateConfig $config) {
		$this->config = $config->getConfig();
	}

	/**
	 * Inspects an incoming request, performs the requested actions, prepares and prints out the response to the requesting entity.
	 *
	 * Note that the method usually returns true or false on completion, depending on the success of the operation. However, some actions such as
	 * the get_*_csv actions, might stop the script after execution to prevent invalid data being appended to the output.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return bool false if an error occured, otherwise true.
	 */
	public function handleRequest($data = array()) {
		// log incoming request
		$this->log($this->cleanParamsForLog($data), ShopgateObject::LOGTYPE_ACCESS."\n");

		// save the params
		$this->params = $data;

		// add trace id to response
		if (isset($this->params['trace_id'])) {
			$this->response['trace_id'] = $this->params['trace_id'];
		}

		try {
	 		ShopgateAuthentificationService::getInstance()->checkAuthentification();

			// load config
			// TODO: again??
			$this->config = ShopgateConfig::validateAndReturnConfig();

			// set error handler to Shopgate's handler if requested
			if (!empty($this->params["use_errorhandler"])) {
				set_error_handler('ShopgateErrorHandler');
			}

			// check if an action to call has been passed, is known and enabled
			if (empty($this->params['action'])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ACTION, 'Passed parameters: '.var_export($data, true));
			}

			if (!in_array($this->params['action'], $this->actionWhitelist)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_ACTION, "'{$this->params['action']}'");
			}

			if ($this->config['enable_'.$this->params['action']] !== true) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_DISABLED_ACTION, "'{$this->params['action']}'");
			}

			// call the action
			$action = $this->camelize($this->params['action']);
			$this->{$action}();
		} catch (ShopgateLibraryException $e) {
			$this->response['error'] = $e->getCode();
			$this->response['error_text'] = $e->getMessage();
		} catch (Exception $e) {
			// new ShopgateLibraryException to build proper error message and perform logging
			$se = new ShopgateLibraryException($e->getMessage());
			$this->response['error'] = $se->getCode();
			$this->response['error_text'] = $se->getMessage();
		}

		// print out the response
		header("HTTP/1.0 200 OK");
		header('Content-Type: application/json');
		header('Content-Encoding: utf-8');
		echo $this->jsonEncode($this->response);

		// return true or false
		return !(isset($this->response["error"]) && $this->response["error"] > 0);
	}
	
	/**
	 * Function to prepare the parameters of an API request for logging.
	 *
	 * Strips out critical request data like the password of a get_customer request.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return string The cleaned parameters as string ready to log.
	 */
	protected function cleanParamsForLog($data) {
		foreach ($data as $key => &$value) {
			switch ($key) {
				case 'pass': $value = self::OBFUSCATION_STRING;
			}
		}
		
		return print_r($data, true);
	}


	/******************************************************************
	 * Following methods represent the Shopgate Plugin API's actions: *
	 ******************************************************************/

	/**
	 * Represents the "ping" action.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_ping/de
	 */
	private function ping() {
		$this->response["pong"] = "OK";

		function getSettings() {
			$settingDetails = array();

			$allSettings = ini_get_all();

			$settings = array(
				"max_execution_time",
				"memory_limit",
				"allow_call_time_pass_reference",
				"disable_functions",
				"display_errors",
				"file_uploads",
				"include_path",
				"register_globals",
				"safe_mode"
			);

			foreach($settings as $setting) {
				$settingDetails[$setting] = $allSettings[$setting];
			}

			return $settingDetails;
		}

		function getPermissions() {
			$permissions = array();
			$files = array(
				SHOPGATE_BASE_DIR."/config/config.php",
				SHOPGATE_BASE_DIR."/config/myconfig.php",
				SHOPGATE_BASE_DIR."/temp/",
				SHOPGATE_BASE_DIR."/temp/cache/",
			);

			$files[] = ShopgateConfig::getItemsCsvFilePath();
			$files[] = ShopgateConfig::getCategoriesCsvFilePath();
			$files[] = ShopgateConfig::getReviewsCsvFilePath();

			foreach($files as $file) {
				$permission = array();
				$permission["file"] = $file;
				$permission["exist"] = (bool) file_exists($file);
				$permission["writeable"] = (bool) is_writable($file);
				$permission["permission"] = "-";

				$fInfo = pathinfo($file);
				if( file_exists($file) ) {
					$permission["permission"] = substr( sprintf('%o', fileperms($file)), -4);
				} else {
					if( file_exists( $fInfo["dirname"] ) )
						$permission["parent_permission"] = substr( sprintf('%o', fileperms( $fInfo["dirname"] )), -4);
				}

				$permissions[] = $permission;
			}



			return $permissions;
		}

		header("Content-Type: application/json");
		$this->response["configuration"] = $this->config;
		$this->response["permissions"] = getPermissions();
		$this->response["php_version"] = phpversion();
		$this->response["php_config"] = getSettings();
		$this->response["php_curl"] = function_exists("curl_version") ? curl_version() : "No PHP-CURL installed";
		$this->response["php_extensions"] = get_loaded_extensions();
		$this->response["shopgate_library_version"] = SHOPGATE_LIBRARY_VERSION;
	}

	/**
	 * Represents the "add_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_add_order/de
	 */
	private function addOrder() {
		if (!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		$orders = ShopgateMerchantApi::getInstance()->getOrders(array('order_numbers[0]'=>$this->params['order_number']));
		foreach ($orders as $order) {
			$orderId = $this->plugin->addOrder($order);
		}

		$this->response["external_order_number"] = $orderId;
	}

	/**
	 * Represents the "update_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order/de
	 */
	private function updateOrder() {
		if(!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		if (!isset($this->params['payment'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_PAYMENT);
		}

		$orders = ShopgateMerchantApi::getInstance()->getOrders(array('order_numbers[0]'=>$this->params['order_number']));
		$payment = (bool) $this->params['payment'];
		foreach ($orders as $order) {
			$orderId = $this->plugin->updateOrder($order, $payment);
		}

		$this->response["external_order_number"] = $orderId;
	}

	/**
	 * Represents the "get_customer" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_customer/de
	 */
	private function getCustomer() {
		if (!isset($this->params['user'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_USER);
		}

		if (!isset($this->params['pass'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_PASS);
		}

		$customer = $this->plugin->getCustomer($this->params['user'], $this->params['pass']);
		if (!is_object($customer) || !($customer instanceof ShopgateCustomer)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($customer, true));
		}

		$customerData = $customer->toArray();
		$addressList = $customerData['addresses'];
		unset($customerData['addresses']);

		$this->response["user_data"] = $customerData;
		$this->response["addresses"] = $addressList;
	}

	/**
	 * Represents the "get_items_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items_csv/de
	 */
	private function getItemsCsv() {
		$generate_csv = ($this->config["generate_items_csv_on_the_fly"] || isset($this->params["generate_items_csv_on_the_fly"]));

		if (isset($this->params["limit"]) && isset($this->params["offset"])) {
			$this->plugin->exportLimit = (string) $this->params["limit"];
			$this->plugin->exportOffset = (string) $this->params["offset"];
			$this->plugin->splittedExport = true;
		}

		// generate / update items csv file if requested
		if ($generate_csv) {
			$this->plugin->startGetItemsCsv();
		}

		$fileName = ShopgateConfig::getItemsCsvFilePath();
		if (!file_exists($fileName)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: '.$fileName);
		}

		$fp = @fopen($fileName, "r");
		if (!$fp) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$fileName);
		}

		// output headers ...
		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="items.csv"');

		// ... and csv file
		while ($line = fgets($fp)) echo $line;

		// clean up and leave
		fclose($fp);
		exit;
	}

	/**
	 * Represents the "get_categories_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories_csv/de
	 */
	private function getCategoriesCsv() {
		// generate / update categories csv file
		$this->plugin->startGetCategoriesCsv();

		$fileName = ShopgateConfig::getCategoriesCsvFilePath();
		if (!file_exists($fileName)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: '.$fileName);
		}

		$fp = @fopen($fileName, "r");
		if (!$fp) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$fileName);
		}

		// output headers ...
		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="categories.csv"');

		// ... and csv file
		while ($line = fgets($fp)) echo $line;

		// clean up and leave
		fclose($fp);
		exit;
	}

	/**
	 * Represents the "get_reviews_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_reviews_csv/de
	 */
	private function getReviewsCsv() {
		// generate / update reviews csv file
		$this->plugin->startGetReviewsCsv();

		$fileName = ShopgateConfig::getReviewsCsvFilePath();
		if (!file_exists($fileName)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: '.$fileName);
		}

		$fp = @fopen($fileName, "r");
		if (!$fp) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$fileName);
		}

		// output headers ...
		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="reviews.csv"');

		// ... and csv file
		while ($line = fgets($fp)) echo $line;

		// clean up and leave
		fclose($fp);
		exit;
	}

	/**
	 * Represents the "get_pages_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_pages_csv/de
	 */
	private function getPagesCsv() {
		$fileName = ShopgateConfig::getPagesCsvFilePath();
		if (!file_exists($fileName)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: '.$fileName);
		}

		$fp = @fopen($fileName, "r");
		if (!$fp) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$fileName);
		}

		// output headers ...
		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="pages.csv"');

		// ... and csv file
		while ($line = fgets($fp)) echo $line;

		// clean up and leave
		fclose($fp);
		exit;
	}

	/**
	 * Represents the "get_log_file" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_log_file/de
	 */
	private function getLogFile() {
		$type = (empty($this->params['log_type'])) ? ShopgateObject::LOGTYPE_ERROR : $this->params['log_type'];
		$lines = (!isset($this->params['lines'])) ? null : $this->params['lines'];

		$log = $this->tail($type, $lines);

		// return the requested log file content and end the script
		header("HTTP/1.0 200 OK");
		header('Content-Type: text/plain');
		echo $log;
		exit;
	}

	/**
	 * Represents the "get_orders" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_orders/de
	 * @todo
	 */
	private function getOrders() {
		/**** not yet implemented ****/

		//if (!empty($this->params['external_customer_number'])) {
	}
}

/**
 * This class represents the Shopgate Merchant API as described in our wiki.
 *
 * It provides all available actions, calls to the configured API, retrieves, parses and formats the data. It acts as singleton.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateMerchantApi extends ShopgateObject {
	/**
	 * @var ShopgateMerchantApi
	 */
	private static $singleton;

	/**
	 * @var mixed[]
	 */
	private $config;

	/**
	 * @return ShopgateMerchantApi
	 */
	public static function getInstance() {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	protected final function initLibrary() {
		$this->config = ShopgateConfig::validateAndReturnConfig();
	}

	/**
	 * Prepares the request and sends it to the configured Shopgate Merchant API.
	 *
	 * @param mixed[] $data The parameters to send.
	 * @return mixed The JSON decoded response.
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 */
	private function sendRequest($data) {
		$data['shop_number'] = $this->config["shop_number"];
		$data['trace_id'] = 'spa-'.uniqid();

		$url = $this->config["api_url"];
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "ShopgatePlugin/" . SHOPGATE_PLUGIN_VERSION);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'X-Shopgate-Library-Version: '. SHOPGATE_LIBRARY_VERSION,
			'X-Shopgate-Plugin-Version: '.SHOPGATE_PLUGIN_VERSION,
			ShopgateAuthentificationService::getInstance()->buildAuthUserHeader(),
			ShopgateAuthentificationService::getInstance()->buildAuthTokenHeader()
		));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		// check the result
		if (!$response) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_NO_CONNECTION);
		}

		$decodedResponse = $this->jsonDecode($response, true);

		if (empty($decodedResponse)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'Response: '.$response);
		}

		if ($decodedResponse['error'] != 0) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED, $decodedResponse['error_text'], true);
		}

		return $decodedResponse;
	}


	/********************************************************************
	 * Following methods represent the Shopgate Merchant API's actions: *
	 ********************************************************************/

	/**
	 * Represents the "get_orders" action.
	 *
	 * @param mixed[] $parameters
	 * @return ShopgateOrder[]
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_orders/de
	 */
	public function getOrders($parameters) {
		$data = array(
			'action' => "get_orders",
			'with_items' => 1,
		);

		$data = array_merge($data, $parameters);
		$result = $this->sendRequest($data);

		if (empty($result["orders"])) {
			throw new ShopgateLibraryException(
				ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
				'"order" not set. Response: '.var_export($result, true)
			);
		}

		$orders = array();
		foreach ($result["orders"] as $order) {
			$orders[] = new ShopgateOrder($order);
		}

		return $orders;
	}

	/**
	 * Represents the "add_order_delivery_note" action.
	 *
	 * @param string $orderNumber
	 * @param string $shippingServiceId
	 * @param int $trackingNumber
	 * @param bool $markAsCompleted
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_order_delivery_note/de
	 */
	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false) {
		$data = array(
			"action" => "add_order_delivery_note",
			"order_number" => $orderNumber,
			"shipping_service_id" => $shippingServiceId,
			"tracking_number" => (string) $trackingNumber,
			"mark_as_completed" => $markAsCompleted,
		);
	}

	/**
	 * Represents the "set_order_shipping_completed" action.
	 *
	 * @param string $orderNumber
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_set_order_shipping_completed/de
	 */
	public function setOrderShippingCompleted($orderNumber) {
		$data = array(
			'action' => 'set_order_shipping_completed',
			'order_number' => $orderNumber,
		);
	}
}

/**
 * This class acts as super class for plugin implementations and provides some basic functionality.
 *
 * A plugin implementation using the Shopgate Library must be derived from this class. The abstract methods are callback methods for
 * shop system specific operations such as retrieval of customer or order information, adding or updating orders etc.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgatePlugin extends ShopgateObject {
	private $allowedEncodings = array(
		'UTF-8', 'ASCII', 'CP1252', 'ISO-8859-15', 'UTF-16LE','ISO-8859-1'
	);

	/**
	 * @var resource
	 */
	protected $fileHandle;

	/**
	 * @var string[]
	 */
	private $buffer = array();

	/**
	 * @var int
	 */
	private $bufferCounter = 0;

	/**
	 * @var mixed[]
	 */
	protected $config;

	/**
	 * @var int
	 */
	protected $bufferLimit = 100;

	/**
	 * @var int
	 */
	public $exportLimit = 1000;

	/**
	 * @var int
	 */
	public $exportOffset = 0;

	/**
	 * @var bool
	 */
	public $splittedExport = false;

	final protected function initLibrary() {
		// Load configuration
		try {
			$this->setConfig(ShopgateConfig::validateAndReturnConfig());
		} catch (ShopgateLibraryException $e) {
			// Logging is done in exception constructor
		}

		// Set error handler if configured
		if (isset($this->config["use_custom_error_handler"]) && $this->config["use_custom_error_handler"]) {
			set_error_handler('ShopgateErrorHandler');
		}

		// Set plugin instance and fire the plugin's startup callback
		try {
			ShopgatePluginApi::getInstance()->setPlugin($this);
			$this->startup();
		} catch (ShopgateLibraryException $e) {
			// Logging is done in exception constructor
		}
	}

	/**
	 * Sets the current configuration.
	 *
	 * @param mixed[] $config
	 */
	public final function setConfig(array $config = null) {
		$this->config = $config;
	}

	/**
	 * Convenience method to call ShopgatePluginApi::handleRequest() from $this.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return bool false if an error occured, otherwise true.
	 */
	public function handleRequest($data = array()) {
		return ShopgatePluginApi::getInstance()->handleRequest($data);
	}

	/**
	 * Creates a new write buffer for the file under $filePath.
	 *
	 * @param string $filePath Path to the file (the .tmp extension is added automatically).
	 */
	private final function createBuffer($filePath) {
		$timeStart = time();
		$filePath .= ".tmp";

		$this->log('Trying to create "'.basename($filePath).'". ', 'access');

		$this->fileHandle = @fopen($filePath, 'w');
		if (!$this->fileHandle) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$filePath);
		}

		$this->buffer = array();
	}

	/**
	 * Closes the file and flushes the buffer.
	 *
	 * @param string $filePath Path to the file (the .tmp extension is added automatically).
	 */
	private final function finishBuffer($filePath) {
		$this->flushBuffer();
		fclose($this->fileHandle);

		rename($filePath.".tmp", $filePath);

		$this->log('Fertig, '.basename($filePath).' wurde erfolgreich erstellt', "access");
		$duration = time() - $timeStart;
		$this->log("Dauer: $duration Sekunden", "access");
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createItemsCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetItemsCsv() {
		$this->createBuffer(ShopgateConfig::getItemsCsvFilePath());
		$this->createItemsCsv();
		$this->finishBuffer(ShopgateConfig::getItemsCsvFilePath());
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createCategoriesCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetCategoriesCsv() {
		$this->createBuffer(ShopgateConfig::getCategoriesCsvFilePath());
		$this->createCategoriesCsv();
		$this->finishBuffer(ShopgateConfig::getCategoriesCsvFilePath());
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createReviewsCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetReviewsCsv() {
		$this->createBuffer(ShopgateConfig::getReviewsCsvFilePath());
		$this->createReviewsCsv();
		$this->finishBuffer(ShopgateConfig::getReviewsCsvFilePath());
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createPagesCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetPagesCsv() {
		$this->createBuffer(ShopgateConfig::getPagesCsvFilePath());
		$this->createPagesCsv();
		$this->finishBuffer(ShopgateConfig::getReviewsCsvFilePath());
	}

	/**
	 * Adds a line to the csv file buffer.
	 *
	 * @param mixed[] $itemArr
	 */
	protected final function addItem($itemArr) {
		$this->buffer[] = $itemArr;
		$this->bufferCounter++;

		if (
			$this->bufferCounter > $this->bufferLimit ||
			isset($this->config["flush_buffer_size"]) &&
			$this->config["flush_buffer_size"] <= $this->bufferCounter
		) {
			$this->flushBuffer();
		}
	}

	/**
	 * Flushes buffer to the currently opened file handle in $this->fileHandle.
	 *
	 * The data is converted to utf-8 if mb_convert_encoding() exists
	 */
	private final function flushBuffer() {
		if (empty($this->buffer) && ftell($this->fileHandle) == 0) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_EMPTY_BUFFER);
		}

		// write headline if it's the beginning of the file
		if (ftell($this->fileHandle) == 0) {
			fputcsv($this->fileHandle, array_keys($this->buffer[0]), ';', '"');
		}

		foreach ($this->buffer as $item) {
			if (function_exists("mb_convert_encoding")) {
				foreach ($item as &$field) {
					$field = mb_convert_encoding($field, "UTF-8", $this->allowedEncodings);
				}
			}

			fputcsv($this->fileHandle, $item, ";", "\"");
		}

		$this->buffer = array();
		$this->bufferCounter = 0;
	}

	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Categories/de
	 */
	protected function buildDefaultCategoryRow() {
		$row = array(
			"category_number" => "",
			"parent_id" => "",
			"category_name" => "",
			"url_image" => "",
			"order_index" => "",
			"is_active" => 1,
			"url_deeplink" => ""
		);

		return $row;
	}

	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Items/de
	 */
	protected function buildDefaultProductRow() {
		$row = array(
			/* responsible fields */
			'item_number' 				=> "",
			'item_name' 				=> "",
			'unit_amount' 				=> "",
			'currency' 					=> "EUR",
			'tax_percent' 				=> "",
			'description' 				=> "",
			'urls_images' 				=> "",
			'categories' 				=> "",
			'category_numbers'			=> "",
			'is_available' 				=> "1",
			'available_text' 			=> "",
			'manufacturer' 				=> "",
			'manufacturer_item_number' 	=> "",
			'url_deeplink' 				=> "",
			/* additional fields */
			'properties'				=> "",
			'msrp' 						=> "",
			'shipping_costs_per_order' 	=> "0",
			'additional_shipping_costs_per_unit' => "0",
			'is_free_shipping'			=> "0",
			'basic_price' 				=> "",
			'use_stock' 				=> "0",
			'stock_quantity' 			=> "",
			'ean' 						=> "",
			'pzn'						=> "",
			'last_update' 				=> "",
			'tags' 						=> "",
			'sort_order' 				=> "",
			'is_highlight'				=> "0",
			'highlight_order_index'		=> "0",
			'marketplace' 				=> "1",
			'internal_order_info' 		=> "",
			'related_shop_item_numbers' => "",
			'age_rating' 				=> "",
			'weight' 					=> "",
			'block_pricing' 			=> "",
			/* parent/child relationship */
			'has_children' 				=> "0",
			'parent_item_number' 		=> "",
			'attribute_1' 				=> "",
			'attribute_2' 				=> "",
			'attribute_3' 				=> "",
			'attribute_4' 				=> "",
			'attribute_5' 				=> "",
			'attribute_6' 				=> "",
			'attribute_7' 				=> "",
			'attribute_8' 				=> "",
			'attribute_9' 				=> "",
			'attribute_10' 				=> "",
			/* options */
			'has_options' 				=> "0",
			'option_1' 					=> "",
			'option_1_values' 			=> "",
			'option_2' 					=> "",
			'option_2_values' 			=> "",
			'option_3' 					=> "",
			'option_3_values' 			=> "",
			'option_4' 					=> "",
			'option_4_values' 			=> "",
			'option_5' 					=> "",
			'option_5_values' 			=> "",
			'option_6' 					=> "",
			'option_6_values' 			=> "",
			'option_7' 					=> "",
			'option_7_values' 			=> "",
			'option_8' 					=> "",
			'option_8_values' 			=> "",
			'option_9' 					=> "",
			'option_9_values' 			=> "",
			'option_10' 				=> "",
			'option_10_values' 			=> "",
			/* inputfields */
			'has_input_fields' 			=> "0",
			'input_field_1_type'		=> "",
			'input_field_1_label'		=> "",
			'input_field_1_infotext'	=> "",
			'input_field_1_required'	=> "",
			'input_field_1_add_amount'	=> "",
			'input_field_2_type'		=> "",
			'input_field_2_label'		=> "",
			'input_field_2_infotext'	=> "",
			'input_field_2_required'	=> "",
			'input_field_2_add_amount'	=> "",
			'input_field_3_type'		=> "",
			'input_field_3_label'		=> "",
			'input_field_3_infotext'	=> "",
			'input_field_3_required'	=> "",
			'input_field_3_add_amount'	=> "",
			'input_field_4_type'		=> "",
			'input_field_4_label'		=> "",
			'input_field_4_infotext'	=> "",
			'input_field_4_required'	=> "",
			'input_field_4_add_amount'	=> "",
			'input_field_5_type'		=> "",
			'input_field_5_label'		=> "",
			'input_field_5_infotext'	=> "",
			'input_field_5_required'	=> "",
			'input_field_5_add_amount'	=> "",
			'input_field_6_type'		=> "",
			'input_field_6_label'		=> "",
			'input_field_6_infotext'	=> "",
			'input_field_6_required'	=> "",
			'input_field_6_add_amount'	=> "",
			'input_field_7_type'		=> "",
			'input_field_7_label'		=> "",
			'input_field_7_infotext'	=> "",
			'input_field_7_required'	=> "",
			'input_field_7_add_amount'	=> "",
			'input_field_8_type'		=> "",
			'input_field_8_label'		=> "",
			'input_field_8_infotext'	=> "",
			'input_field_8_required'	=> "",
			'input_field_8_add_amount'	=> "",
			'input_field_9_type'		=> "",
			'input_field_9_label'		=> "",
			'input_field_9_infotext'	=> "",
			'input_field_9_required'	=> "",
			'input_field_9_add_amount'	=> "",
			'input_field_10_type'		=> "",
			'input_field_10_label'		=> "",
			'input_field_10_infotext'	=> "",
			'input_field_10_required'	=> "",
			'input_field_10_add_amount'	=> "",
		);

		return $row;
	}

	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Reviews/de
	 */
	protected function buildDefaultReviewsRow() {
		$row = array(
			"item_number" => '',
			"update_review_id" => '',
			"score" => '',
			"name" => '',
			"date" => '',
			"title" => '',
			"text" => '',
		);
		
		return $row;
	}
	
	/**
	 * Rounds and formats a price.
	 *
	 * @param float $price The price of an item.
	 * @param int $digits The number of digits after the decimal separator.
	 * @param string $decimalPoint The decimal separator.
	 * @param string $thousandPoints The thousands separator.
	 */
	protected function formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "") {
		$price = round($price, $digits);
		$price = number_format($price, $digits, $decimalPoint, $thousandPoints);
		return $price;
	}

	/**
	 * Removes all disallowed HTML tags from a given string.
	 *
	 * By default the following are allowed:
	 *
	 * "ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE", "BODY", "BR",
	 * "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
	 * "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML",
	 * "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
	 * "IMG", "INPUT", "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
	 * "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
	 *
	 *
	 * @param string $string The input string to be filtered.
	 * @param string[] $removeTags The tags to be removed.
	 * @param string[] $additionalAllowedTags Additional tags to be allowed.
	 *
	 * @return string The sanititzed string.
	 */
	protected function removeTagsFromString($string, $removeTags = array(), $additionalAllowedTags = array()) {
		// all tags available
		$allowedTags = array("ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE",
			"BODY", "BR", "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
			"EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML", "IMG", "INPUT",
			"ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
			"P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
			"TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
		);

		// make them all lowercase
		foreach ($allowedTags as &$t) $t = strtolower($t);
		foreach ($removeTags as &$t) $t = strtolower($t);
		foreach ($additionalAllowedTags as &$t) $t = strtolower($t);

		// add the additional allowed tags to the list
		$allowedTags = array_merge($allowedTags, $additionalAllowedTags);

		// strip the disallowed tags from the list
		$allowedTags = array_diff($allowedTags, $removeTags);

		// add HTML brackets
		foreach ($allowedTags as &$t) $t = "<$t>";

		// let PHP sanitize the string and return it
		return strip_tags($string, implode(",", $allowedTags));
	}

	protected $exchangeRate = 1;


	/*******************************************************************************
	 * Following methods are the callbacks that need to be implemented by plugins. *
	 *******************************************************************************/

	/**
	 * Callback function for initialization by plugin implementations.
	 *
	 * This method gets called on instantiation of a ShopgatePlugin child class and serves as __construct() replacement.
	 */
	public abstract function startup();

	/**
	 * This performs the necessary queries to build a ShopgateCustomer object for the given log in credentials.
	 *
	 * The method should not abort on soft errors like when the street or phone number of a customer can't be found.
	 *
	 * @param string $user The user name the customer entered at Shopgate Connect.
	 * @param string $pass The password the customer entered at Shopgate Connect.
	 * @return ShopgateCustomer A ShopgateCustomer object.
	 * @throws ShopgateLibraryException on invalid log in data or hard errors like database failure.
	 */
	public abstract function getCustomer($user, $pass);

	/**
	 * Performs the necessary queries to add an order to the shop system's database.
	 *
	 * @param ShopgateOrder $order The ShopgateOrder object to be added to the shop system's database.
	 * @return int The ID of the added order in your shop system's database.
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function addOrder(ShopgateOrder $order);

	/**
	 * Performs the necessary queries to update an order in the shop system's database.
	 *
	 * @param ShopgateOrder $order The ShopgateOrder object to be update in the shop system's database.
	 * @param bool $payment True if the payment status of an order should be updated, false otherwise.
	 * @return int The ID of the added order in your shop system's database.
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function updateOrder(ShopgateOrder $order, $payment);

	/**
	 * Loads the products of the shop system's database and passes them to the buffer.
	 *
	 * User ShopgatePlugin::buildDefaultProductRow() to get the correct indices for the field names in a Shopgate items csv and
	 * use ShopgatePlugin::addItem() to add it to the output buffer.
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createItemsCsv();

	/**
	 * Loads the product categories of the shop system's database and passes them to the buffer.
	 *
	 * User ShopgatePlugin::buildDefaultProductRow() to get the correct indices for the field names in a Shopgate categories csv and
	 * use ShopgatePlugin::addItem() to add it to the output buffer.
	 *
	 * @throws ShopgateLibraryException
	 */	protected abstract function createCategoriesCsv();

	/**
	 * Loads the product reviews of the shop system's database and passes them to the buffer.
	 *
	 * User ShopgatePlugin::buildDefaultCategoriesRow() to get the correct indices for the field names in a Shopgate reviews csv and
	 * use ShopgatePlugin::addItem() to add it to the output buffer.
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createReviewsCsv();

	/**
	 * Loads the product pages of the shop system's database and passes them to the buffer.
	 *
	 * @throws ShopgateLibraryException
	 */
	//protected abstract function getPagesCsv();
}

/**
 * This class provides methods to check and generate authentification strings.
 *
 * It acts as Singleton and is usually only used internally by the Shopgate Library to send requests or check incoming
 * requests.
 *
 * To check authentication on incoming request it accesses the $_SERVER variable which should contain the required X header fields for
 * authentication.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateAuthentificationService extends ShopgateObject {
	private static $singleton;

	const HEADER_X_SHOPGATE_AUTH_USER  = 'X-Shopgate-Auth-User';
	const HEADER_X_SHOPGATE_AUTH_TOKEN = 'X-Shopgate-Auth-Token';
	const PHP_X_SHOPGATE_AUTH_USER  = 'HTTP_X_SHOPGATE_AUTH_USER';
	const PHP_X_SHOPGATE_AUTH_TOKEN = 'HTTP_X_SHOPGATE_AUTH_TOKEN';

	private $customerNumber;
	private $apiKey;
	private $timestamp;

	protected final function initLibrary() {
		$config = ShopgateConfig::getConfig();
		$this->customerNumber = $config["customer_number"];
		$this->apiKey = $config["apikey"];
		$this->timestamp = time();
	}

	/**
	 * @return ShopgateAuthentificationService
	 */
	public static function getInstance() {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	/**
	 * @return string The X-Shopgate-Auth-User HTTP header for an outgoing request.
	 */
	public function buildAuthUserHeader() {
		return self::HEADER_X_SHOPGATE_AUTH_USER .': '. $this->customerNumber.'-'.$this->timestamp;
	}

	/**
	 * @return string The X-Shopgate-Auth-Token HTTP header for an outgoing request.
	 */
	public function buildAuthTokenHeader() {
		return self::HEADER_X_SHOPGATE_AUTH_TOKEN.': '.sha1("SMA-{$this->customerNumber}-{$this->timestamp}-{$this->apiKey}");
	}

	/**
	 * @throws ShopgateLibraryException if authentication fails
	 */
	public function checkAuthentification() {
		if (empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_USER]) || empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN])){
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTIFICATION_FAILED, 'No authentication data present.');
		}

		// for convenience
		$name = $_SERVER[self::PHP_X_SHOPGATE_AUTH_USER];
		$token = $_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN];

	    // extract customer number and timestamp from username
		$matches = array();
	 	if (!preg_match('/(?<customer_number>[1-9][0-9]+)-(?<timestamp>[1-9][0-9]+)/', $name, $matches)){
	 		throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTIFICATION_FAILED, 'Cannot parse: '.$name.'.');
   		}

   		// for convenience
   		$customer_number = $matches["customer_number"];
   		$timestamp = $matches["timestamp"];

   		// request shouldn't be older than 30 minutes
   		if ((time() - $timestamp) >= (30*60)) {
   			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTIFICATION_FAILED, 'Request too old.');
   		}

   		// create the authentification-password
		$generatedPassword = sha1("SPA-{$customer_number}-{$timestamp}-{$this->apiKey}");

		// compare customer-number and auth-password
		if (($customer_number != $this->customerNumber) || ($token != $generatedPassword)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTIFICATION_FAILED, 'Invalid authentication data.');
		}
	}
}

/**
 * Interface for visitors of ShopgateContainer objects.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateContainerVisitor {
	public function visitContainer(ShopgateContainer $c);
	public function visitCustomer(ShopgateCustomer $c);
	public function visitAddress(ShopgateAddress $a);
	public function visitOrder(ShopgateOrder $o);
	public function visitOrderItem(ShopgateOrderItem $i);
	public function visitOrderItemOption(ShopgateOrderItemOption $o);
	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d);
}

/**
 * Creates a new object with every value inside utf-8 de- / encoded.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateUtf8Visitor implements ShopgateContainerVisitor {
	const MODE_ENCODE = 1;
	const MODE_DECODE = 2;

	protected $object;
	protected $mode;

	/**
	 * @param int $mode Set mode to one of the two class constants. Default is MODE_DECODE.
	 */
	public function __construct($mode = self::MODE_DECODE) {
		switch ($mode) {
			// default mode
			default: $mode = self::MODE_DECODE;

			// allowed modes
			case self::MODE_ENCODE: case self::MODE_DECODE:
				$this->mode = $mode;
			break;
		}
	}

	public function getObject() {
		return $this->object;
	}

	public function visitContainer(ShopgateContainer $c) {
		$c->accept($this);
	}

	public function visitCustomer(ShopgateCustomer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['addresses'] = $this->iterateObjectList($properties['addresses']);

		// create new object with utf-8 decoded data
		try {
			$this->object = new ShopgateCustomer($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitAddress(ShopgateAddress $a) {
		// create new object with utf-8 decoded data
		$properties = $a->buildProperties();
		$this->iterateSimpleProperties($properties);

		try {
			$this->object = new ShopgateAddress($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrder(ShopgateOrder $o) {
		// get properties
		$properties = $o->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate the order's payment_infos
		if (!empty($properties['payment_infos']) && is_array($properties['payment_infos'])) {
			$this->iterateSimpleProperties($properties['payment_infos']);
		}

		// visit delivery_address
		if (!empty($properties['delivery_address']) && ($properties['delivery_address'] instanceof ShopgateAddress)) {
			$properties['delivery_address']->accept($this);
			$properties['delivery_address'] = $this->object;
		}

		// visit invoice_address
		if (!empty($properties['invoice_address']) && ($properties['invoice_address'] instanceof ShopgateAddress)) {
			$properties['invoice_address']->accept($this);
			$properties['invoice_address'] = $this->object;
		}

		// iterate lists of referred objects
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);

		// create new object with utf-8 decoded data
		try {
			$this->object = new ShopgateOrder($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItem(ShopgateOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate lists of referred objects
		$properties['options'] = $this->iterateObjectList($properties['options']);
		// TODO: $properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// create new object with utf-8 decoded data
		try {
			$this->object = new ShopgateOrderItem($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItemOption(ShopgateOrderItemOption $o) {
			// create new object with utf-8 decoded data
		$properties = $o->buildProperties();
		$this->iterateSimpleProperties($properties);

		try {
			$this->object = new ShopgateOrderItemOption($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d) {
			// create new object with utf-8 decoded data
		$properties = $d->buildProperties();
		$this->iterateSimpleProperties($properties);

		try {
			$this->object = new ShopgateDeliveryNote($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	protected function iterateSimpleProperties(array &$properties) {
		foreach ($properties as $key => &$value) {
			// we only want the simple types
			if (is_object($value) || is_array($value)) continue;

			switch ($this->mode) {
				case self::MODE_ENCODE: $value = utf8_encode($value); break;
				case self::MODE_DECODE: $value = utf8_decode($value); break;
			}
		}
	}

	protected function iterateObjectList($list = null) {
		$newList = array();
		
		if (!empty($list) && is_array($list)) {
			foreach ($list as $object) {
				if (!($object instanceof ShopgateContainer)) {
					ShopgateObject::logWrite('Encountered unknown type in what is supposed to be a list of ShopgateContainer objects: '.var_export($object, true));
					continue;
				}

				$object->accept($this);
				$newList[] = $this->object;
			}
		}
		
		return $newList;
	}
}

/**
 * Turns a ShopgateContainer or an array of ShopgateContainers into an array.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateContainerToArrayVisitor implements ShopgateContainerVisitor {
	protected $array;

	public function getArray() {
		return $this->array;
	}

	public function visitContainer(ShopgateContainer $c) {
		$c->accept($this);
	}

	public function visitCustomer(ShopgateCustomer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['addresses'] = $this->iterateObjectList($properties['addresses']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitAddress(ShopgateAddress $a) {
		// get properties and iterate (no complex types in ShopgateAddress objects)
		$this->array = $this->iterateSimpleProperties($a->buildProperties());
	}

	public function visitOrder(ShopgateOrder $o) {
		// get properties
		$properties = $o->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate the order's payment_infos
		if (!empty($properties['payment_infos']) && is_array($properties['payment_infos'])) {
			$properties['payment_infos'] = $this->iterateSimpleProperties($properties['payment_infos']);
		}

		// visit invoice address
		if (!empty($properties['invoice_address']) && ($properties['invoice_address'] instanceof ShopgateAddress)) {
			$properties['invoice_address']->accept($this);
			$properties['invoice_address'] = $this->array;
		}

		// visit delivery address
		if (!empty($properties['delivery_address']) && ($properties['delivery_address'] instanceof ShopgateAddress)) {
			$properties['delivery_address']->accept($this);
			$properties['delivery_address'] = $this->array;
		}

		// visit the items and delivery notes arrays
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitOrderItem(ShopgateOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['options'] = $this->iterateObjectList($properties['options']);
		// TODO: $properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitOrderItemOption(ShopgateOrderItemOption $o) {
		// get properties and iterate (no complex types in ShopgateOrderItemOption objects)
		$this->array = $this->iterateSimpleProperties($o->buildProperties());
	}

	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d) {
		// get properties and iterate (no complex types in ShopgateDeliveryNote objects)
		$this->array = $this->iterateSimpleProperties($d->buildProperties());
	}


	protected function iterateSimpleProperties(array $properties) {
		foreach ($properties as $key => &$value) {
			// we only want the simple types
			if (is_object($value) || is_array($value)) continue;

			$value = $this->sanitizeSimpleVar($value);
		}

		return $properties;
	}

	protected function iterateObjectList($list = null) {
		$newList = array();
		
		if (!empty($list) && is_array($list)) {
			foreach ($list as $object) {
				if (!($object instanceof ShopgateContainer)) {
					ShopgateObject::logWrite('Encountered unknown type in what is supposed to be a list of ShopgateContainer objects: '.var_export($object, true));
					continue;
				}

				$object->accept($this);
				$newList[] = $this->array;
			}
		}

		return $newList;
	}

	protected function sanitizeSimpleVar($v) {
		if (is_int($v)) {
			return (int) $v;
		} elseif (is_bool($v)) {
			return (int) $v;
		} elseif (is_string($v)) {
			return $v;
		}
	}
}