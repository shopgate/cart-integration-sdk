<?php
/**
 * This interface represents the Shopgate Plugin API as described in our wiki.
 *
 * It provides all available actions and calls the plugin implementation's callback methods for data retrieval if necessary. It acts
 * as singleton.
 *
 * @see http://wiki.shopgate.com/Shopgate_Plugin_API/de
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgatePluginApiInterface {
	/**
	 * Inspects an incoming request, performs the requested actions, prepares and prints out the response to the requesting entity.
	 *
	 * Note that the method usually returns true or false on completion, depending on the success of the operation. However, some actions such as
	 * the get_*_csv actions, might stop the script after execution to prevent invalid data being appended to the output.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return bool false if an error occured, otherwise true.
	 */
	public function handleRequest(array $data = array());
}

/**
 * This class represents the Shopgate Merchant API as described in our wiki.
 *
 * It provides all available actions, calls to the configured API, retrieves, parses and formats the data. It acts as singleton.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateMerchantApiInterface {
	/**
	 * Represents the "get_orders" action.
	 *
	 * @param mixed[] $parameters
	 * @return ShopgateMerchantResponse
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_orders/de
	 */
	public function getOrders($parameters);
	
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
	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false);
	
	/**
	 * Represents the "set_order_shipping_completed" action.
	 *
	 * @param string $orderNumber
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_set_order_shipping_completed/de
	 */
	public function setOrderShippingCompleted($orderNumber);
	
	/**
	 * Represents the "get_mobile_redirect_keywords" action.
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_mobile_redirect_keywords/de
	 */
	public function getMobileRedirectKeywords();
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_items/de
	 */
	public function getItems($data);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_item/de
	 */
	public function addItem($data);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_update_item/de
	 */
	public function updateItem($data);
	
	/**
	 * Delete a Item by given item_number
	 *
	 * @param string $item_number
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_delete_item/de
	 */
	public function deleteItem($item_number);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_categories/de
	 */
	public function getCategories($data);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_category/de
	 */
	public function addCategory($data);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_update_category/de
	 */
	public function updateCategory($data);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_delete_category/de
	 */
	public function deleteCategory($category_number, $delete_subcategories = false, $delete_items = false);
	
	/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_item_to_category/de
	 */
	public function addItemToCategory($item_number, $category_number, $order_index = null);
	
		/**
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_delete_item_from_category/de
	 */
	public function deleteItemFromCategory($item_number, $category_number);
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
interface ShopgateAuthentificationServiceInterface {
	const HEADER_X_SHOPGATE_AUTH_USER  = 'X-Shopgate-Auth-User';
	const HEADER_X_SHOPGATE_AUTH_TOKEN = 'X-Shopgate-Auth-Token';
	const PHP_X_SHOPGATE_AUTH_USER  = 'HTTP_X_SHOPGATE_AUTH_USER';
	const PHP_X_SHOPGATE_AUTH_TOKEN = 'HTTP_X_SHOPGATE_AUTH_TOKEN';
	
	/**
	 * @return string The X-Shopgate-Auth-User HTTP header for an outgoing request.
	 */
	public function buildAuthUserHeader();
	
	/**
	 * @return string The X-Shopgate-Auth-Token HTTP header for an outgoing request.
	 */
	public function buildAuthTokenHeader();
	
	/**
	 * @throws ShopgateLibraryException if authentication fails
	 */
	public function checkAuthentification();
}

class ShopgatePluginApi extends ShopgateObject implements ShopgatePluginApiInterface {
	/**
	 * @var ShopgatePlugin
	 */
	private $plugin;

	/**
	 * @var ShopgateConfig
	 */
	protected $config;

	/**
	 * @var ShopgateMerchantApi
	 */
	protected $merchantApi;

	/**
	 * @var ShopgateAuthentificationService
	 */
	protected $authService;

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
		return ShopgateLibraryFactory::getInstance()->getPluginApi();
	}

	protected final function initLibrary() {
		// fetch config, plugin and API instances
		$factory = &ShopgateLibraryFactory::getInstance();
		$this->config = &$factory->getConfig();
		$this->plugin = &$factory->getPlugin();
		$this->merchantApi = &$factory->getMerchantApi();
		$this->authService = &$factory->getAuthService();
		
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

	public function handleRequest(array $data = array()) {
		// log incoming request
		$this->log($this->cleanParamsForLog($data), ShopgateObject::LOGTYPE_ACCESS);

		// save the params
		$this->params = $data;

		// add trace id to response
		if (isset($this->params['trace_id'])) {
			$this->response['trace_id'] = $this->params['trace_id'];
		}

		try {
			$this->authService->checkAuthentification();

			// set error handler to Shopgate's handler if requested
			if (!empty($this->params["use_errorhandler"])) {
				set_error_handler('ShopgateErrorHandler');
			}

			// check if an action to call has been passed, is known and enabled
			if (empty($this->params['action'])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ACTION, 'Passed parameters: '.var_export($data, true));
			}

			// check if the action is white-listed
			if (!in_array($this->params['action'], $this->actionWhitelist)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_ACTION, "'{$this->params['action']}'");
			}

			// check if action is enabled in the config
			$configArray = $this->config->toArray();
			if (empty($configArray['enable_'.$this->params['action']])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_DISABLED_ACTION, "'{$this->params['action']}'");
			}

			// call the action
			$action = $this->camelize($this->params['action']);
			$this->{$action}();
		} catch (ShopgateLibraryException $e) {
			$this->response['error'] = $e->getCode();
			$this->response['error_text'] = $e->getMessage();
		} catch (ShopgateMerchantApiException $e) {
			$this->response['error'] = ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED;
			$this->response['error_text'] = ShopgateLibraryException::getMessageFor(ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED).': "'.$e->getCode() . " - " . $e->getMessage().'"';
		} catch (Exception $e) {
			$message  = "";
			$message .= "Unknown Exception\n";
			$message .= "Exception: " . get_class( $e ) . "\n";
			$message .= "Code: " . $e->getCode() . "\n";
			$message .= "Message: " . $e->getMessage() . "\n";

			// new ShopgateLibraryException to build proper error message and perform logging
			$se = new ShopgateLibraryException($message);
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


	/******************************************************************
	 * Following methods represent the Shopgate Plugin API's actions: *
	 ******************************************************************/

	/**
	 * Represents the "ping" action.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_ping/de
	 */
	private function ping() {
		$this->response["pong"] = 'OK';

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
					SHOPGATE_BASE_DIR.'/config/config.php',
					SHOPGATE_BASE_DIR.'/config/myconfig.php',
					SHOPGATE_BASE_DIR.'/temp/',
					SHOPGATE_BASE_DIR.'/temp/cache/',
					SHOPGATE_BASE_DIR.'/temp/items.csv',
					SHOPGATE_BASE_DIR.'/temp/categories.csv',
					SHOPGATE_BASE_DIR.'/temp/reviews.csv',
			);

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

		// obfuscate data relevant for authentication
		$config = $this->config->toArray();
		$config['customer_number']	= ShopgateObject::OBFUSCATION_STRING;
		$config['shop_number']		= ShopgateObject::OBFUSCATION_STRING;
		$config['apikey']			= ShopgateObject::OBFUSCATION_STRING;

		// return the pong object
		header("Content-Type: application/json");
		$this->response["configuration"] = $config;
		$this->response["permissions"] = getPermissions();
		$this->response["php_version"] = phpversion();
		$this->response["php_config"] = getSettings();
		$this->response["php_curl"] = function_exists('curl_version') ? curl_version() : 'No PHP-CURL installed';
		$this->response["php_extensions"] = get_loaded_extensions();
		$this->response["shopgate_library_version"] = SHOPGATE_LIBRARY_VERSION;
		$this->response["plugin_version"] = defined("SHOPGATE_PLUGIN_VERSION") ? SHOPGATE_PLUGIN_VERSION : 'UNKNOWN';
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

		$orders = $this->merchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number'], 'with_items' => 1))->getData();
		if (empty($orders)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"order" not set. Response: '.var_export($_orders, true));
		}
		
		if (count($orders) > 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'more than one order in response. Response: '.var_export($_orders, true));
		}

		$this->response["external_order_number"] = $this->plugin->addOrder($orders[0]);
	}

	/**
	 * Represents the "update_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order/de
	 */
	private function updateOrder() {
		if (!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		$orders = $this->merchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number']))->getData();

		if (empty($orders)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"order" not set. Response: '.var_export($_orders, true));
		}

		if (count($orders) > 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'more than one order in response. Response: '.var_export($_orders, true));
		}
		
		$payment = 0;
		$shipping = 0;

		if (isset($this->params['payment'])) {
			$payment = (bool) $this->params['payment'];
		}
		if (isset($this->params['shipping'])) {
			$shipping = (bool) $this->params['shipping'];
		}

		$orders[0]->setUpdatePayment($payment);
		$orders[0]->setUpdateShipping($shipping);

		$this->response["external_order_number"] = $this->plugin->updateOrder($orders[0]);
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
		if (isset($this->params["limit"]) && isset($this->params["offset"])) {
			$this->plugin->exportLimit = (string) $this->params["limit"];
			$this->plugin->exportOffset = (string) $this->params["offset"];
			$this->plugin->splittedExport = true;
		}

		// generate / update items csv file if requested
		if ($this->config->getGenerateItemsCsvOnTheFly() || isset($this->params["generate_items_csv_on_the_fly"])) {
			$this->plugin->startGetItemsCsv();
		}

		$fileName = SHOPGATE_BASE_DIR.DS.'';
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
		ShopgateLibraryFactory::getInstance()->getPlugin()->startGetCategoriesCsv();

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
		ShopgateLibraryFactory::getInstance()->getPlugin()->startGetReviewsCsv();

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

class ShopgateMerchantApi extends ShopgateObject implements ShopgateMerchantApiInterface {
	/**
	 * @var ShopgateMerchantApi
	 */
	private static $singleton;

	/**
	 * @var bool Enforces instantion through getInstance()
	 */
	private static $singletonEnforcer = true;

	/**
	 * @var mixed[]
	 */
	private $config;

	/**
	 * @return ShopgateMerchantApi
	 */
	public static function getInstance() {
		self::$singletonEnforcer = false;

		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}

		self::$singletonEnforcer = true;

		return self::$singleton;
	}

	protected final function initLibrary() {
		if (!empty(self::$singletonEnforcer)) {
			trigger_error('Class '.__CLASS__.' is a singleton. Please call '.__CLASS__.'::getInstance() to get the singleton instance of the class.', E_USER_ERROR);
		}

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

		$this->log('Sending request to "'.$url.'": '.$this->cleanParamsForLog($data), ShopgateObject::LOGTYPE_REQUEST);

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
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		// check the result
		if (!$response) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_NO_CONNECTION);
		}

		$decodedResponse = $this->jsonDecode($response, true);

		if (empty($decodedResponse)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'Response: '.$response, true);
		}

		if ($decodedResponse['error'] != 0) {
			throw new ShopgateMerchantApiException($decodedResponse['error'], $decodedResponse['error_text']);
		}

		return $decodedResponse;
	}


	/********************************************************************
	 * Following methods represent the Shopgate Merchant API's actions: *
	 ********************************************************************/

	/********************************************************************
	 * Orders                                                           *
	 ********************************************************************/

	public function getOrders($parameters) {
		$data = array(
				'action' => "get_orders",
		);

		$data = array_merge($data, $parameters);
		$response = $this->sendRequest($data);

		if (!is_array($response["orders"])) {
			throw new ShopgateLibraryException(
					ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
					'"orders" is not an array. Response: '.var_export($response, true)
			);
		}

		$orders = array();
		foreach ($response["orders"] as $order) {
			$orders[] = new ShopgateOrder($order);
		}

		$oResponse = new ShopgateMerchantApiResponse($response);
		$oResponse->setData( $orders );

		return $oResponse;
	}

	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false) {
		$data = array(
				"action" => "add_order_delivery_note",
				"order_number" => $orderNumber,
				"shipping_service_id" => $shippingServiceId,
				"tracking_number" => (string) $trackingNumber,
				"mark_as_completed" => $markAsCompleted,
		);

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}

	public function setOrderShippingCompleted($orderNumber) {
		$data = array(
				'action' => 'set_order_shipping_completed',
				'order_number' => $orderNumber,
		);

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}

	public function getMobileRedirectKeywords(){
		$data = array(
				'action' => 'get_mobile_redirect_keywords',
		);

		$response = $this->sendRequest($data);

		return $response['keywords'];
	}

	/********************************************************************
	 * Items                                                            *
	 ********************************************************************/
	public function getItems($data) {
		$data['action'] = 'get_items';

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		if (!is_array($response["items"])) {
			throw new ShopgateLibraryException(
					ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
					'"items" is not an array. Response: '.var_export($response, true)
			);
		}

		$items = array();
		foreach($response["items"] as $_item) {
			$items[] = new ShopgateItem($_item);
		}

		$oResponse->setData($items);
		return $oResponse;
	}

	public function addItem($data) {
		$data['action'] = 'add_item';

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);
		return $oResponse;
	}

	public function updateItem($data) {
		$data['action'] = 'update_item';

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);
		return $oResponse;
	}

	public function deleteItem($item_number) {
		$data = array(
				'item_number' => $item_number,
				'action' => 'delete_item',
		);

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);
		return $oResponse;
	}

	/********************************************************************
	 * Categories                                                       *
	 ********************************************************************/
	public function getCategories($data) {
		$data['action'] = 'get_categories';

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		if (!is_array($response["categories"])) {
			throw new ShopgateLibraryException(
					ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
					'"categories" is not an array. Response: '.var_export($response, true)
			);
		}

		$aCategories = array();
		foreach($response["categories"] as $aCategory) {
			$aCategories[] = new ShopgateCategory($aCategory);

		}
		$oResponse->setData($aCategories);

		return $oResponse;
	}

	public function addCategory( $data ) {
		if($data instanceof ShopgateCategory) {
			$data = $data->toArray();
		}
		$data['action'] = 'add_category';

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}

	public function updateCategory($data) {
		if($data instanceof ShopgateCategory) {
			$data = $data->toArray();
		}
		$data['action'] = 'update_category';

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}

	public function deleteCategory($category_number, $delete_subcategories = false, $delete_items = false) {
		$data = array(
				'action' => 'delete_category',
				'category_number' => $category_number,
				'delete_subcategories' => $delete_subcategories ? 1 : 0,
				'delete_items' => $delete_items ? 1 : 0,
		);

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}

	public function addItemToCategory($item_number, $category_number, $order_index = null) {
		$data = array();
		$data['action'] = 'add_item_to_category';
		$data['category_number'] = $category_number;
		$data['item_number'] = $item_number;

		if(isset($order_index))
			$data['order_index'] = $order_index;

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}

	public function deleteItemFromCategory($item_number, $category_number) {
		$data = array();
		$data['action'] = 'delete_item_from_category';
		$data['category_number'] = $category_number;
		$data['item_number'] = $item_number;

		$response = $this->sendRequest($data);
		$oResponse = new ShopgateMerchantApiResponse($response);

		return $oResponse;
	}
}

class ShopgateAuthentificationService extends ShopgateObject implements ShopgateAuthentificationServiceInterface {
	/**
	 * @var ShopgateAuthentificationService
	 */
	private static $singleton;

	/**
	 * @var bool Enforces instantion through getInstance()
	 */
	private static $singletonEnforcer = true;

	private $customerNumber;
	private $apiKey;
	private $timestamp;

	protected final function initLibrary() {
		if (!empty(self::$singletonEnforcer)) {
			trigger_error('Class '.__CLASS__.' is a singleton. Please call '.__CLASS__.'::getInstance() to get the singleton instance of the class.', E_USER_ERROR);
		}

		$config = ShopgateConfig::getConfig();
		$this->customerNumber = $config["customer_number"];
		$this->apiKey = $config["apikey"];
		$this->timestamp = time();
	}

	/**
	 * @return ShopgateAuthentificationService
	 */
	public static function getInstance() {
		self::$singletonEnforcer = false;

		if (empty(self::$singleton)) {
			self::$singleton = new self();
		}

		self::$singletonEnforcer = true;

		return self::$singleton;
	}

	public function buildAuthUserHeader() {
		return self::HEADER_X_SHOPGATE_AUTH_USER .': '. $this->customerNumber.'-'.$this->timestamp;
	}

	public function buildAuthTokenHeader() {
		return self::HEADER_X_SHOPGATE_AUTH_TOKEN.': '.sha1("SMA-{$this->customerNumber}-{$this->timestamp}-{$this->apiKey}");
	}

	public function checkAuthentification() {
		if(defined("SHOPGATE_DEBUG") && SHOPGATE_DEBUG === 1) return;

		if (empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_USER]) || empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN])){
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'No authentication data present.');
		}

		// for convenience
		$name = $_SERVER[self::PHP_X_SHOPGATE_AUTH_USER];
		$token = $_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN];

		// extract customer number and timestamp from username
		$matches = array();
		if (!preg_match('/(?<customer_number>[1-9][0-9]+)-(?<timestamp>[1-9][0-9]+)/', $name, $matches)){
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Cannot parse: '.$name.'.');
		}

		// for convenience
		$customer_number = $matches["customer_number"];
		$timestamp = $matches["timestamp"];

		// request shouldn't be older than 30 minutes
		if ((time() - $timestamp) >= (30*60)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Request too old.');
		}

		// create the authentification-password
		$generatedPassword = sha1("SPA-{$customer_number}-{$timestamp}-{$this->apiKey}");

		// compare customer-number and auth-password
		if (($customer_number != $this->customerNumber) || ($token != $generatedPassword)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Invalid authentication data.');
		}
	}
}

/**
 * Shopgate Responsecontainer for MerchantApi requests
 *
 * Use the getData()-Function to get the received Data.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateMerchantApiResponse extends ShopgateObject {
	private $sma_version = null;
	private $trace_id = null;
	private $limit = 1;
	private $offset = 1;
	private $has_more_results = false;

	private $data = null;

	protected final function initLibrary($data = array()) {
		if (is_array($data)) {
			$methods = get_class_methods($this);
			foreach ($data as $key => $value) {
				$setter = 'set'.$this->camelize($key, true);
				if (!in_array($setter, $methods)) {
					continue;
				}
				$this->{$setter}($value);
			}
		}
	}

	/**
	 *
	 * @param $sma_version
	 */
	private function setSmaVersion($sma_version) {
		$this->sma_version = $sma_version;
	}

	/**
	 *
	 * @param $trace_id
	 */
	private function setTraceId($trace_id) {
		$this->trace_id = $trace_id;
	}

	/**
	 *
	 * @param $limit
	 */
	private function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 *
	 * @param $offset
	 */
	private function setOffset($offset) {
		$this->offset = $offset;
	}

	/**
	 *
	 * @param $has_more_results
	 */
	private function setHasMoreResults($has_more_results) {
		$this->has_more_results = $has_more_results;
	}

	/**
	 *
	 * @param $data
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * The Shopgate-Merchant-API-Version (SMA-Version)
	 *
	 * If Shopgate released a new API-Version the Version-Number increased
	 *
	 * @return string
	 */
	public function getSmaVersion() {
		return $this->sma_version;
	}

	/**
	 * The Trace-ID for the currend Request
	 *
	 * On Errors it will helb to find them
	 *
	 * @return string
	 */
	public function getTraceId() {
		return $this->trace_id;
	}

	/**
	 * The limit of the request
	 *
	 * @return integer
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * The offset of the request
	 *
	 * @return integer
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * Are there more results to fetch from Shopgate
	 *
	 * @return boolean
	 */
	public function getHasMoreResults() {
		return $this->has_more_results;
	}

	/**
	 * The received data
	 *
	 * @return ShopgateContainer|mixed
	 */
	public function getData() {
		return $this->data;
	}
}