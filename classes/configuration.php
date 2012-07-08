<?php
/**
 * Manages configuration for library _and_ plugin options.
 *
 * This class is used to save general library settings and specific settings for your plugin.
 *
 * To add your own specific settings
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateConfigInterface {
	const SHOPGATE_API_URL_LIVE = 'https://api.shopgate.com/merchant/';
	const SHOPGATE_API_URL_PG   = 'https://api.shopgatepg.com/merchant/';
	
	/**
	 * Tries to load the configuration from a file.
	 *
	 * If a $path is passed, this method tries to include the file. If that fails an exception is thrown.<br />
	 * <br />
	 * If $path is empty it tries to load .../shopgate_library/config/myconfig.php or if that fails,
	 * .../shopgate_library/config/config.php is tried to be loaded. If that fails too, an exception is
	 * thrown.<br />
	 * <br />
	 * The configuration file must be a PHP script defining an indexed array called $shopgate_config
	 * containing the desired configuration values to set. If that is not the case, an exception is thrown
	 *
	 * @param string $path The path to the configuration file or nothing to load the default Shopgate Library configuration files.
	 * @throws ShopgateLibraryException in case a configuration file could not be loaded or the $shopgate_config is not set.
	 */
	public function loadFile($path = null);
	
	/**
	 * Saves the desired configuration fields to the specified file or myconfig.php.
	 *
	 * This calls $this->loadFile() with the given $path to load the current configuration. In case that fails, the $shopgate_config
	 * array is initialized empty. The values defined in $fieldList are then validated (if desired), assigned to $shopgate_config and
	 * saved to the specified file or myconfig.php.
	 *
	 * In case the file cannot be (over)written or created, an exception with code ShopgateLibrary::CONFIG_READ_WRITE_ERROR is thrown.
	 *
	 * In case the validation fails for one or more fields, an exception with code ShopgateLibrary::CONFIG_ is thrown. The failed
	 * fields are appended as additional information in form of a comma-separated list.
	 *
	 * @param string[] $fieldList The list of fieldnames that should be saved to the configuration file.
	 * @param string $path The path to the configuration file or empty to use .../shopgate_library/config/myconfig.php.
	 * @param bool $validate True to validate the fields that should be set.
	 * @throws ShopgateLibraryException in case the configuration can't be loaded or saved.
	 */
	public function saveFile(array $fieldList, $path = null, $validate = true);
	
	/**
	 * Validates the configuration values.
	 *
	 * If $fieldList contains values, only these values will be validated. It it's empty, all values that have a validation
	 * rule will be validated.
	 *
	 * In case one or more validations fail an exception is thrown. The failed fields are appended as additonal information
	 * in form of a comma-separated list.
	 *
	 * @param string[] $fieldList The list of fields to be validated or empty, to validate all fields.
	 */
	public function validate(array $fieldList = array());
	
	/**
	 * @return string The name of the plugin / shop system the plugin is for.
	 */
	public function getPluginName();

	/**
	 * @return bool true to activate the Shopgate error handler.
	 */
	public function getUseCustomErrorHandler();

	/**
	 * @return int Shopgate customer number (at least 5 digits)
	 */
	public function getCustomerNumber();

	/**
	 * @return int Shopgate shop number (at least 5 digits)
	 */
	public function getShopNumber();
	
	/**
	 * @return string API key (exactly 20 hexadecimal digits)
	 */
	public function getApikey();
	
	/**
	 * @return string Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in between are ok)
	 */
	public function getAlias();
	
	/**
	 * @return string Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	public function getCname();
	
	/**
	 * @return string The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
	 */
	public function getServer();
	
	/**
	 * @return string If getServer() returns "live", ShopgateConfigInterface::SHOPGATE_API_URL_LIVE is returned.<br />
	 *                 If getServer() returns "pg", ShopgateConfigInterface::SHOPGATE_API_URL_PG is returned.<br />
	 *                 If getServer() returns "custom": A custom API url (empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters) is returned.<br />
	 *                 If getServer() returns a different value than the above, ShopgateConfigInterface::SHOPGATE_API_URL_LIVE is returned.
	 */
	public function getApiUrl();
	
	/**
	 * @return bool true to indicate a shop has been activated by Shopgate
	 */
	public function getShopIsActive();
	
	/**
	 * @return bool true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile header button)
	 */
	public function getAlwaysUseSsl();
	
	/**
	 * @return int (hours) The update period for keywords that identify mobile devices. Leave empty to download once and then always use the cached keywords
	 */
	public function getEnableRedirectKeywordUpdate();
	
	/**
	 * @return bool
	 */
	public function getEnablePing();
	
	/**
	 * @return bool
	 */
	public function getEnableAddOrder();
	
	/**
	 * @return bool
	 */
	public function getEnableUpdateOrder();
	
	/**
	 * @return bool
	 */
	public function getEnableGetOrders();
	
	/**
	 * @return bool
	 */
	public function getEnableGetCustomer();
	
	/**
	 * @return bool
	 */
	public function getEnableGetItemsCsv();
	
	/**
	 * @return bool
	 */
	public function getEnableGetCategoriesCsv();
	
	/**
	 * @return bool
	 */
	public function getEnableGetReviewsCsv();
	
	/**
	 * @return bool
	 */
	public function getEnableGetPagesCsv();
	
	/**
	 * @return bool
	 */
	public function getEnableGetLogFile();
	
	/**
	 * @return bool
	 */
	public function getEnableMobileWebsite();
	
	/**
	 * @return bool true to create the items CSV file on-the-fly the moment the API gets called.
	 */
	public function getGenerateItemsCsvOnTheFly();
	
	/**
	 * @return int The maximum number of attributes per product that are created. If the number is exceeded, attributes should be converted to options.
	 */
	public function getMaxAttributes();
	
	/**
	 * @return int The capacity (number of lines) of the buffer used for the export actions.
	 */
	public function getExportBufferCapacity();
	
	/**
	 * @return string The path to where the items CSV file is stored and retrieved from.
	 */
	public function getItemsCsvPath();
	
	/**
	 * @return string The path to where the categories CSV file is stored and retrieved from.
	 */
	public function getCategoriesCsvPath();
	
	/**
	 * @return string The path to where the reviews CSV file is stored and retrieved from.
	 */
	public function getReviewsCsvPath();
	
	/**
	 * @return string The path to where the pages CSV file is stored and retrieved from.
	 */
	public function getPagesCsvPath();
	
	/**
	 * @return string The path to the access log file.
	 */
	public function getAccessLogPath();
	
	/**
	 * @return string The path to the request log file.
	 */
	public function getRequestLogPath();
	
	/**
	 * @return string The path to the error log file.
	 */
	public function getErrorLogPath();
	
	/**
	 * @return string The path to the cache file for mobile device detection keywords.
	 */
	public function getRedirectKeywordCachePath();
	
	
	/**
	 * @param string $value The name of the plugin / shop system the plugin is for.
	 */
	public function setPluginName($value);
	
	/**
	 * @param bool $value true to activate the Shopgate error handler.
	 */
	public function setUseCustomErrorHandler($value);
	
	/**
	 * @param int $value Shopgate customer number (at least 5 digits)
	 */
	public function setCustomerNumber($value);
	
	/**
	 * @param int $value Shopgate shop number (at least 5 digits)
	 */
	public function setShopNumber($value);
	
	/**
	 * @param string $value API key (exactly 20 hexadecimal digits)
	 */
	public function setApikey($value);
	
	/**
	 * @param string $value Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in between are ok)
	 */
	public function setAlias($value);
	
	/**
	 * @param string $value Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	public function setCname($value);
	
	/**
	 * @param string $value The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
	 */
	public function setServer($value);
	
	/**
	 * @param string $value If $server is set to custom, Shopgate Merchant API calls will be made to this URL (empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	public function setApiUrl($value);
	
	/**
	 * @param bool $value true to indicate a shop has been activated by Shopgate
	 */
	public function setShopIsActive($value);
	
	/**
	 * @param bool $value true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile header button)
	 */
	public function setAlwaysUseSsl($value);
	
	/**
	 * @param bool $value (hours) The update period for keywords that identify mobile devices. Leave empty to download once and then always use the cached keywords
	 */
	public function setEnableRedirectKeywordUpdate($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnablePing($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableAddOrder($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableUpdateOrder($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetOrders($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetCustomer($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetItemsCsv($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetCategoriesCsv($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetReviewsCsv($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetPagesCsv($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetLogFile($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableMobileWebsite($value);
	
	/**
	 * @param bool $value true to create the items CSV file on-the-fly the moment the API gets called.
	 */
	public function setGenerateItemsCsvOnTheFly($value);
	
	/**
	 * @param int $value The maximum number of attributes per product that are created. If the number is exceeded, attributes should be converted to options.
	 */
	public function setMaxAttributes($value);

	/**
	 * @param int The capacity (number of lines) of the buffer used for the export actions.
	 */
	public function setExportBufferCapacity($value);
	
	/**
	 * @param string $value The path to where the items CSV file is stored and retrieved from.
	 */
	public function setItemsCsvPath($value);
	
	/**
	 * @param string $value The path to where the categories CSV file is stored and retrieved from.
	 */
	public function setCategoriesCsvPath($value);
	
	/**
	 * @param string $value The path to where the reviews CSV file is stored and retrieved from.
	 */
	public function setReviewsCsvPath($value);
	
	/**
	 * @param string $value The path to where the pages CSV file is stored and retrieved from.
	 */
	public function setPagesCsvPath($value);
	
	/**
	 * @param string $value The path to the access log file.
	 */
	public function setAccessLogPath($value);
	
	/**
	 * @param string $value The path to the request log file.
	 */
	public function setRequestLogPath($value);
	
	/**
	 * @param string $value The path to the error log file.
	 */
	public function setErrorLogPath($value);
	
	/**
	 * @param string $value The path to the cache file for mobile device detection keywords.
	 */
	public function setRedirectKeywordCachePath($value);
	
	/**
	 * Returns an additional setting.
	 *
	 * @param string $setting The name of the setting.
	 */
	public function returnAdditionalSetting($setting);
	
	/**
	 * Returns the additional settings array.
	 *
	 * The naming of this method doesn't follow the getter/setter naming convention because $this->additionalSettings
	 * is not a regular property.
	 *
	 * @return array<string, mixed> The additional settings a plugin may have defined.
	 */
	public function returnAdditionalSettings();
}

/**
 * Manages configuration for library _and_ plugin options.
 *
 * This class is used to save general library settings and specific settings for your plugin.
 *
 * To add your own specific settings
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateConfig extends ShopgateContainer implements ShopgateConfigInterface {
	/**
	 * @var array<string, string> List of field names (index) that must have a value according to its validation regex (value)
	 */
	protected $coreValidations = array(
		'customer_number' => '/^[0-9]{5,}$/', // at least 5 digits
		'shop_number' => '/^[0-9]{5,}$/', // at least 5 digits
		'apikey' => '/^[0-9a-f]{20}$/', // exactly 20 hexadecimal digits
		'alias' => '/^[0-9a-zA-Z]+(([\.]?|[\-]+)[0-9a-zA-Z]+)*$/', // start and end with alpha-numerical characters, multiple dashes and single dots in between are ok
		'server' => '/^(live|pg|custom)$/', // "live" or "pg" or "custom"
		'api_url' => '/^(https?:\/\/\S+)?$/', // empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters (this is used for testing only, thus the lose validation)
	);
	
	/**
	 * @var string The name of the plugin / shop system the plugin is for.
	 */
	protected $plugin_name;
	
	/**
	 * @var bool true to activate the Shopgate error handler.
	 */
	protected $use_custom_error_handler;
	
	
	##################################################################################
	### basic shop information necessary for use of the APIs, mobile redirect etc. ###
	##################################################################################
	/**
	 * @var int Shopgate customer number (at least 5 digits)
	 */
	protected $customer_number;
	
	/**
	 * @var int Shopgate shop number (at least 5 digits)
	 */
	protected $shop_number;
	
	/**
	 * @var string API key (exactly 20 hexadecimal digits)
	 */
	protected $apikey;
	
	/**
	 * @var string Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in between are ok)
	 */
	protected $alias;
	
	/**
	 * @var string Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	protected $cname;
	
	/**
	 * @var string The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
	 */
	protected $server;
	
	/**
	 * @var string If $server is set to custom, Shopgate Merchant API calls will be made to this URL (empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	protected $api_url;
	
	/**
	 * @var bool true to indicate a shop has been activated by Shopgate
	 */
	protected $shop_is_active;
	
	/**
	 * @var bool true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile header button)
	 */
	protected $always_use_ssl;
	
	/**
	 * @var bool true to enable updates of keywords that identify mobile devices
	 */
	protected $enable_redirect_keyword_update;
	
	
	##############################################################
	### Indicators to (de)activate Shopgate Plugin API actions ###
	##############################################################
	/**
	 * @var bool
	 */
	protected $enable_ping;
	
	/**
	 * @var bool
	 */
	protected $enable_add_order;
	
	/**
	 * @var bool
	 */
	protected $enable_update_order;
	
	/**
	 * @var bool
	 */
	protected $enable_get_orders;
	
	/**
	 * @var bool
	 */
	protected $enable_get_customer;
	
	/**
	 * @var bool
	 */
	protected $enable_get_items_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_categories_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_reviews_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_pages_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_log_file;
	
	/**
	 * @var bool
	 */
	protected $enable_mobile_website;
	
	
	#######################################################
	### Options regarding shop system specific settings ###
	#######################################################
	/**
	 * @var bool true to create the items CSV file on-the-fly the moment the API gets called.
	 */
	protected $generate_items_csv_on_the_fly;
	
	/**
	 * @var int The capacity (number of lines) of the buffer used for the export actions.
	 */
	protected $export_buffer_capacity;
	
	/**
	 * @var int The maximum number of attributes per product that are created. If the number is exceeded, attributes should be converted to options.
	 */
	protected $max_attributes;
	
	/**
	 * @var string The path to where the items CSV file is stored and retrieved from.
	 */
	protected $items_csv_path;
	
	/**
	 * @var string The path to where the categories CSV file is stored and retrieved from.
	 */
	protected $categories_csv_path;
	
	/**
	 * @var string The path to where the reviews CSV file is stored and retrieved from.
	 */
	protected $reviews_csv_path;
	
	/**
	 * @var string The path to where the pages CSV file is stored and retrieved from.
	 */
	protected $pages_csv_path;
	
	/**
	 * @var string The path to the access log file.
	 */
	protected $access_log_path;
	
	/**
	 * @var string The path to the request log file.
	 */
	protected $request_log_path;
	
	/**
	 * @var string The path to the error log file.
	 */
	protected $error_log_path;
	
	/**
	 * @var string The path to the cache file for mobile device detection keywords.
	 */
	protected $redirect_keyword_cache_path;

	/**
	 * @var array<string, mixed> Additional shop system specific settings that cannot (or should not) be generalized and thus be defined by a plugin itself.
	 */
	protected $additionalSettings = array();
	
	
	###################################################
	### Initialization, loading, saving, validating ###
	###################################################
	
	public final function __construct(array $data = array()) {
		// parent constructor not called on purpose, because we need special
		// initialization behaviour here (e.g. loading via array or file)
		
		// default values
		$this->plugin_name = 'not set';
		$this->use_custom_error_handler = 0;
		$this->customer_number = '12345';
		$this->shop_number = '12345';
		$this->apikey = '123456789abcdef01234';
		$this->alias = 'my-shop';
		$this->cname = '';
		$this->server = 'live';
		$this->api_url = '';
		$this->shop_is_active = 0;
		$this->always_use_ssl = 0;
		
		$this->enable_redirect_keyword_update = 0;
		$this->enable_ping = 1;
		$this->enable_add_order = 0;
		$this->enable_update_order = 0;
		$this->enable_get_orders = 0;
		$this->enable_get_customer = 0;
		$this->enable_get_items_csv = 0;
		$this->enable_get_categories_csv = 0;
		$this->enable_get_reviews_csv = 0;
		$this->enable_get_pages_csv = 0;
		$this->enable_get_log_file = 1;
		$this->enable_mobile_website = 0;
		
		$this->generate_items_csv_on_the_fly = 0;
		$this->export_buffer_capacity = 100;
		$this->max_attributes = 50;
		
		$this->items_csv_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'items.csv';
		$this->categories_csv_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'categories.csv';
		$this->reviews_csv_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'reviews.csv';
		$this->pages_csv_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'pages.csv';
		
		$this->access_log_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.'access.log';
		$this->request_log_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.'request.log';
		$this->error_log_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.'error.log';
		
		$this->redirect_keyword_cache_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'cache'.DS.'redirect_keywords.txt';
		
		// call possible sub classes' startup()
		$this->startup();
		
		$this->loadArray($data);
	}
	
	/**
	 * Inititialization for sub classes
	 * 
	 * This can be overwritten by subclasses to initialize further default values or overwrite the library defaults.
	 * It gets called after default value initialization of the library and befor initialization by file or array.
	 */
	protected function startup() {
		// nothing to do here
	}
	
	/**
	 * Tries to assign the values of an array to the configuration fields or load it from a file.
	 *
	 * This overrides ShopgateContainer::loadArray() which is called on object instantiation. It tries to assign
	 * the values of $data to the class attributes by $data's keys. If a key is not the name of a
	 * class attribute it's appended to $this->additionalSettings.<br />
	 * <br />
	 * If $data is empty or not an array, the method calls $this->loadFile().
	 *
	 * @param $data array<string, mixed> The data to be assigned to the configuration.
	 */
	protected function loadArray(array $data = array()) {
		// if no $data was passed try loading the default configuration file
		if (empty($data)) {
			$this->loadFile();
			return;
		}
		
		// if data was passed, map via setters
		$unmappedData = parent::loadArray($data);
		
		// put the rest into $this->additionalSettings
		$this->mapAdditionalSettings($unmappedData);
	}
	
	public function loadFile($path = null) {
		global $shopgate_config;
		
		// unset $shopgate_config to avoid reading from somehow injected global variables
		if (isset($shopgate_config)) {
			$shopgate_config = null;
		}
		
		// try loading files
		if (!empty($path)) {
			// try $path
			$success = $this->includeFile($path);
			
			if (!$success) {
				throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'The passed configuration file "'.$path.'" does not exist or does not define the $shopgate_config variable.');
			}
		} else {
			// try myconfig.php
			$success = $this->includeFile(SHOPGATE_BASE_DIR.DS.'config'.DS.'myconfig.php');
			
			// if unsuccessful, use default configuration values
			if (!$success) {
				return;
			}
		}
		
		// if we got here, we have a $shopgate_config to load
		$unmappedData = parent::loadArray($shopgate_config);
		$this->mapAdditionalSettings($unmappedData);
	}
	
	public function saveFile(array $fieldList, $path = null, $validate = true) {
		global $shopgate_config;
		
		// if desired, validate before doing anything else
		if ($validate) {
			$this->validate($fieldList);
		}
		
		// preserve values of the fields to save
		$saveFields = array();
		$currentConfig = $this->toArray();
		foreach ($fieldList as $field) {
			$saveFields[$field] = (isset($currentConfig[$field])) ? $currentConfig[$field] : null;
		}
		
		// load the current configuration file
		try {
			$this->loadFile($path);
		} catch (ShopgateLibraryException $e) {
			$shopgate_config = array();
		}
		
		// merge old config with new values
		$newConfig = array_merge($this->toArray(), $saveFields);
		
		// if necessary point $path to  myconfig.php
		if (empty($path)) {
			$path = SHOPGATE_BASE_DIR.DS.'config'.DS.'myconfig.php';
		}
		
		// create the array definition string and save it to the file
		$shopgateConfigFile = "<?php\n\$shopgate_config = ".var_export($newConfig, true).';';
		if (!@file_put_contents($path, $shopgateConfigFile)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'The configuration file "'.$path.'" could not be saved.');
		}
	}
	
	public final function validate(array $fieldList = array()) {
		$properties = $this->buildProperties();
		
		if (empty($fieldList)) {
			$coreFields = array_keys($properties);
			$additionalFields = array_keys($this->additionalSettings);
			$fieldList = array_merge($coreFields, $additionalFields);
		}
		
		$failedFields = array();
		foreach ($fieldList as $field) {
			if (empty($this->coreValidations[$field])) {
				continue;
			} else {
				if (!preg_match($this->coreValidations[$field], $properties[$field])) {
					$failedFields[] = $field;
				}
			}
		}
		
		if (!empty($failedFields)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_INVALID_VALUE, implode(',', $failedFields));
		}
	}
	
	
	###############
	### Getters ###
	###############
	public function getPluginName() {
		return $this->plugin_name;
	}
	
	public function getUseCustomErrorHandler() {
		return $this->use_custom_error_handler;
	}
	
	public function getCustomerNumber() {
		return $this->customer_number;
	}
	
	public function getShopNumber() {
		return $this->shop_number;
	}
	
	public function getApikey() {
		return $this->apikey;
	}
	
	public function getAlias() {
		return $this->alias;
	}
	
	public function getCname() {
		return $this->cname;
	}
	
	public function getServer() {
		return $this->server;
	}
	
	public function getApiUrl() {
		switch ($this->getServer()) {
			default: // fall through to 'live'
			case 'live':   return ShopgateConfigInterface::SHOPGATE_API_URL_LIVE;
			case 'pg':     return ShopgateConfigInterface::SHOPGATE_API_URL_PG;
			case 'custom': return $this->api_url;
		}
	}
	
	public function getShopIsActive() {
		return $this->shop_is_active;
	}
	
	public function getAlwaysUseSsl() {
		return $this->always_use_ssl;
	}
	
	public function getEnableRedirectKeywordUpdate() {
		return $this->enable_redirect_keyword_update;
	}
	
	public function getEnablePing() {
		return $this->enable_ping;
	}
	
	public function getEnableAddOrder() {
		return $this->enable_add_order;
	}
	
	public function getEnableUpdateOrder() {
		return $this->enable_update_order;
	}
	
	public function getEnableGetOrders() {
		return $this->enable_get_orders;
	}
	
	public function getEnableGetCustomer() {
		return $this->enable_get_customer;
	}
	
	public function getEnableGetItemsCsv() {
		return $this->enable_get_items_csv;
	}
	
	public function getEnableGetCategoriesCsv() {
		return $this->enable_get_categories_csv;
	}
	
	public function getEnableGetReviewsCsv() {
		return $this->enable_get_reviews_csv;
	}
	
	public function getEnableGetPagesCsv() {
		return $this->enable_get_pages_csv;
	}
	
	public function getEnableGetLogFile() {
		return $this->enable_get_log_file;
	}
	
	public function getEnableMobileWebsite() {
		return $this->enable_mobile_website;
	}
	
	public function getGenerateItemsCsvOnTheFly() {
		return $this->generate_items_csv_on_the_fly;
	}
	
	public function getExportBufferCapacity() {
		return $this->export_buffer_capacity;
	}
	
	public function getMaxAttributes() {
		return $this->max_attributes;
	}
	
	public function getItemsCsvPath() {
		return $this->items_csv_path;
	}
	
	public function getCategoriesCsvPath() {
		return $this->categories_csv_path;
	}
	
	public function getReviewsCsvPath() {
		return $this->reviews_csv_path;
	}
	
	public function getPagesCsvPath() {
		return $this->pages_csv_path;
	}
	
	public function getAccessLogPath() {
		return $this->access_log_path;
	}
	
	public function getRequestLogPath() {
		return $this->request_log_path;
	}
	
	public function getErrorLogPath() {
		return $this->error_log_path;
	}
	
	public function getRedirectKeywordCachePath() {
		return $this->redirect_keyword_cache_path;
	}
	
	
	###############
	### Setters ###
	###############
	public function setPluginName($value) {
		$this->plugin_name = $value;
	}
	
	public function setUseCustomErrorHandler($value) {
		$this->use_custom_error_handler = $value;
	}
	
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}
	
	public function setShopNumber($value) {
		$this->shop_number = $value;
	}
	
	public function setApikey($value) {
		$this->apikey = $value;
	}
	
	public function setAlias($value) {
		$this->alias = $value;
	}
	
	public function setCname($value) {
		$this->cname = $value;
	}
	
	public function setServer($value) {
		$this->server = $value;
	}
	
	public function setApiUrl($value) {
		$this->api_url = $value;
	}
	
	public function setShopIsActive($value) {
		$this->shop_is_active = $value;
	}
	
	public function setAlwaysUseSsl($value) {
		$this->always_use_ssl = $value;
	}
	
	public function setEnableRedirectKeywordUpdate($value) {
		$this->enable_redirect_keyword_update = $value;
	}
	
	public function setEnablePing($value) {
		$this->enable_ping = $value;
	}
	
	public function setEnableAddOrder($value) {
		$this->enable_add_order = $value;
	}
	
	public function setEnableUpdateOrder($value) {
		$this->enable_update_order = $value;
	}
	
	public function setEnableGetOrders($value) {
		$this->enable_get_orders = $value;
	}
	
	public function setEnableGetCustomer($value) {
		$this->enable_get_customer = $value;
	}
	
	public function setEnableGetItemsCsv($value) {
		$this->enable_get_items_csv = $value;
	}
	
	public function setEnableGetCategoriesCsv($value) {
		$this->enable_get_categories_csv = $value;
	}
	
	public function setEnableGetReviewsCsv($value) {
		$this->enable_get_reviews_csv = $value;
	}
	
	public function setEnableGetPagesCsv($value) {
		$this->enable_get_pages_csv = $value;
	}
	
	public function setEnableGetLogFile($value) {
		$this->enable_get_log_file = $value;
	}
	
	public function setEnableMobileWebsite($value) {
		$this->enable_mobile_website = $value;
	}
	
	public function setGenerateItemsCsvOnTheFly($value) {
		$this->generate_items_csv_on_the_fly = $value;
	}
	
	public function setExportBufferCapacity($value) {
		$this->export_buffer_capacity = $value;
	}
	
	public function setMaxAttributes($value) {
		$this->max_attributes = $value;
	}
	
	public function setItemsCsvPath($value) {
		$this->items_csv_path = $value;
	}
	
	public function setCategoriesCsvPath($value) {
		$this->categories_csv_path = $value;
	}
	
	public function setReviewsCsvPath($value) {
		$this->reviews_csv_path = $value;
	}
	
	public function setPagesCsvPath($value) {
		$this->pages_csv_path = $value;
	}
	
	public function setAccessLogPath($value) {
		$this->access_log_path = $value;
	}
	
	public function setRequestLogPath($value) {
		$this->request_log_path = $value;
	}
	
	public function setErrorLogPath($value) {
		$this->error_log_path = $value;
	}
	
	public function setRedirectKeywordCachePath($value) {
		$this->redirect_keyword_cache_path = $value;
	}
	
	
	###############
	### Helpers ###
	###############
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitConfig($this);
	}
	
	public function returnAdditionalSetting($setting) {
		return (isset($this->additionalSettings[$setting])) ? $this->additionalSettings[$setting] : null;
	}
	
	public function returnAdditionalSettings() {
		return $this->additionalSettings;
	}
	
	/**
	 * Tries to include the specified file and check for $shopgate_config.
	 *
	 * @param string $path The path to the configuration file.
	 * @return boolean true if the file was included and defined $shopgate_config, false otherwise
	 */
	private function includeFile($path) {
		global $shopgate_config;
		
		// unset $shopgate_config to avoid reading from somehow injected global variables
		if (isset($shopgate_config)) {
			unset($shopgate_config);
		}
		
		// try including the file
		if (file_exists($path)) {
			ob_start();
			include($path);
			ob_clean();
		} else {
			return false;
		}
		
		// check $shopgate_config
		if (!isset($shopgate_config) || !is_array($shopgate_config)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Maps the passed data to the additional settings array.
	 *
	 * @param array<string, mixed> $data The data to map.
	 */
	private function mapAdditionalSettings($data = array()) {
		foreach ($data as $key => $value) {
			$this->additionalSettings[$key] = $value;
		}
	}
	
	
	##################################
	### Deprecated / Compatibility ###
	##################################
	/**
	 * Routes static calls to ShopgateConfigOld (the former ShopgateConfig class).
	 *
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @param string $name Method name.
	 * @param mixed[] $arguments Arguments to call the method with.
	 * @return mixed The return value of the called method.
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld.
	 */
	public static function __callStatic($name, $arguments) {
		return call_user_func_array(array('ShopgateConfigOld', $name), $arguments);
	}
}

/**
 * Einstellungen für das Framework
 *
 * @version 1.0.0
 * @deprecated
 * @see ShopgateConfig
 */
class ShopgateConfigOld extends ShopgateObject {

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
		'alias' => 'my-shop',
		'cname' => '',
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
	 * @deprecated
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
	 *
	 * @deprecated
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
	 *
	 * @deprecated
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
	 *
	 * @deprecated
	 */
	public static final function getLogFilePath($type = ShopgateObject::LOGTYPE_ERROR) {
		switch (strtolower($type)) {
			default: $type = 'error';
			case "access": case "request": case "request":
		}

		if(isset(self::$config['path_to_'.strtolower($type).'_log_file'])) {
			return self::$config['path_to_'.strtolower($type).'_log_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/logs/'.strtolower($type).'.log';
		}
	}

	/**
	 * Gibt den Pfad zur items-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 *
	 * @deprecated
	 */
	public static final function getItemsCsvFilePath() {
		if(isset(self::$config['path_to_items_csv_file'])) {
			return self::$config['path_to_items_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/items.csv';
		}
	}

	/**
	 * @deprecated
	 */
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
	 *
	 * @deprecated
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
	 *
	 * @deprecated
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
	 * @deprecated
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

	/**
	 * @deprecated
	 * @throws ShopgateLibraryException
	 */
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