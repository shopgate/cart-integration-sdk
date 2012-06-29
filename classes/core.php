<?php

###################################################################################
# define constants
###################################################################################
define('SHOPGATE_LIBRARY_VERSION', '2.0.15/new_config');
	
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


class ShopgateLibraryFactory {
	const PLUGIN = 'plugin';
	const CONFIG = 'config';
	
	const PLUGIN_API = 'plugin_api';
	const MERCHANT_API = 'merchant_api';
	const AUTH_SERVICE = 'auth_service';
	
	const REDIRECT = 'redirect';
	
	private $classes = array(
		self::PLUGIN => array('name' => 'ShopgatePlugin', 'is_a' => 'ShopgatePlugin', 'instance' => null),
		self::CONFIG => array('name' => 'ShopgateConfig', 'is_a' => 'ShopgateConfigInterface', 'instance' => null),
		
		self::PLUGIN_API   => array('name' => 'ShopgatePluginApi', 'is_a' => 'ShopgatePluginApiInterface', 'instance' => null),
		self::MERCHANT_API => array('name' => 'ShopgateMerchantApi', 'is_a' => 'ShopgateMerchantApiInterface', 'instance' => null),
		self::AUTH_SERVICE => array('name' => 'ShopgateAuthentificationService', 'is_a' => 'ShopgateAuthentificationServiceInterface', 'instance' => null),
		
		self::REDIRECT => array('name' => 'ShopgateMobileRedirect', 'is_a' => 'ShopgateMobileRedirectInterface', 'instance' => null),
	);
	
	private function __construct() {}
	public function __clone() { trigger_error('ShopgateLibraryFactory is a singleton and cannot be cloned.', E_USER_ERROR); }
	private function __destruct() {}
	
	private static $singleton;
	
	public static function &getInstance() {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}
		
		return self::$singleton;
	}
	
	/**
	 * @param string $className The name of the class to be used.
	 * @throws ShopgateLibraryException if the class does not implement ShopgateConfigInterface.
	 */
	public function setConfig($className, &$instance) {
		$this->setClass(self::CONFIG, $className);
		
		$this->classes[self::CONFIG]['instance'] = &$instance;
	}
	
	/**
	 * @param string $className The name of the class to be used.
	 * @param ShopgatePlugin An instance of a ShopgatePlugin subclass.
	 * @throws ShopgateLibraryException if the class is not a subclass of ShopgatePlugin.
	 */
	public function setPlugin($className, &$instance) {
		$this->setClass(self::PLUGIN, $className);
		
		$this->classes[self::PLUGIN]['instance'] = &$instance;
	}
	
	/**
	 * @param string $className The name of the class to be used.
	 * @throws ShopgateLibraryException if the class does not implement ShopgatePluginApiInterface.
	 */
	public function setPluginApi($className) {
		$this->setClass(self::PLUGIN_API, $className);
	}
	
	/**
	 * @param string $className The name of the class to be used.
	 * @throws ShopgateLibraryException if the class does not implement ShopgateMerchantApiInterface.
	 */
	public function setMerchantApi($className) {
		$this->setClass(self::MERCHANT_API, $className);
	}
	
	/**
	 * @param string $className The name of the class to be used.
	 * @throws ShopgateLibraryException if the class does not implement ShopgateAuthentificationServiceInterface.
	 */
	public function setAuthService($className) {
		$this->setClass(self::AUTH_SERVICE, $className);
	}
	
	/**
	 * @param string $className The name of the class to be used.
	 * @throws ShopgateLibraryException if the class does not implement ShopgateMobileRedirectInterface.
	 */
	public function setRedirect($className) {
		$this->setClass(self::REDIRECT, $className);
	}
	
	/**
	 * @return ShopgateConfigInterface
	 */
	public function &getConfig() {
		return $this->getClass(self::CONFIG);
	}
	
	/**
	 * @return ShopgatePluginInterface An instance of the class, that has been registered as plugin.
	 */
	public function &getPlugin() {
		return $this->getClass(self::PLUGIN);
	}
	
	/**
	 * @return ShopgatePluginApiInterface
	 */
	public function &getPluginApi() {
		return $this->getClass(self::PLUGIN_API);
	}
	
	/**
	 * @return ShopgateMerchantApiInterface
	 */
	public function &getMerchantApi() {
		return $this->getClass(self::MERCHANT_API);
	}
	
	/**
	 * @return ShopgateAuthentificationServiceInterface
	 */
	public function &getAuthService() {
		return $this->getClass(self::AUTH_SERVICE);
	}
	
	/**
	 * @return ShopgateMobileRedirectInterface
	 */
	public function &getRedirect() {
		return $this->getClass(self::REDIRECT);
	}
	
	private function setClass($classType, $className) {
		if (empty($this->classes[$classType])) {
			trigger_error('Error setting class: unknown class type "'.$classType.'".', E_USER_ERROR);
		}
		
		if (!class_exists($className)) {
			trigger_error('Error setting class: "'.$this->classes[$classType]['name'].'" not found.', E_USER_ERROR);
		}
		
		if (!$this->getIsA($className, $this->classes[$classType]['name']['is_a'])) {
			trigger_error('Error setting class: "'.$this->classes[$classType]['is_a'].'" is not a '.$this->classes[$classType]['is_a'].'.', E_USER_ERROR);
		}
		
		$this->classes[$classType]['name'] = $className;
		if (isset($this->classes[$classType]['instance'])) {
			$this->classes[$classType]['instance']->__destruct();
			$this->classes[$classType]['instance'] = null;
		}
	}
	
	private function &getClass($classType) {
		if (empty($this->classes[$classType]) || empty($this->classes[$classType]['name'])) {
			trigger_error('Error instantiating class: unknown class type "'.$classType.'".', E_USER_ERROR);
		}
		
		if (!class_exists($this->classes[$classType]['name'])) {
			trigger_error('Error instantiating class: "'.$this->classes[$classType]['name'].'" not found.', E_USER_ERROR);
		}
		
		if (empty($this->classes[$classType]['instance'])) {
			$instance = new ${$this->classes[$classType]['name']}();
			$this->classes[$classType]['instance'] = &$instance;
		} else {
			$instance = &$this->classes[$classType]['instance'];
		}
		
		return $instance;
	}
	
	private function getIsA($className, $parentOrInterfaceName) {
		$reflection = new ReflectionClass($className);
		return ($reflection->isSubclassOf($parentOrInterfaceName) || $reflection->implementsInterface($parentOrInterfaceName));
	}
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
	const PLUGIN_API_UNKNOWN_LOGTYPE = 38;

	// Plugin errors
	const PLUGIN_DUPLICATE_ORDER = 60;
	const PLUGIN_ORDER_NOT_FOUND = 61;
	const PLUGIN_NO_CUSTOMER_GROUP_FOUND = 62;
	const PLUGIN_ORDER_ITEM_NOT_FOUND = 63;
	const PLUGIN_ORDER_STATUS_IS_SENT = 64;
	const PLUGIN_ORDER_ALREADY_UP_TO_DATE = 65;

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
	const AUTHENTICATION_FAILED = 120;

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
		self::PLUGIN_API_UNKNOWN_LOGTYPE => 'unknown logtype',

		// Plugin errors
		self::PLUGIN_DUPLICATE_ORDER => 'duplicate order',
		self::PLUGIN_ORDER_NOT_FOUND => 'order not found',
		self::PLUGIN_NO_CUSTOMER_GROUP_FOUND => 'no customer group found for customer',
		self::PLUGIN_ORDER_ITEM_NOT_FOUND => 'order item not found',
		self::PLUGIN_ORDER_STATUS_IS_SENT => 'order status is "sent"',
		self::PLUGIN_ORDER_ALREADY_UP_TO_DATE => 'order is already up to date',

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
		self::AUTHENTICATION_FAILED => 'authentication failed',
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
	public function __construct($code, $additionalInformation = null, $appendAdditionalInformationToMessage = false) {
		// Set code and message
		$logMessage = self::buildLogMessageFor($code, $additionalInformation);
		if (isset(self::$errorMessages[$code])) {
			$message = self::$errorMessages[$code];
		} else {
			$message = 'Unknown error code: "'.$code.'"';
			$code = self::UNKNOWN_ERROR_CODE;
		}

		if($appendAdditionalInformationToMessage){
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
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateMerchantApiException extends Exception {
	public function __construct($code, $additionalInformation = null) {
		$message = $additionalInformation;

		if(ShopgateObject::logWrite($code.' - '.$additionalInformation) === false){
			$message .= ' (unable to log)';
		}
		parent::__construct($message, $code);
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
	const LOGTYPE_REQUEST = 'request';
	const LOGTYPE_ERROR = 'error';

	const OBFUSCATION_STRING = 'XXXXXXXX';

	/**
	 * @var resource[]
	 */
	private static $fileHandles = array(
		self::LOGTYPE_ACCESS => null,
		self::LOGTYPE_ERROR => null,
		self::LOGTYPE_REQUEST => null,
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
			case self::LOGTYPE_REQUEST:
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
	protected function initLibrary($data = array()) {
		$this->loadArray($data);
	}
	
	/**
	 * Tries to map an associative array to the object's attributes.
	 *
	 * The passed data must be an array, it's indices must be the un-camelized,
	 * underscored names of the set* methods of the object.
	 *
	 * Tha data that couldn't be mapped is returned as an array.
	 *
	 * @param array $data The data that should be mapped to the container object.
	 * @return array The part of the array that couldn't be mapped.
	 */
	protected function loadArray($data = array()) {
		$unmappedData = array();
		
		if (is_array($data)) {
			$methods = get_class_methods($this);
			foreach ($data as $key => $value) {
				$setter = 'set'.$this->camelize($key, true);
				if (!in_array($setter, $methods)) {
					$unmappedData[$key] = $value;
					continue;
				}
				$this->$setter($value);
			}
		}
		
		return $unmappedData;
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
	 * @param String $sourceEncoding The source Encoding of the strings
	 *
	 * @return ShopgateContainer The new object with utf-8 encoded values.
	 */
	public function utf8Encode($sourceEncoding = 'ISO-8859-15') {
		$visitor = new ShopgateUtf8Visitor(ShopgateUtf8Visitor::MODE_ENCODE, $sourceEncoding);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates a new object of the same type with every value recursively utf-8 decoded.
	 *
	 * @param String $destinationEncoding The destination Encoding for the strings
	 *
	 * @return ShopgateContainer The new object with utf-8 decoded values.
	 */
	public function utf8Decode($destinationEncoding = 'ISO-8859-15') {
		$visitor = new ShopgateUtf8Visitor(ShopgateUtf8Visitor::MODE_DECODE, $destinationEncoding);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates an array of all properties that have getters.
	 *
	 * @return mixed[]
	 */
	public function buildProperties() {
		$methods = get_class_methods($this);
		$properties = get_object_vars($this);
		$filteredProperties = array();

		// only properties that have getters should be extracted
		foreach ($properties as $property => $value) {
			$getter = 'get'.$this->camelize($property, true);
			if (in_array($getter, $methods)) {
				$filteredProperties[$property] = $this->{$getter}();
			}
		}

		return $filteredProperties;
	}

	/**
	 * @param ShopgateContainerVisitor $v
	 */
	public abstract function accept(ShopgateContainerVisitor $v);
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
	 * @var int (timestamp) starting time of export
	 */
	protected $timeStart;

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
		// TODO
		// startup laden
		// prüfen, ob config gesetzt ist
		// => wenn nicht: config laden
		// => ansonsten:  prüfen, ob ShopgateLibraryFactory die config kennt, ggf. nachtragen
		
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
		$this->timeStart = time();
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
		$duration = time() - $this->timeStart;
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
			'old_unit_amount'			=> "",
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
	public abstract function updateOrder(ShopgateOrder $order);

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
	 * User ShopgatePlugin::buildDefaultCategoryRow() to get the correct indices for the field names in a Shopgate categories csv and
	 * use ShopgatePlugin::addItem() to add it to the output buffer.
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createCategoriesCsv();

	/**
	 * Loads the product reviews of the shop system's database and passes them to the buffer.
	 *
	 * User ShopgatePlugin::buildDefaultReviewsRow() to get the correct indices for the field names in a Shopgate reviews csv and
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
	public function visitShopgateCategory(ShopgateCategory $d);
	public function visitShopgateItem(ShopgateItem $i);
	public function visitShopgateItemOption(ShopgateItemOption $i);
	public function visitShopgateItemOptionValue(ShopgateItemOptionValue $i);
	public function visitShopgateItemInput(ShopgateItemInput $i);
	public function visitConfig(ShopgateConfig $c);
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
	protected $encoding;

	/**
	 * @param int $mode Set mode to one of the two class constants. Default is MODE_DECODE.
	 * @param string $encoding The source or destination encoding according to PHP's mb_convert_encoding().
	 * @see http://www.php.net/manual/en/function.mb-convert-encoding.php
	 */
	public function __construct($mode = self::MODE_DECODE, $encoding = 'ISO-8859-15') {
		switch ($mode) {
			// default mode
			default: $mode = self::MODE_DECODE;

			// allowed modes
			case self::MODE_ENCODE: case self::MODE_DECODE:
				$this->mode = $mode;
			break;
		}
		$this->encoding = $encoding;
	}

	/**
	 * @return ShopgateContainer the utf-8 de- / encoded newly built object.
	 */
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

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateCustomer($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitAddress(ShopgateAddress $a) {
		$properties = $a->buildProperties();
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateAddress($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrder(ShopgateOrder $o) {
		// get properties
		$properties = $o->buildProperties();

		// iterate the simple variables and arrays with simple variables recursively
		$this->iterateSimpleProperties($properties);

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

		// create new object with utf-8 en- / decoded data
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

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderItem($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItemOption(ShopgateOrderItemOption $o) {
		$properties = $o->buildProperties();
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderItemOption($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d) {
		$properties = $d->buildProperties();
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateDeliveryNote($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitShopgateCategory(ShopgateCategory $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateCategory($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitShopgateItem(ShopgateItem $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate the item options and inputs
		$properties['options'] = $this->iterateObjectList($properties['options']);
		$properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItem($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitShopgateItemOption(ShopgateItemOption $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate the item option values
		$properties['option_values'] = $this->iterateObjectList($properties['option_values']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItemOption($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitShopgateItemOptionValue(ShopgateItemOptionValue $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItemOptionValue($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitShopgateItemInput(ShopgateItemInput $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItemInput($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitConfig(ShopgateConfig $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateConfig($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	protected function iterateSimpleProperties(array &$properties) {
		foreach ($properties as $key => &$value) {
			if (empty($value)) continue;

			// we only want the simple types
			if (is_object($value)) continue;

			// iterate through arrays recursively
			if (is_array($value)) {
				$this->iterateSimpleProperties($value);
				continue;
			}

			// perform encoding / decoding on simple types
			switch ($this->mode) {
				case self::MODE_ENCODE: $value = mb_convert_encoding($value, 'UTF-8', $this->encoding); break;
				case self::MODE_DECODE: $value = mb_convert_encoding($value, $this->encoding, 'UTF-8'); break;
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

	/**
	 * mixed[] The array-turned object
	 */
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

	public function visitShopgateCategory(ShopgateCategory $d) {
		$this->array = $this->iterateSimpleProperties($d->buildProperties());
	}

	public function visitShopgateItem(ShopgateItem $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['options'] = $this->iterateObjectList($properties['options']);
		$properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitShopgateItemOption(ShopgateItemOption $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate item option values
		$properties['option_values'] = $this->iterateObjectList($properties['option_values']);
		// TODO: $properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitShopgateItemOptionValue(ShopgateItemOptionValue $i) {
		$this->array = $this->iterateSimpleProperties($i->buildProperties());
	}

	public function visitShopgateItemInput(ShopgateItemInput $d) {
		// get properties and iterate (no complex types in ShopgateDeliveryNote objects)
		$this->array = $this->iterateSimpleProperties($d->buildProperties());
	}

	public function visitConfig(ShopgateConfig $c) {
		$properties = $this->iterateSimpleProperties($c->buildProperties());
		$additionalSettings = $this->iterateSimpleProperties($c->returnAdditionalSettings());
		$this->array = array_merge($properties, $additionalSettings);
	}

	protected function iterateSimpleProperties(array $properties) {
		foreach ($properties as $key => &$value) {
			if (empty($value)) continue;

			// we only want the simple types
			if (is_object($value)) continue;

			// iterate through arrays recursively
			if (is_array($value)) {
				$this->iterateSimpleProperties($value);
				continue;
			}

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