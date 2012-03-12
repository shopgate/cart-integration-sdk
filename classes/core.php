<?php

###################################################################################
# Defines
###################################################################################

define('SHOPGATE_LIBRARY_VERSION', "2.0.0");

define('SHOPGATE_BASE_DIR', realpath(dirname(__FILE__).'/../'));
define('SHOPGATE_ITUNES_URL', 'http://itunes.apple.com/de/app/shopgate-eine-app-alle-shops/id365287459?mt=8');

## QR-Code Config - Start
define('QR_CACHEABLE', false);
define('QR_CACHE_DIR', false);
define('QR_LOG_DIR', dirname(__FILE__).'/../temp/');
define('QR_FIND_BEST_MASK', true);
define('QR_FIND_FROM_RANDOM', 2);
define('QR_DEFAULT_MASK', 2);
define('QR_PNG_MAXIMUM_SIZE',  1024);
## QR-Code Config - End



###################################################################################
# Helper-Klassen
###################################################################################

function debug($debug) {
	echo "<pre>";
	var_dump($debug);
	echo "</pre>";
}

function sg_json_encode($array) {
	$string = "";
	if(function_exists("json_encode")) {
		$string = json_encode($array);
	} else {
		if(!class_exists("Services_JSON"))
			require_once dirname(__FILE__).'/../vendors/JSON.php';
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$string = $json->encode($array);
	}

	return $string;
}

function sg_json_decode($json, $assoc = false) {
	$array = "";
	if(function_exists("json_encode")) {
		$array = json_decode($json, $assoc);
	} else {
		if(!class_exists("Services_JSON"))
			require_once dirname(__FILE__).'/../vendors/JSON.php';

		$jsonUse = SERVICES_JSON_IN_OBJ;
		if($assoc) $jsonUse = SERVICES_JSON_LOOSE_TYPE;

		$oJson = new Services_JSON($jsonUse);
		$array = $oJson->decode($json);
	}

	return $array;
}

function ShopgateErrorHandler($errno, $errstr, $errfile, $errline) {
	//no difference between excpetions and E_WARNING
	$msg = "Fatal PHP Error [Nr. $errno : $errfile / $errline] ";
	$msg .= "$errstr";

	$msg .= "\n". print_r(debug_backtrace(false), true);

	ShopgateObject::logWrite($msg);

	return true;
}

/**
 * Diese Excpetion wird in einem Fehlerfall von der Shopgate Library geworfen.
 * Alle Fehler werden im Log mitprotokolliert.
 *
 * @author Martin Weber
 * @version 1.0.$Rev: 81 $
 *
 */
class ShopgateLibraryException extends Exception {
	public $lastResponse;

	/**
	 * Es sind nur Nachrichten als Text erlaubt
	 * @param string $message
	 */
	function __construct($message) {
		$this->lastResponse = $message;

		$btrace = debug_backtrace();
		$btrace = $btrace[1];
		$message = (isset($btrace["class"])?$btrace["class"]."::":"").$btrace["function"]."():".$btrace["line"]." - " . print_r($message, true);
		ShopgateLibrary::logWrite($message);

		parent::__construct($message);
	}
}


/**
 * Einstellungen für das Framework
 *
 * @author Daniel Aigner
 * @version 1.0.0
 *
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
		'api_url' => 'https://api.shopgate.com/merchant_api/',
		'customer_number' => 'THE_CUSTOMER_NUMBER',
		'shop_number' => 'THE_SHOP_NUMBER',
		'apikey' => 'THE_API_KEY',
		'server' => 'live',
		'plugin' => 'example',
		'plugin_language' => 'DE',
		'plugin_currency' => 'EUR',
		'plugin_root_dir' => "",
		'enable_ping' => true,
		'enable_get_shop_info' => true,
		'enable_add_order' => true,
		'enable_update_order' => true,
		'enable_connect' => true,
		'enable_get_items_csv' => true,
		'enable_get_categories_csv' => true,
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
				return SHOPGATE_BASE_DIR.'/temp/access.log';
			}
		} else {
			if(isset(self::$config['path_to_error_log_file'])) {
				return self::$config['path_to_error_log_file'];
			} else {
				return SHOPGATE_BASE_DIR.'/temp/error.log';
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
		if(!preg_match("/^\S+/", $newConfig['apikey'])){
			throw new ShopgateLibraryException("Das Feld 'apikey' in der Konfiguration hat ein Falsches Format oder ist leer. Bitte prüfen Sie, das keine Leerzeichen vorhanden sind.");
		}
		if(!preg_match("/^\d{5,}$/", $newConfig['customer_number'])){
			throw new ShopgateLibraryException("Das Feld 'customer_number' in der Konfiguration muss mindestens fünf Ziffern enthalten und darf keine Leerzechen enthalten.");
		}
		if(!preg_match("/^\d{5}$/", $newConfig['shop_number'])){
			throw new ShopgateLibraryException("Das Feld 'shop_number' in der Konfiguration muss genau fünf Ziffern enthalten und darf keine Leerzeichen enthalten.");
		}

		////////////////////////////////////////////////////////////////////////
		// Server URL setzen
		////////////////////////////////////////////////////////////////////////
		if(!empty($newConfig["server"]) && $newConfig["server"] === "pg") {
			// Playground?
			self::$config["api_url"] = "https://api.shopgatepg.com/merchant_api/";
		} else if(!empty($newConfig["server"]) && $newConfig["server"] === "custom"
		&& !empty($newConfig["server_custom_url"])) {
			// Eigener Test-Server?
			self::$config["api_url"] = $newConfig["server_custom_url"];
		} else {
			// Live-Server?
			self::$config["api_url"] = "https://api.shopgate.com/merchant_api/";
		}
	}

	public static function saveConfig() {
		$config = self::validateAndReturnConfig();

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
		$handle = fopen(dirname(__FILE__).'/../config/myconfig.php', 'w+');
		if($handle == false){
			throw new ShopgateLibraryException("Fehler beim Lesen oder Schrieben der Config-Datei");
			fclose($handle);
		}else{
			if(!fwrite($handle, $returnString))
			throw new ShopgateLibraryException("Fehler beim Lesen oder Schrieben der Config-Datei");
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
		echo sg_json_encode($response);
		exit;
	}
}

class ShopgateObject {

	/**
	 * Der FileHandler für die Fehler-Log-Datei.
	 *
	 * @var resource
	 */
	protected static $errorLogFileHandler = null;
	/**
	 *
	 * Der FileHandler für die Zugriff-Log-Datei.
	 *
	 * @var resource
	 */
	protected static $accessLogFileHandler = null;

	/**
	 * Leitet die geloggten Daten an logWrite weiter
	 *
	 * @see lib/ShopgateObject::logWrite($msg)
	 * @param string $msg
	 */
	public function log($msg, $type="error") {
		self::logWrite($msg, $type);
	}

	/**
	 * Schreibt die Nachricht in die Log-Datei.
	 * Wenn die Datei noch nicht existiert, wird diese
	 * automatisch erstellt.
	 *
	 * Der Speicherort dieser Datei ist temp/shopgate_framework.log.
	 * Alternativ kann man einen Pfad in dr config.php angeben
	 * <code>
	 * $shopgate_config['path_to_log_file'] = "/path/to/file.log";
	 * </code>
	 *
	 * @param string $msg
	 */
	public static function logWrite($msg, $type="error") {
		$logFilePath = ShopgateConfig::getLogFilePath($type);
		$msg = gmdate('d-m-Y H:i:s: ').$msg."\n";
		if($type === "access") {
			if(!self::$accessLogFileHandler) {
				// Datei öffnen
				self::$accessLogFileHandler = fopen($logFilePath, 'a');
			}
			// In Datei schreiben
			fwrite(self::$accessLogFileHandler, $msg);
		}
		else {
			if(!self::$errorLogFileHandler) {
				// Datei öffnen
				self::$errorLogFileHandler = fopen($logFilePath, 'a');
			}
			// In Datei schreiben
			fwrite(self::$errorLogFileHandler, $msg);
		}
	}

	/**
	 * Converts a an underscored string to a camelized one.
	 *
	 * e.g.:<br />
	 * $this->camelize("get_categories_csv") returns "getCategoriesCsv"<br />
	 * $this->camelize("shopgate_library", true) returns "ShopgateLibrary"<br />
	 *
	 * @param string $str The underscored string.
	 * @param bool $capitalize_first Set true to capitalize the first letter (e.g. for class names). Default: false.
	 * @return string The camelized string.
	 */
	public function camelize($str, $capitalize_first = false) {
		if($capitalize_first) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z0-9])/', $func, $str);
	}

	/**
	 * Sorgt am Ende für das Schließen der Log-Datei,
	 * falls diese noch offen sein sollte.
	 */
	public function __destruct() {
		if(self::$errorLogFileHandler) {
			// Datei schließen
			fclose(self::$errorLogFileHandler);
			self::$errorLogFileHandler = null;
		}
	}
}

class ShopgateLibrary extends ShopgateObject {
	private static $singleton;

	/**
	 * Konfiguration des Frameworks.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Das Plugin für das jeweilige Shopping-System, das passende
	 * Plugin wird entsprechend der Config geladen.
	 *
	 * @var ShopgatePluginApi
	 */
	private $plugin;

	/**
	 * Die übergebenen POST- und GET-Parameter.
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Die Klasse für die Kommunikation mit der ShopgateMerchantApi.
	 *
	 * @var ShopgateMerchantApi
	 */
	private $shopgateMerchantApi;

	/**
	 * Die erlaubten Funktionen, die aufgerufen werden können.
	 *
	 * @var array
	 */
	private  $actionWhitelist = array(
		'ping',
		'get_shop_info',
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

	/**
	 * Die Daten, die zurück an Shopgate gehen. Dieses Array wird beim
	 * Beenden der Startfunktion als json-Array zurückgegeben
	 *
	 * @var array
	 */
	private $response = array();

	/**
	 * @return ShopgateLibrary
	 */
	public static function &getInstance() {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	private function __construct() {
		// Übergebene Parameter importieren
		// TODO in $_POST ändern. Zum testen $_REQUEST
		$this->params = $_REQUEST;

		$this->response["error"] = 0;
		$this->response["error_text"] = "";
// 		$this->response["version"] = SHOPGATE_PLUGIN_VERSION;
		$this->response["trace_id"] = isset($this->params["trace_id"]) ? $this->params["trace_id"] : null;
	}

	/**
	 * Registers the current ShopgatePluginApi's instance for callbacks.
	 *
	 * This is usually done by ShopgatePluginApi::__construct() as soon as you instantiate your plugin implementation.
	 * The registered instance is the one whose callback methods (e.g. ShopgatePlugin::addOrder()) get called on incoming
	 * requests.
	 *
	 * @param ShopgatePluginApi $shopgatePluginApi
	 */
	public function setPlugin(ShopgatePluginApi $shopgatePluginApi) {
		$this->plugin = $shopgatePluginApi;
	}

	public function setConfig(ShopgateConfig $config) {
		$this->config = $config->getConfig();
	}

	/**
	 * Dies ist der Einstiegspunkt des Frameworks. Es werden die Konfigurationen
	 * ausgelesen und gesetzt. Vor dem Aufrufen der eigentlichen Aktion wird
	 * geprüft, ob diese in der Konfiguration auch freigegeben wurde.
	 *
	 * Eventuell aufgetretene Fehler werden hier abgefangen und an den Server
	 * zurückgegeben.
	 *
	 * @throws ShopgateLibraryException
	 */
	public function handleRequest($data) {
		define("_SHOPGATE_API", true);

		// TODO: Workaround entfernen
		$this->params = $data;

// 		$valServ = new ShopgateAuthentificationService();
// 		$valServ->checkValidAuthentification();

		header("HTTP/1.0 200 OK");

		try {
			// Config-Datei laden
			$this->config = ShopgateConfig::validateAndReturnConfig();

			// Plugin-Datei laden
			//$this->plugin = ShopgatePluginCore::newInstance($this->config);

			if(!empty($this->params["use_errorhandler"]))
				set_error_handler('ShopgateErrorHandler');

			// Action überprüfen und aufrufen
			if(empty($this->params['action'])) {
				throw new ShopgateLibraryException('Get-Parameter "action" nicht übergeben');
			}

			$action = $this->params['action'];

// 			if(!in_array($action, $this->actionWhitelist)) {
// 				throw new ShopgateLibraryException('Unbekannte Action: '.$action);
// 			} if($this->config['enable_'.$action] !== true) {
// 				throw new ShopgateLibraryException('Action '.$action.' ist nicht in der Config-Datei erlaubt worden');
// 			}

			$actionCamelCase = $this->__toCamelCase($action);

			$this->{$actionCamelCase}();
		} catch (Exception $e) {
			header('HTTP/1.0 500 ShopgateLibraryException Throwed', true, 500);

			// Abfangen einer beliebigen Excpetion innerhalb eines Plugins.
			// Der Fehler wird an den Serve zurückgegeben
			$this->response["error"] = 1; // TODO:
			$this->response["error_text"] = $e->getMessage();
		}

		// Gib die Daten zurück an Shopgate

		header('Content-Type: application/json');
		echo sg_json_encode($this->response);

		return !(isset($this->response["error"]) && $this->response["error"] > 0);
	}

	/**
	 * Erzeugt aus get_items_csv => getItemsCSV
	 *
	 * @param string $str
	 * @param bool $capitalise_first_char
	 */
	private function __toCamelCase($str, $capitalise_first_char = false) {
		if($capitalise_first_char) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}

	/**
	 * Prüft ob der gegebene API-Key mit dem der Konfiguration übereinstimmt.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function __checkApiKey() {
		if(defined('DEBUG') && DEBUG == 1) return ;

		if(!isset($this->params['shop_number'])) {
			header("HTTP/1.0 403 Forbidden");
			throw new ShopgateLibraryException('Keine shop_number übergeben');
		} elseif($this->params['shop_number'] != $this->config['shop_number']) {
			header("HTTP/1.0 403 Forbidden");
			throw new ShopgateLibraryException('Die shop_number ist falsch');
		}
	}


	/****************************************
	 * Actions die Aufgerufen werden können
	 ****************************************/

	/**
	 * Liefert mindestens einen "pong=OK" zurück.
	 *
	 * Wenn der API-Key und die Customer-Number stimmen, werden Informationen
	 * zum Server zurückgegeben. U.a, welche Server-Version und welche Plugins
	 * installiert sind.
	 */
	private function ping() {
		$this->response["pong"] = "OK";

		// Statusmeldung ausgeben

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
	}

	/**
	 * Liefert Information ueber das verwendete Shopsystem zurueck
	 */
	private function getShopInfo() {
		//$this->__checkApiKey();
		$Plugin = ShopgatePluginCore::newInstance($this->config);
		$info = $Plugin->startCreateShopInfo();
		if(!empty($info)){
			$this->response["shopinfo"] = $info;
		}else{
			$this->response["shopinfo"] = 'Keine Information über das Shopsystem verfügbar';
		}
	}

	/**
	 * Informiere das Framework über neue Meldungen wie z.B. eine neue
	 * Bestellung eingegangen.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function addOrder() {
		$this->log("Bestellung mit folgenden Parametern wurde übergeben:\n".print_r($this->params,true), 'access');

		// Benachrichtigung über neue Bestellung oder sonstige Benachrichtigung
		if(!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException('add_order aufgerufen, aber keine order_number übergeben');
		}

		$this->shopgateMerchantApi = new ShopgateMerchantApi();
		$orders = $this->shopgateMerchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number']));
		foreach($orders as $order){
			$orderId = $this->plugin->addOrder($order);
		}

		$this->response["external_order_number"] = $orderId;
	}

	/**
	 * Informiere das Framework über neue Meldungen wie z.B. eine neue
	 * Bestellung eingegangen.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function updateOrder() {
		//$this->__checkApiKey();

		$this->log("Bestellung mit folgenden Parametern wurde übergeben:\n".print_r($this->params,true), 'access');

		// Benachrichtigung über neue Bestellung oder sonstige Benachrichtigung
		if(!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException('add_order aufgerufen, aber keine order_number übergeben');
		}

		$orderApi = new ShopgateOrderApi();
		// Neue Bestellung: Daten holen und im eigenen System speichern
		$order = $orderApi->getOrderDetails($this->params['order_number']);

		$this->plugin->addOrder($order);
	}

	/**
	 * ShopgateConnect
	 * Verbindet einen ShopgateAccount mit einem ShopAccount.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function getCustomer() {
		$this->log("Call ShopgateConnect", "access");
		//$this->__checkApiKey();

		// Shopgate-Connect
		// GET-Parameter: user, pass
		if(!isset($this->params['user'])) {
			throw new ShopgateLibraryException('Parameter user nicht übergeben');
		} elseif(!isset($this->params['pass'])) {
			throw new ShopgateLibraryException('Parameter pass nicht übergeben');
		}

		// Die Userdaten über das Plugin auslesen
		$userData = $this->plugin->getCustomer($this->params['user'], $this->params['pass']);

		if(!is_object($userData) || get_class($userData) !== "ShopgateCustomer") {
			throw new ShopgateLibraryException("Das zurückgegebene Format ist ungültig.");
		}

		// Daten als JSON zurückliefern
		$data = $userData->toArray();
		$this->response["user_data"] = $data[0];
		$this->response["addresses"] = $data[1];
	}

	/**
	 * Liefert die generierte items.csv-Datei an Shopgate zurück.
	 *
	 * Nach der Ausgabe wird das Skript sofort beendet.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function getItemsCsv() {
		//$this->__checkApiKey();

		$generate_csv = $this->config["generate_items_csv_on_the_fly"];

		if(isset($this->params["generate_items_csv_on_the_fly"]))
		$generate_csv = $this->params["generate_items_csv_on_the_fly"];

		$this->log("Parameter: " . print_r($this->params, true), "access");

		if(isset($this->params["limit"]) && isset($this->params["offset"])) {

			$this->plugin->exportLimit = (string) $this->params["limit"];
			$this->plugin->exportOffset = (string) $this->params["offset"];
			$this->plugin->splittetExport = true;

		}

		$fileName = ShopgateConfig::getItemsCsvFilePath();

		if($generate_csv) {
			// CSV-Datei erstellen/updaten
			$this->plugin->startGetItemsCsv();
		}

		if(!file_exists($fileName)) {
			throw new ShopgateLibraryException("Datei $fileName konnte nicht gefunden werden.");
		}

		// Inhalt der Datei zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="items.csv"');

		$fp = fopen($fileName, "r");

		if(!$fp) {
			throw new ShopgateLibraryException('Konnte Datei nicht öffnen');
		}

		while($line = fgets($fp) )
		{
			echo $line;
		}//while end

		fclose($fp);

		exit;
	}

	private function getCategoriesCsv() {
		//$this->__checkApiKey();

		$fileName = ShopgateConfig::getCategoriesCsvFilePath();

		// Plugin-Klasse initialisieren
// 		$this->plugin = ShopgatePluginCore::newInstance($this->config);

		// CSV-Datei erstellen/updaten
		$this->plugin->startGetCategoriesCsv();

		if(!file_exists($fileName)) {
			throw new ShopgateLibraryException("Datei $fileName konnte nicht gefunden werden.");
		}

		// Inhalt der Datei zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="categories.csv"');

		$fp = fopen($fileName, "r");

		if(!$fp) {
			throw new ShopgateLibraryException('Konnte Datei nicht öffnen');
		}

		while($line = fgets($fp) )
		{
			echo $line;
		}//while end

		fclose($fp);

		exit;
	}

	/**
	 * Liefert die generierte reviews.csv Datei an Shopgate zurück.
	 *
	 * Nach der Ausgabe wird das Skript sofort beendet.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function getReviewsCsv() {
		//$this->__checkApiKey();

		$fileName = ShopgateConfig::getReviewsCsvFilePath();

		$Plugin = ShopgatePluginCore::newInstance($this->config);
		$Plugin->startGetReviewsCsv();

		if(!file_exists($fileName)) {
			throw new ShopgateLibraryException("Datei $fileName nicht gefunden");
		}

		// Inhalt der Datei an den Browser zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="reviews.csv"');
		readfile($fileName);
		exit;
	}

	/**
	 * Liefert die generierte pages.csv-Datei an Shopgate zurück.
	 *
	 * Nach der Ausgabe wird das Skript sofort beendet.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function getPagesCsv() {
		//$this->__checkApiKey();

		$fileName = ShopgateConfig::getPagesCsvFilePath();

		if(!file_exists($fileName)) {
			throw new ShopgateLibraryException("Datei $fileName nicht gefunden");
		}

		// Inhalt der Datei an den Browser zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="pages.csv"');
		readfile($fileName);
		exit;
	}

	/**
	 * Parameter "kilobyte" muss gesetzt sein. Liefert dann die
	 * letzten x Kilobyte der Log-Datei zurück.
	 *
	 */
	private function getLogFile() {
		//$this->__checkApiKey();

		$type = "error";
		if(isset($this->params['log_type'])) {
			$type = $this->params['log_type'];
		}

		if(!isset($this->params['kilobyte'])) {
			throw new ShopgateLibraryException('Parameter kilobyte nicht übergeben');
		}


		$kilobyte = $this->params['kilobyte']; // Letzten x Kilobyte der Logdatei zurückgeben

		$filePath = ShopgateConfig::getLogFilePath($type);

		$bufferSize = floatval($kilobyte) * 1024;
		$returnStr = "";

		$fileSize = filesize($filePath);
		if($fileSize > $bufferSize) {
			$log = fopen($filePath,'r');
			$returnStr = (fseek($log, -1*$bufferSize, SEEK_END) == 0) ? fread($log, $bufferSize) : "Error reading the file $filePath.";
			fclose($log);
		} else {
			$returnStr = file_get_contents($filePath);
		}


		echo $returnStr;
		exit;
	}

}

class ShopgateMerchantApi extends ShopgateObject {
	private $config;

	/**
	 * Der Konstruktor der ShopgateMerchantApi-Klasse.
	 * Lädt die angegebene Config.
	 *
	 * ShopgateConfig
	 */
	public function __construct() {
		$this->config = ShopgateConfig::validateAndReturnConfig();
	}

	/**
	 * Führt alle Abfragen an der ShopgateMerchantApi durch.
	 *
	 * @access private
	 *
	 * @param array $data  	Die POST-Parameter der aufgerufenen Funktion.
	 *
	 * @throws ShopgateLibraryException
	 */
	private function sendRequest($data) {
		if(empty($this->config)) $this->config = ShopgateConfig::validateAndReturnConfig();


		$data['shop_number'] = $this->config["shop_number"];

		$url = $this->config["api_url"];
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "ShopgatePlugin/" . SHOPGATE_PLUGIN_VERSION);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, ShopgateAuthentificationService::getCurlAuthentificationString());
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		if (!$response) throw new ShopgateLibraryException("No Connection to the Server");

		$decodetResponse = sg_json_decode($response, true);

		if(empty($decodetResponse)) {
			throw new ShopgateLibraryException('Error Parsing the Response \n'.print_r($response, true));
		}

		if($decodetResponse['error'] != 0) {
			throw new ShopgateLibraryException($decodetResponse);
		}

		return $decodetResponse;
	}

	/**
	 * Die Details einer Bestellung von Shopgate mit der Bestellnummer abholen.
	 * Es wird ein Objekt mit den kompletten Bestellinformationen zurückgegeben.
	 *
	 * @tutorial <a href="https://www.shopgate.com/apidoc/function_details/9">
	 * https://www.shopgate.com/apidoc/function_details/9</a>
	 *
	 * @param mixed[] $parameter	Sucheinschränkungen können über diesen Parameter eingestellt werden.
	 * @throws ShopgateLibraryException
	 * @return ShopgateOrder[] 	Die Bestellung in einem ShopgateOrder-Objekt
	 */
	public function getOrders($parameter) {
		$data["action"] = "get_orders";
		$data["with_items"] = 1;

		$data = array_merge($data, $parameter);
		$result = $this->sendRequest( $data );

		if(empty($result["orders"])) throw new ShopgateLibraryException("Das Format entspricht nicht der ShopgateAPI.\n".print_r($result,true));

		$orders = array();
		foreach($result["orders"] as $order) {
			$orders[] = new ShopgateOrder( $order );
		}

		return $orders;
	}

	/**
	 * Fügt einer Bestellung von Shopgate einen Lieferschein hinzu.
	 *
	 * @param ShopgateOrder $order
	 * @param String $shippingServiceId
	 * @param Integer $trackingNumber
	 * @param Boolean $markAsCompleted
	 * @return mixed[]
	 */
	public function addOrderDeliveryNote(ShopgateOrder $order, $shippingServiceId, $trackingNumber, $markAsCompleted = false) {
		if(is_object($order) && get_class($order) == "ShopgateOrder")
			$order = $order->getOrderNumber();

		$data = array(
			"action" => "addOrderDeliveryNote",
			"order_number" => $order,
			"shipping_service_id" => $shippingServiceId,
			"tracking_number" => (string) $trackingNumber,
			"mark_as_completed" => $markAsCompleted,
		);

		return $this->_execute($data);
	}

	/**
	 * Eine Bestellung als abgeschlossen markieren
	 *
	 * @param String $orderNumber
	 * @return mixed []
	 */
	public function setOrderShippingCompleted($orderNumber) {
		$data = array(
			'action'=>'set_order_shipping_completed',
			'order_number'=>$orderNumber,
		);

		return $this->sendRequest($data);
	}

	/**
	 * Eine Nachricht an den Kunden der Bestellung schicken.
	 *
	 * @param ShopgateOrder $order	Die Bestellung in einem ShopgateOrder-Objekt
	 * @param string $message	Die Nachricht an den Kunden
	 */
	public function sendOrderMessage($order, $message) {
		$data = array(
			"action" => "send_order_message",
			"order_number"=>$order->getOrderNumber(),
			"message"=>$message,
		);

		$this->sendRequest($data);
	}

}

/**
 * The Basic functions of the Framework
 *
 * The PlugIns must implements the following functions
 *
 * <code>
 * class ShopgatePlugin extends ShopgatePluginCore {
 *	public function getUserData($user, $pass) {}
 *	public function getOrders() {}
 *	public function addOrder(ShopgateOrder $order) {}
 *	public function updateOrder(ShopgateOrder $order) {}
 *	protected function createItemsCSV() {}
 *	protected function createCategoriesCSV() {}
 *	protected function createReviewsCSV() {}
 *	protected function createPagesCSV() {}
 *	protected function createCustomer() {}
 *
 * }
 * </code>
 *
 * @author Martin Weber
 * @version 1.0.0
 */
abstract class ShopgatePluginApi extends ShopgateObject {
	private $allowedEncodings = array(
		'UTF-8', 'ASCII', 'CP1252', 'ISO-8859-15', 'UTF-16LE','ISO-8859-1'
	);

	/**
	 * Die Handler für die Datei, in die geschrieben werden soll.
	 *
	 * @var resource
	 */
	protected $fileHandle;
	/**
	 * Der Buffer.
	 *
	 * @var array
	 */
	private $buffer = array();
	/**
	 * Der aktuelle Füllstand des Buffers.
	 *
	 * @var int
	 */
	private $bufferCounter = 0;

	/**
	 * Die Konfiguration des Plugins.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Wenn der Buffer größer als dieser Wert ist,
	 * werden alle Datensätze in die Datei geschrieben.
	 *
	 * @var int
	 */
	protected $bufferLimit = 100; // Gibt an, nach wievielen Zeilen in die CSV-Datei geschrieben werden soll

	public $exportLimit = 1000;

	public $exportOffset = 0;

	public $splittetExport = false;

	final public function __construct() {
		ShopgateLibrary::getInstance()->setPlugin($this);

		if(!$this->setConfig(ShopgateConfig::validateAndReturnConfig())) {
			throw new ShopgateLibraryException("Config-Datei konnte nicht initialisiert werden");
		}

		if(isset($this->config["use_custom_error_handler"]) && $this->config["use_custom_error_handler"]) {
			set_error_handler('ShopgateErrorHandler');
		}

		// Muss das Wort "true" in der überschriebenen startup() zurückgeben
		if($this->startup() !== true) {
			throw new ShopgateLibraryException("Plugin konnte nicht initialisiert werden ");
		}
	}

	/**
	 * Speichert die Konfiguration.
	 *
	 * @param array $config
	 */
	public final function setConfig(array $config) {
		$this->config = $config;

		return true;
	}

	/**
	 * Wird beim Start aufgerufen. Funktion überschreiben um
	 * hier evtl. eigene Variablen zu initialisieren, oder die
	 * Verbindung zu einer Datenbank aufzubauen etc..
	 *
	 */
	public function startup() {

		return true;
	}

	/**
	 * Wird bei jedem Request aufgerufen und leitet die Anfrage zum Framework weiter,
	 * damit dieses dann die Anfrage weiter bearbeiten kann.
	 */
	public function handleRequest($data = array()) {
		ShopgateLibrary::getInstance($this)->handleRequest($data);
	}

	/**
	 * Starte das Erstellen der ShopInfo
	 *
	 * @return unknown
	 */
	public function startCreateShopInfo() {
		$shopInfo = $this->createShopInfo();

		return $shopInfo;
	}

	/**
	 * Erstellt eine neue Datei und einen neuen Buffer für Schreibzugriffe in diese Datei
	 *
	 * @param String $filePath   - Der Pfad zu der zuerzeugenden Datei (ohne .tmp)
	 * @param Boolean $createTempFile  -  Erzeugt eine .tmp
	 */
	private final function createBuffer($filePath){
		$timeStart = time();
		$filePath .= ".tmp";

		$this->log(basename($filePath).' wird erstellt', "access");

		$this->fileHandle = @fopen($filePath, 'w');
		if(!$this->fileHandle) {
			throw new ShopgateLibraryException("Datei $filePath konnte nicht geöffnet/erstellt werden");
		}
		$this->buffer = array();
	}

	/**
	 * Schließt die Datei und leert den Buffer
	 *
	 * @param String $filePath  Der Pfad der zu erzeugten Datei (ohne .tmp)
	 * @param Boolean $isTempFile  Es handelt sich um eine .tmp - Datei. Die Datei wird umbenannt von $filePath.tmp in $filePath
	 * @param Boolean $saveOldFile
	 */
	private final function finishBuffer($filePath){
		$this->flushBuffer(); // Evtl. noch nicht gespeicherte Daten im Buffer schreiben
		fclose($this->fileHandle);

		rename($filePath.".tmp", $filePath);

		$this->log('Fertig, '.basename($filePath).' wurde erfolgreich erstellt', "access");
		$duration = time() - $timeStart;
		$this->log("Dauer: $duration Sekunden", "access");
	}

	/**
	 * Starte das Erstellen der items.csv.
	 *
	 * Stellt sicher, dass die Datei beschrieben werden kann und das der Buffer
	 * geleert wird.
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetItemsCsv() {
		$this->createBuffer(ShopgateConfig::getItemsCsvFilePath());
		$this->createItemsCsv(); // CSV-Datei mit Buffer schreiben
		$this->finishBuffer(ShopgateConfig::getItemsCsvFilePath());
	}

	public final function startGetCategoriesCsv() {
		$this->createBuffer(ShopgateConfig::getCategoriesCsvFilePath());
		$this->createCategoriesCsv(); // CSV-Datei mit Buffer schreiben
		$this->finishBuffer(ShopgateConfig::getCategoriesCsvFilePath());
	}

	/**
	 * Starte das Erstellen der reviews.csv.
	 *
	 * Stellt sicher, dass die Datei beschrieben werden kann und das der Buffer
	 * geleert wird.
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetReviewsCsv() {
		$this->createBuffer(ShopgateConfig::getReviewsCsvFilePath());
		$this->createReviewsCsv(); // CSV-Datei mit Buffer schreiben
		$this->finishBuffer(ShopgateConfig::getReviewsCsvFilePath());
	}

	/**
	 * Starte das Erstellen der pages.csv.
	 *
	 * Stellt sicher, dass die Datei beschrieben werden kann und das der Buffer
	 * geleert wird.
	 *
	 * @throws ShopgateLibraryException
	 *
	 */
	public final function startGetPagesCsv() {
		$this->createBuffer(ShopgateConfig::getPagesCsvFilePath());
		$this->createPagesCsv(); // CSV-Datei mit Buffer schreiben
		$this->finishBuffer(ShopgateConfig::getReviewsCsvFilePath());
	}

	/**
	 * Zeile in die CSV-Datei schreiben (gebuffert)
	 *
	 * @param array $itemArr
	 */
	protected final function addItem($itemArr) {
		// Item Buffern, evtl. Buffer schreiben
		$this->buffer[] = $itemArr;
		$this->bufferCounter++;

		if($this->bufferCounter > $this->bufferLimit
		|| isset($this->config["flush_buffer_size"]) && $this->config["flush_buffer_size"] <= $this->bufferCounter) {
			$this->flushBuffer();
		}
	}

	/**
	 * Flush Buffer to file if $this->bufferLimit is exceeded.
	 * The content is convert to UTF-8 if necessary.
	 *
	 */
	private final function flushBuffer() {
		// Buffer leerschreiben
		$c = "\"";
		$string = '';

		if(empty($this->buffer) && ftell($this->fileHandle) == 0)
			throw new ShopgateLibraryException("Buffer ist Leer");


		// Wenn noch am Anfang der CSV-Datei, schreibe die Kopfzeile
		if(ftell($this->fileHandle) == 0) {
			fputcsv($this->fileHandle, array_keys($this->buffer[0]), ';', '"');
		}

		// Schreibe jeden Datensatz nach $string
		foreach($this->buffer as $item) {
			// Konvertiere nach UTF-8
			if(function_exists("mb_convert_encoding")) {
				foreach($item as &$field) {
// 					if(mb_detect_encoding($field, $this->allowedEncodings) != "UTF-8")
					$field = mb_convert_encoding($field, "UTF-8", $this->allowedEncodings);
				}
			}

			fputcsv($this->fileHandle, $item, ";", "\"");
		}

		$this->buffer = array(); // Leere den Buffer
		$this->bufferCounter = 0; // Setze zähler auf
	}

	/**
	 * Schreibe die Fehlermeldung in das Log
	 * und werfe eine ShopgateLibraryException
	 *
	 * @param string $msg	Die Fehlermeldung.
	 * @throws ShopgateLibraryException
	 */
	protected final function _error($msg) {
		$this->log($msg);
		throw new ShopgateLibraryException($msg);
	}

	/**
	 * Setze Anführngszeichen um array Elemente
	 *
	 * @param array $array Der Daten-array
	 * @return daten-array
	 * @throws ShopgateLibraryException
	 * @deprecated
	 */
	public function enquoteArray ($array)
	{
		if (!is_array($array)) {
			throw new ShopgateLibraryException("Array parameter ist kein array");
		}

		foreach ($array as $k=>$v) {
			$array[$k] = $this->enquote ($v);
		}

		return $array;
	}

	/**
	 * Setze Anführngszeichen um einen String
	 *
	 * @param string $string Der String
	 * @return String
	 * @deprecated
	 */
	public function enquote ($string)
	{
		return '"' . $string . '"';
	}

	/**
	 *
	 * Build a full Category-Array with default values
	 *
	 * @see http://www.shopgate.com/csvdoc/csv_docu_categories/
	 * @return array
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
	 * Build a full Product-Array with default values
	 *
	 * @see http://www.shopgate.com/csvdoc
	 * @return array
	 * @deprecated
	 */
	protected  function buildDefaultRow() {
		return $this->buildDefaultProductRow();
	}

	/**
	 *
	 * Build a full-Array with default values
	 *
	 * @see http://www.shopgate.com/csvdoc
	 * @return array
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

	protected function formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "") {
		$price = round($price, $digits);
		$price = number_format($price, $digits, $decimalPoint, $thousandPoints);
		return $price;
	}

	/**
	 * Filter the String and remove all tags, which are not allowed.
	 *
	 * @param String $string
	 * @param array $removeTags
	 * @param array $allowedTags
	 *
	 * @return String
	 */
	protected function removeTagsFromString($string, $removeTags = array(), $allowedTags = array()) {
		// Verfügbare Tags
		$_Tags = array("ADDRESS", "APPLET", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE",
			"BODY", "BR", "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
			"EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML", "IMG", "INPUT",
			"ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
			"P", "SAMP", "SCRIPT", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
			"TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR");

		foreach ($_Tags as &$t) $t = strtolower($t);
		foreach ($removeTags as &$t) $t = strtolower($t);
		foreach ($allowedTags as &$t) $t = strtolower($t);

		// Tags, die immer entfernt werden
		$force_remove_tags = array("APPLET", "SCRIPT", "OBJECT");
		$aTags = array_diff($_Tags, $force_remove_tags);

		$_Tags = array_merge($_Tags, $allowedTags);

		// Angegebene Tags entfernen
		$_Tags = array_diff($_Tags, $removeTags);

		foreach ($_Tags as &$t) $t = "<$t>";

		$string = strip_tags($string, implode(",", $_Tags));

		return $string;
	}

	protected $exchangeRate = 1;

	///////////////////////////////////////////////////////////////////////////
	// Die Folgenden Funktionen müssen in der                                //
	// abgeleiteten Klasse implementiert werden                              //
	///////////////////////////////////////////////////////////////////////////

	/**
	 * Vergleicht $user und $pass mit den Daten in der Datenbank und gibt die
	 * Benutzerdaten als ShopgateShopCustomer-Objekt zurück.
	 *
	 *  Diese Funktion muss in der ShopgatePlugin-Klasse implementiert werden!
	 *
	 * @param String $user
	 * @param String $pass
	 * @return ShopgateCustomer
	 */
	public abstract function getCustomer($user, $pass);

	/**
	 * <p>Diese Funktion speichert eine Bestellung in Ihre Datenbank. Das Object $order enthält alle
	 * relevanten Daten und die bestellten Artikel. Zudem werden auch Lieferanschrift,
	 * Rechnungsanschrift und Kundenanschrift mit übergeben.</p>
	 *
	 * <p>Die Produkte können über die Funktion $order->getOrderItems() als Array
	 * abgerufen werden. Jedes Element ist ein Objelt vom Typ ShopgateOrderItem,
	 * welches die Wichtigsten Informationen zu dem jeweiligen Produkt enthält.</p>
	 *
	 * <code>
	 * foreach($order->getOrderItems() as $orderItem) {
	 *
	 * }
	 * </code>
	 *
	 * <p>Die Addressdaten sind vom Typ ShopgateOrderAddress und enthalten jeweils die
	 * Kunden-, Liefer-, oder Rechnungsanschrift.</p>
	 * <ul>
	 * <li><b>Die Adresse des Kunden:</b><br/>
	 *        $order->getCustomerAddress();</li>
	 * <li><b>Die Lieferadresse:</b><br />
	 *        $order->getDeliveryAddress();</li>
	 * <li><b>Die Rechungsadresse:</b><br />
	 *        $order->getInvoiceAddress();</li>
	 * </ul>
	 *
	 * @param ShopgateOrder $order
	 */
	public abstract function addOrder(ShopgateOrder $order);

	public abstract function updateOrder(ShopgateOrder $order);

	/**
	 * Diese Funktion soll die Daten aus der Datenbank laden und mittels der
	 * Funktion addItem() der CSV-Datei hinzufügen
	 *
	 * Die Dukumentation zum aufbau der CSV-Datei steht unter
	 * <a href="https://www.shopgate.com/csvdoc">https://www.shopgate.com/csvdoc</a>
	 *
	 * @throws ShopgateLibraryException
	 * @example plugins/plugin_example.inc.php
	 */
	protected abstract function createItemsCsv();

	protected abstract function createCategoriesCsv();

	/**
	 * Erzeugt die CSV-Datei mit den Produktberwertungen
	 *
	 * @throws ShopgateLibraryException
	 * @example plugins/plugin_example.inc.php
	 */
	protected abstract function createReviewsCsv();

	/**
	 * Erzeugt die CSV-Datei mit den Zusatztexten für Produkte
	 *
	 * @throws ShopgateLibraryException
	 * @example plugins/plugin_example.inc.php
	 */
	//protected abstract function getPagesCsv();

	/**
	 * Erstellt Informationen ueber das verwendete Shopsystem
	 *
	 * @throws ShopgateLibraryException
	 * @example plugins/plugin_example.inc.php
	 */
	//protected abstract function createShopInfo();
}


class ShopgateAuthentificationService extends ShopgateObject {
	private $customerNumber;
	private $apiKey;
	private $timestamp;

	public function __construct() {
		$config = ShopgateConfig::getConfig();
		$this->customerNumber = $config["customer_number"];
		$this->apiKey = $config["apikey"];
		$this->timestamp = time();
	}

	public function getRequestUsername() {
		$userName = "{$this->customerNumber}-{$this->timestamp}";
		return $userName;
	}

	/**
	 * Generate the Password for requests to shopgate
	 *
	 * Format: SMA-<customer_number>-<unix_timestamp>-<api_key>
	 *
	 * @return string
	 */
	public function getRequestPassword() {
		$password = sha1("SMA-{$this->customerNumber}-{$this->timestamp}-{$this->apiKey}");
		return $password;
	}

	/**
	 * Generates the http-basic auth string <user>:<password>
	 *
	 * @return string
	 */
	public static function getCurlAuthentificationString() {
		$obj = new ShopgateAuthentificationService();
		$string = "{$obj->getRequestUsername()}:{$obj->getRequestPassword()}";
		return $string;
	}

	/**
	 * Login Check
	 *
	 * @throws ShopgateLibraryException
	 * @return boolean
	 */
	public function checkValidAuthentification() {
		header('WWW-Authenticate: Basic realm="Shopgate Merchant API"');
	    header('HTTP/1.0 401 Unauthorized');
	    echo "Insert Valid Login Data";

	    // No Login-Data => Exit
	    if(empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) exit;

	    // Extraxt customer-number and Timestamp from username
		$matches = array();
	 	if(!preg_match('/(?<customer_number>[1-9][0-9]+)-(?<timestamp>[1-9][0-9]+)/', $_SERVER['PHP_AUTH_USER'], $matches)){
			throw new ShopgateLibraryException("authorization username invalid format");
   		}

   		$customer_number = $matches["customer_number"];
   		$timestamp = $matches["timestamp"];

   		// create the authentification-password
		$generatedPassword = sha1("SPA-{$customer_number}-{$timestamp}-{$this->apiKey}");


		$valid = false;

		// Compare customer-number and auth-password
		if($customer_number === $this->customerNumber && $_SERVER["PHP_AUTH_PW"] === $generatedPassword)
			$valid = true;

		// If not valid => Error
		if(!$valid) { throw new ShopgateLibraryException("authorization username invalid format"); }

		return $valid;
	}
}