<?php
/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * File-based configuration management for library _and_ plugin options.
 *
 * This class is used to save general library settings and specific settings for your plugin.
 *
 * To add your own specific settings
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateConfig extends ShopgateContainer implements ShopgateConfigInterface
{
    /**
     * default xsd location url
     */
    const DEFAULT_XSD_URL_LOCATION = 'http://files.shopgate.com/xml/xsd';

    /**
     * @var string The path to the folder where the config file(s) are saved.
     * @deprecated 2.9.69 Use ShopgateConfig::buildConfigFilePath() instead.
     */
    protected $config_folder_path;

    /**
     * @var array<string, string> List of field names (index) that must have a value according to their validation
     *      regex (value)
     */
    protected $coreValidations = array(
        'customer_number' => '/^[0-9]{5,}$/',
        // at least 5 digits
        'shop_number'     => '/^[0-9]{5,}$/',
        // at least 5 digits
        'apikey'          => '/^[0-9a-f]{20}$/',
        // exactly 20 hexadecimal digits
        'alias'           => '/^[0-9a-zA-Z]+(([\.]?|[\-]+)[0-9a-zA-Z]+)*$/',
        // start and end with alpha-numerical characters, multiple dashes and single dots in between are ok
        'cname'           => '/^((http|https):\/\/)?((([\w-]+)[\.]?)*([\w-]+)\.(\w){2,})?(\/)?$/i',
        // empty or a string beginning with "http://" followed by any number of non-whitespace characters
        'server'          => '/^(live|pg|sl|custom)$/',
        // "live" or "pg" or "sl" or "custom"
        'api_url'         => '/^(https?:\/\/\S+)?$/i',
        // empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters (this is used for testing only, thus the lose validation)
    );

    /**
     * @var array<string, string> List of field names (index) that must have a value according to their validation
     *      regex (value)
     */
    protected $customValidations = array();

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
     * @var string Shopgate oauth access token
     */
    protected $oauth_access_token;

    //	/**
    //	 * @var int Class name for the authentication service, that is used for the Shopgate PluginAPI
    //	 */
    //	protected $spa_auth_service_class_name;

    /**
     * @var string Class name for the authentication service, that is used for the Shopgate MerchantAPI
     */
    protected $sma_auth_service_class_name;

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
     * @var string Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in
     *      between are ok)
     */
    protected $alias;

    /**
     * @var string Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://"
     *      followed by any number of non-whitespace characters)
     */
    protected $cname;

    /**
     * @var string The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
     */
    protected $server;

    /**
     * @var array<string, array<string, string>> api url map for server and authentication service type
     */
    protected $api_urls = array(
        'live' => array(
            ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE => ShopgateConfigInterface::SHOPGATE_API_URL_LIVE,
            ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH    => ShopgateConfigInterface::SHOPGATE_API_URL_LIVE_OAUTH,
        ),
        'pg'   => array(
            ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE => ShopgateConfigInterface::SHOPGATE_API_URL_PG,
            ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH    => ShopgateConfigInterface::SHOPGATE_API_URL_PG_OAUTH,
        ),
        'sl'   => array(
            ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE => ShopgateConfigInterface::SHOPGATE_API_URL_SL,
            ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH    => ShopgateConfigInterface::SHOPGATE_API_URL_SL_OAUTH,
        ),
    );

    /**
     * @var string If $server is set to custom, Shopgate Merchant API calls will be made to this URL (empty or a string
     *      beginning with "http://" or "https://" followed by any number of non-whitespace characters)
     */
    protected $api_url;

    /**
     * @var bool true to indicate a shop has been activated by Shopgate
     */
    protected $shop_is_active;

    /**
     * @var bool true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile
     *      header button). Content provided by Shopgate is always available via HTTPS
     */
    protected $always_use_ssl;

    /**
     * @var bool true to enable updates of keywords that identify mobile devices
     */
    protected $enable_redirect_keyword_update;

    /**
     * @var bool true to enable default redirect for mobile devices from content sites to mobile website (welcome page)
     */
    protected $enable_default_redirect;

    /**
     * @var string the encoding the shop system is using internally
     */
    protected $encoding;

    /**
     * @var bool true to enable automatic encoding conversion to utf-8 during export
     */
    protected $export_convert_encoding;

    /**
     * @var bool if true forces the $encoding to be the only one source encoding for all encoding operations
     */
    protected $force_source_encoding;

    /**
     * @var string[] the list of fields supported by the plugin method check_cart
     */
    protected $supported_fields_check_cart;

    /**
     * @var string[] the list of fields supported by the plugin method get_settings
     */
    protected $supported_fields_get_settings;

    /**
     * @var string[] the list of methods supported by the cron action
     */
    protected $supported_methods_cron;

    /**
     * @var array<string, string[]> the list of response types supported by the plugin, indexed by actions
     */
    protected $supported_response_types;

    ##############################################################
    ### Indicators to (de)activate Shopgate Plugin API actions ###
    ##############################################################
    /**
     * @var int
     */
    protected $enable_ping;

    /**
     * @var int
     */
    protected $enable_add_order;

    /**
     * @var int
     */
    protected $enable_update_order;

    /**
     * @var int
     */
    protected $enable_check_cart;

    /**
     * @var int
     */
    protected $enable_check_stock;

    /**
     * @var int
     */
    protected $enable_redeem_coupons;

    /**
     * @var int
     */
    protected $enable_get_orders;

    /**
     * @var int
     */
    protected $enable_get_customer;

    /**
     * @var int
     */
    protected $enable_register_customer;

    /**
     * @var int
     */
    protected $enable_get_debug_info;

    /**
     * @var int
     */
    protected $enable_get_items;

    /**
     * @var int
     */
    protected $enable_get_items_csv;

    /**
     * @var int
     */
    protected $enable_get_categories_csv;

    /**
     * @var int
     */
    protected $enable_get_categories;

    /**
     * @var int
     */
    protected $enable_get_reviews_csv;

    /**
     * @var int
     */
    protected $enable_get_reviews;

    /**
     * @var int
     */
    protected $enable_get_media_csv;

    /**
     * @var int
     */
    protected $enable_get_log_file;

    /**
     * @var int
     */
    protected $enable_mobile_website;

    /**
     * @var int
     */
    protected $enable_cron;

    /**
     * @var int
     */
    protected $enable_clear_log_file;

    /**
     * @var int
     */
    protected $enable_clear_cache;

    /**
     * @var int
     */
    protected $enable_get_settings;

    /**
     * @var int
     */
    protected $enable_set_settings;

    /**
     * @var int
     */
    protected $enable_sync_favourite_list;

    /**
     * @var int
     */
    protected $enable_receive_authorization;

    #######################################################
    ### Options regarding shop system specific settings ###
    #######################################################
    /**
     * @var string The ISO 3166 ALPHA-2 code of the country the plugin uses for export.
     */
    protected $country;

    /**
     * @var string The ISO 639 code of the language the plugin uses for export.
     */
    protected $language;

    /**
     * @var string The ISO 4217 code of the currency the plugin uses for export.
     */
    protected $currency;

    /**
     * @var string CSS style identifier for the parent element the Mobile Header should be attached to.
     */
    protected $mobile_header_parent;

    /**
     * @var bool True to insert the Mobile Header as first child element, false to append it.
     */
    protected $mobile_header_prepend;

    /**
     * @var int The capacity (number of lines) of the buffer used for the export actions.
     */
    protected $export_buffer_capacity;

    /**
     * @var int The maximum number of attributes per product that are created. If the number is exceeded, attributes
     *      should be converted to options.
     */
    protected $max_attributes;

    /**
     * @var string The path to the folder where the export CSV files are stored and retrieved from.
     */
    protected $export_folder_path;

    /**
     * @var string The path to the folder where the log files are stored and retrieved from.
     */
    protected $log_folder_path;

    /**
     * @var string The path to the folder where cache files are stored and retrieved from.
     */
    protected $cache_folder_path;

    /**
     * @var string The name of the items CSV file.
     */
    protected $items_csv_filename;

    /**
     * @var string The name of the items XML file.
     */
    protected $items_xml_filename;

    /**
     * @var string The name of the items JSON file.
     */
    protected $items_json_filename;

    /**
     * @var string The name of the items CSV file.
     */
    protected $media_csv_filename;

    /**
     * @var string The name of the categories CSV file.
     */
    protected $categories_csv_filename;

    /**
     * @var string The name of the categories XML file.
     */
    protected $categories_xml_filename;

    /**
     * @var string The name of the categories JSON file.
     */
    protected $categories_json_filename;

    /**
     * @var string The name of the reviews CSV file.
     */
    protected $reviews_csv_filename;

    /**
     * @var string The name of the reviews XML file.
     */
    protected $reviews_xml_filename;

    /**
     * @var string The name of the reviews JSON file.
     */
    protected $reviews_json_filename;

    /**
     * @var string The name of the access log file.
     */
    protected $access_log_filename;

    /**
     * @var string The name of the request log file.
     */
    protected $request_log_filename;

    /**
     * @var string The name of the error log file.
     */
    protected $error_log_filename;

    /**
     * @var string The name of the debug log file.
     */
    protected $debug_log_filename;

    /**
     * @var string The name of the cache file for mobile device detection keywords.
     */
    protected $redirect_keyword_cache_filename;

    /**
     * @var string The name of the cache file for mobile device skip detection keywords.
     */
    protected $redirect_skip_keyword_cache_filename;

    /**
     * @var bool True if the plugin is an adapter between Shopgate's and a third-party-API and servers multiple shops
     *      on both ends.
     */
    protected $is_shopgate_adapter;

    /**
     * @var array<string, mixed> Additional shop system specific settings that cannot (or should not) be generalized
     *      and thus be defined by a plugin itself.
     */
    protected $additionalSettings = array();

    /**
     * @var array<int, string> an array with a list of get params which are allowed to passthrough to the mobile device
     *      on redirect
     */
    protected $redirectable_get_params = array();

    /**
     * @var string A JSON encoded string containing the HTML tags to be placed on the desktop website.
     */
    protected $html_tags;

    /**
     * @var int execution time limit for file export in seconds
     */
    protected $default_execution_time;

    /**
     * @var int memory limit in MB
     */
    protected $default_memory_limit;

    /**
     * @var array list of items which should be excluded from the item export
     */
    protected $exclude_item_ids = array();

    /**
     * @var int the facebook pixel ID, configurable in the merchant area and automatically sent to the plugin
     */
    protected $facebook_pixel_id = null;

    /** @var array */
    protected $cronJobWhiteList;

    ###################################################
    ### Initialization, loading, saving, validating ###
    ###################################################

    /** @noinspection PhpMissingParentConstructorInspection */
    final public function __construct(array $data = array())
    {
        // parent constructor not called on purpose, because we need special
        // initialization behaviour here (e.g. loading via array or file)

        // default values
        $this->plugin_name                    = 'not set';
        $this->use_custom_error_handler       = 0;
        $this->customer_number                = null;
        $this->shop_number                    = null;
        $this->apikey                         = null;
        $this->alias                          = 'my-shop';
        $this->cname                          = '';
        $this->server                         = 'live';
        $this->api_url                        = '';
        $this->shop_is_active                 = 0;
        $this->always_use_ssl                 = 1; // default should be 1, no exceptions should be made for production systems!
        $this->enable_redirect_keyword_update = 0;
        $this->enable_default_redirect        = 0;
        $this->encoding                       = 'UTF-8';
        $this->export_convert_encoding        = 1;
        $this->force_source_encoding          = false;
        $this->supported_fields_check_cart    = array();
        $this->supported_fields_get_settings  = array();
        $this->supported_methods_cron         = array();
        $this->supported_response_types       = array(
            'get_items'      => array('xml'),
            'get_categories' => array('xml'),
            'get_reviews'    => array('xml'),
        );
        $this->enable_ping                    = 1;
        $this->enable_add_order               = 0;
        $this->enable_update_order            = 0;
        $this->enable_check_cart              = 0;
        $this->enable_check_stock             = 0;
        $this->enable_redeem_coupons          = 0;
        $this->enable_get_orders              = 0;
        $this->enable_get_customer            = 0;
        $this->enable_get_debug_info          = 0;
        $this->enable_get_items_csv           = 0;
        $this->enable_get_items               = 0;
        $this->enable_get_categories          = 0;
        $this->enable_get_media_csv           = 0;
        $this->enable_get_categories_csv      = 0;
        $this->enable_get_reviews             = 0;
        $this->enable_get_reviews_csv         = 0;
        $this->enable_get_log_file            = 1;
        $this->enable_mobile_website          = 0;
        $this->enable_cron                    = 0;
        $this->enable_clear_log_file          = 1;
        $this->enable_clear_cache             = 1;
        $this->enable_get_settings            = 0;
        $this->enable_set_settings            = 1;
        $this->enable_register_customer       = 0;
        $this->enable_sync_favourite_list     = 0;
        $this->enable_receive_authorization   = 0;

        $this->sma_auth_service_class_name = ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE;

        $this->country  = 'DE';
        $this->language = 'de';
        $this->currency = 'EUR';

        $this->mobile_header_parent  = 'body';
        $this->mobile_header_prepend = true;

        $this->export_buffer_capacity = 100;
        $this->max_attributes         = 50;

        /** @noinspection PhpDeprecationInspection */
        $this->config_folder_path = SHOPGATE_BASE_DIR . DS . 'config';

        $this->export_folder_path = SHOPGATE_BASE_DIR . DS . 'temp';
        $this->log_folder_path    = SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs';
        $this->cache_folder_path  = SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'cache';

        $this->items_csv_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'items.csv';
        $this->items_xml_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'items.xml';
        $this->items_json_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'items.json';

        $this->media_csv_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'media.csv';

        $this->categories_csv_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'categories.csv';
        $this->categories_xml_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'categories.xml';
        $this->categories_json_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'categories.json';

        $this->reviews_csv_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'reviews.csv';
        $this->reviews_xml_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'reviews.xml';
        $this->reviews_json_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'reviews.json';

        $this->access_log_filename  = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'access.log';
        $this->request_log_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'request.log';
        $this->error_log_filename   = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'error.log';
        $this->debug_log_filename   = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'debug.log';

        $this->redirect_keyword_cache_filename      = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'redirect_keywords.txt';
        $this->redirect_skip_keyword_cache_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX . 'skip_redirect_keywords.txt';

        $this->is_shopgate_adapter     = false;
        $this->redirectable_get_params = array(
            'gclid',
            'gclsrc',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'courier',
            'trackingNo',
            'lang',
        );
        $this->html_tags               = '';

        $this->cronJobWhiteList = array(
            ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
            ShopgatePluginApi::JOB_CLEAN_ORDERS,
            ShopgatePluginApi::JOB_CANCEL_ORDERS,
        );

        $this->default_memory_limit   = ShopgateConfigInterface::DEFAULT_MEMORY_LIMIT;
        $this->default_execution_time = ShopgateConfigInterface::DEFAULT_EXECUTION_TIME;

        // call possible sub class' startup()
        if (!$this->startup()) {
            $this->loadArray($data);
        }
    }

    /**
     * Inititialization for sub classes
     *
     * This can be overwritten by subclasses to initialize further default values or overwrite the library defaults.
     * It gets called after default value initialization of the library and before initialization by file or array.
     *
     * @return bool false if initialization should be done by ShopgateConfig, true if it has already been done.
     */
    protected function startup()
    {
        // nothing to do here
        return false;
    }

    public function buildConfigFilePath($fileName = self::DEFAULT_CONFIGURATION_FILE_NAME)
    {
        /** @noinspection PhpDeprecationInspection */
        return $this->config_folder_path . DS . $fileName;
    }

    public function load(array $settings = null)
    {
        $this->loadArray($settings);
    }

    /**
     * returns the current xsd location
     *
     * @return string
     */
    public static function getCurrentXsdLocation()
    {
        return ShopgateConfig::DEFAULT_XSD_URL_LOCATION;
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
     *
     * @return void
     */
    public function loadArray(array $data = array())
    {
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
     * @param string $path The path to the configuration file or nothing to load the default Shopgate Cart Integration
     *                     SDK configuration files.
     *
     * @throws ShopgateLibraryException in case a configuration file could not be loaded or the $shopgate_config is not
     *                                  set.
     */
    public function loadFile($path = null)
    {
        $config = null;

        // try loading files
        if (!empty($path) && file_exists($path)) {
            // try $path
            $config = $this->includeFile($path);

            if (!$config) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::CONFIG_READ_WRITE_ERROR,
                    'The passed configuration file "' . $path . '" does not exist or does not define the $shopgate_config variable.'
                );
            }
        } else {
            // try myconfig.php
            $config = $this->includeFile($this->buildConfigFilePath());

            // if unsuccessful, use default configuration values
            if (!$config) {
                return;
            }
        }

        // if we got here, we have a $shopgate_config to load
        $unmappedData = parent::loadArray($config);
        $this->mapAdditionalSettings($unmappedData);
    }

    /**
     * Loads the configuration file for a given Shopgate shop number.
     *
     * @param string $shopNumber The shop number.
     *
     * @throws ShopgateLibraryException in case the $shopNumber is empty or no configuration file can be found.
     */
    public function loadByShopNumber($shopNumber)
    {
        if (empty($shopNumber) || !preg_match($this->coreValidations['shop_number'], $shopNumber)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_READ_WRITE_ERROR,
                'configuration file cannot be found without shop number'
            );
        }

        // find all config files
        $configFile = null;
        $files      = scandir(dirname($this->buildConfigFilePath()));
        ob_start();
        foreach ($files as $file) {
            if (!is_file($this->buildConfigFilePath($file))) {
                continue;
            }

            $shopgate_config = null;
            /** @noinspection PhpIncludeInspection */
            include($this->buildConfigFilePath($file));
            if (isset($shopgate_config) && isset($shopgate_config['shop_number'])
                && ($shopgate_config['shop_number'] == $shopNumber)) {
                $configFile = $this->buildConfigFilePath($file);
                break;
            }
        }
        ob_end_clean();
        if (empty($configFile)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_READ_WRITE_ERROR,
                'no configuration file found for shop number "' . $shopNumber . '"',
                true,
                false
            );
        }

        $this->loadFile($configFile);
        $this->initFileNames();
    }

    /**
     * Loads the configuration file by a given language or the global configuration file.
     *
     * @param string|null $language the ISO-639 code of the language or null to load global configuration
     *
     * @throws ShopgateLibraryException
     */
    public function loadByLanguage($language)
    {
        if (!is_null($language) && !preg_match('/[a-z]{2}/', $language)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_READ_WRITE_ERROR,
                'invalid language code "' . $language . '"',
                true,
                false
            );
        }

        $this->loadFile($this->buildConfigFilePath('myconfig-' . $language . '.php'));
        $this->initFileNames();
    }

    /**
     * Sets the file names according to the language of the configuration.
     */
    protected function initFileNames()
    {
        $this->items_csv_filename  = 'items-' . $this->language . '.csv';
        $this->items_xml_filename  = 'items-' . $this->language . '.xml';
        $this->items_json_filename = 'items-' . $this->language . '.json';

        $this->media_csv_filename = 'media-' . $this->language . '.csv';

        $this->categories_csv_filename  = 'categories-' . $this->language . '.csv';
        $this->categories_xml_filename  = 'categories-' . $this->language . '.xml';
        $this->categories_json_filename = 'categories-' . $this->language . '.json';

        $this->reviews_csv_filename = 'reviews-' . $this->language . '.csv';

        $this->access_log_filename  = 'access-' . $this->language . '.log';
        $this->request_log_filename = 'request-' . $this->language . '.log';
        $this->error_log_filename   = 'error-' . $this->language . '.log';
        $this->debug_log_filename   = 'debug-' . $this->language . '.log';

        $this->redirect_keyword_cache_filename      = 'redirect_keywords-' . $this->language . '.txt';
        $this->redirect_skip_keyword_cache_filename = 'skip_redirect_keywords-' . $this->language . '.txt';
    }

    public function save(array $fieldList, $validate = true)
    {
        if ($this->checkUseGlobalFor($this->language)) {
            $this->saveFile($fieldList, null, $validate);
        } else {
            $this->saveFileForLanguage($fieldList, $this->language, $validate);
        }
    }

    /**
     * Saves the desired configuration fields to the specified file or myconfig.php.
     *
     * This calls $this->loadFile() with the given $path to load the current configuration. In case that fails, the
     * $shopgate_config array is initialized empty. The values defined in $fieldList are then validated (if desired),
     * assigned to $shopgate_config and saved to the specified file or myconfig.php.
     *
     * In case the file cannot be (over)written or created, an exception with code
     * ShopgateLibrary::CONFIG_READ_WRITE_ERROR is thrown.
     *
     * In case the validation fails for one or more fields, an exception with code
     * ShopgateLibrary::CONFIG_INVALID_VALUE is thrown. The failed fields are appended as additional information in
     * form of a comma-separated list.
     *
     * @param string[] $fieldList The list of fieldnames that should be saved to the configuration file.
     * @param string   $path      The path to the configuration file or empty to use
     *                            .../shopgate_library/config/myconfig.php.
     * @param bool     $validate  True to validate the fields that should be set.
     *
     * @throws ShopgateLibraryException in case the configuration can't be loaded or saved.
     */
    public function saveFile(array $fieldList, $path = null, $validate = true)
    {
        // if desired, validate before doing anything else
        if ($validate) {
            $this->validate($fieldList);
        }

        // preserve values of the fields to save
        $saveFields    = array();
        $currentConfig = $this->toArray();
        foreach ($fieldList as $field) {
            $saveFields[$field] = (isset($currentConfig[$field]))
                ? $currentConfig[$field]
                : null;
        }

        // load the current configuration file
        try {
            $this->loadFile($path);
        } catch (ShopgateLibraryException $e) {
            ShopgateLogger::getInstance()->log(
                '-- Don\'t worry about the "error reading or writing configuration", that was just a routine check during saving.'
            );
        }

        // merge old config with new values
        $newConfig = array_merge($this->toArray(), $saveFields);

        // default if no path to the configuration file is set
        if (empty($path)) {
            $path = $this->buildConfigFilePath();
        }

        // create the array definition string and save it to the file
        $shopgateConfigFile = "<?php\n\$shopgate_config = " . var_export($newConfig, true) . ';';
        if (!@file_put_contents($path, $shopgateConfigFile)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_READ_WRITE_ERROR,
                'The configuration file "' . $path . '" could not be saved.'
            );
        }
    }

    /**
     * Saves the desired fields to the configuration file for a given language or global configuration
     *
     * @param string[] $fieldList the list of fieldnames that should be saved to the configuration file.
     * @param string   $language  the ISO-639 code of the language or null to save to global configuration
     * @param bool     $validate  true to validate the fields that should be set.
     *
     * @throws ShopgateLibraryException in case the configuration can't be loaded or saved.
     */
    public function saveFileForLanguage(array $fieldList, $language = null, $validate = true)
    {
        $fileName = null;
        if (!is_null($language)) {
            $this->setLanguage($language);
            $fieldList[] = 'language';
            $fileName    = $this->buildConfigFilePath('myconfig-' . $language . '.php');
        }

        $this->saveFile($fieldList, $fileName, $validate);
    }

    /**
     * Checks for duplicate shop numbers in multiple configurations.
     *
     * This checks all files in the configuration folder and shop numbers in all
     * configuration files.
     *
     * @return bool true if there are duplicates, false otherwise.
     */
    public function checkDuplicates()
    {
        $shopNumbers = array();
        $files       = scandir(dirname($this->buildConfigFilePath()));

        foreach ($files as $file) {
            if (!is_file($this->buildConfigFilePath($file))) {
                continue;
            }

            $shopgate_config = null;
            /** @noinspection PhpIncludeInspection */
            include($this->buildConfigFilePath($file));
            if (isset($shopgate_config) && isset($shopgate_config['shop_number'])) {
                if (in_array($shopgate_config['shop_number'], $shopNumbers)) {
                    return true;
                } else {
                    $shopNumbers[] = $shopgate_config['shop_number'];
                }
            }
        }

        return false;
    }

    /**
     * Checks if there is more than one configuration file available.
     *
     * @return bool true if multiple configuration files are available, false otherwise.
     */
    public function checkMultipleConfigs()
    {
        $files   = scandir(dirname($this->buildConfigFilePath()));
        $counter = 0;

        foreach ($files as $file) {
            if (!is_file($this->buildConfigFilePath($file))) {
                continue;
            }

            if (substr($file, -4) !== '.php') {
                continue;
            }

            ob_start();
            /** @noinspection PhpIncludeInspection */
            include($this->buildConfigFilePath($file));
            ob_end_clean();
            if (!isset($shopgate_config)) {
                continue;
            }

            $counter++;
            unset($shopgate_config);
        }

        return ($counter > 1);
    }

    /**
     * Checks if the global a configuration file should be used for the language requested.
     *
     * @param string $language the ISO-639 code of the language
     *
     * @return bool true if global configuration should be used, false if the language has separate configuration
     */
    public function checkUseGlobalFor($language)
    {
        return !file_exists($this->buildConfigFilePath('myconfig-' . $language . '.php'));
    }

    /**
     * Removes the configuration file for the language requested.
     *
     * @param string $language the ISO-639 code of the language or null to load global configuration
     *
     * @throws ShopgateLibraryException in case the file exists but cannot be deleted.
     */
    public function useGlobalFor($language)
    {
        $fileName = $this->buildConfigFilePath('myconfig-' . $language . '.php');
        if (file_exists($fileName)) {
            if (!@unlink($fileName)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::CONFIG_READ_WRITE_ERROR,
                    'Error deleting configuration file "' . $fileName . "'."
                );
            }
        }
    }

    final public function validate(array $fieldList = array())
    {
        $properties = $this->buildProperties();

        if (empty($fieldList)) {
            $coreFields       = array_keys($properties);
            $additionalFields = array_keys($this->additionalSettings);
            $fieldList        = array_merge($coreFields, $additionalFields);
        }

        $validations  = array_merge($this->customValidations, $this->coreValidations);
        $failedFields = array();
        foreach ($fieldList as $field) {
            if (empty($validations[$field]) || preg_match($validations[$field], $properties[$field])) {
                continue;
            } else {
                $failedFields[] = $field;
            }
        }

        // run custom validations
        $failedCustomFields = $this->validateCustom($fieldList);
        if (!empty($failedCustomFields) && is_array($failedCustomFields)) {
            $failedFields = array_merge($failedCustomFields, $failedFields);
        }

        if (!empty($failedFields)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_INVALID_VALUE,
                implode(',', $failedFields)
            );
        }
    }

    /**
     * Validates the configuration values.
     *
     * @param string[] $fieldList The list of fields to be validated.
     *
     * @return string[] The list of fields that failed validation or an empty array if validation was successful.
     */
    protected function validateCustom(
        /** @noinspection PhpUnusedParameterInspection */
        array $fieldList = array()
    ) {
        return array();
    }


    ###############
    ### Getters ###
    ###############
    public function getPluginName()
    {
        return $this->plugin_name;
    }

    public function getUseCustomErrorHandler()
    {
        return $this->use_custom_error_handler;
    }

    //	public function getSpaAuthServiceClassName() {
    //		return $this->spa_auth_service_class_name;
    //	}

    public function getSmaAuthServiceClassName()
    {
        return $this->sma_auth_service_class_name;
    }

    public function getOauthAccessToken()
    {
        return $this->oauth_access_token;
    }

    public function getCustomerNumber()
    {
        return $this->customer_number;
    }

    public function getShopNumber()
    {
        return $this->shop_number;
    }

    public function getApikey()
    {
        return $this->apikey;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getCname()
    {
        return rtrim($this->cname, '/');
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getApiUrls()
    {
        return $this->api_urls;
    }

    public function getApiUrl()
    {
        switch ($this->server) {
            default: // fall through to 'live'
            case 'live':
            case 'sl':
            case 'pg':
                return $this->api_urls[$this->server][$this->sma_auth_service_class_name];
            case 'custom':
                return $this->api_url;
        }
    }

    public function getShopIsActive()
    {
        return $this->shop_is_active;
    }

    public function getAlwaysUseSsl()
    {
        return $this->always_use_ssl;
    }

    public function getEnableRedirectKeywordUpdate()
    {
        return $this->enable_redirect_keyword_update;
    }

    public function getEnableDefaultRedirect()
    {
        return $this->enable_default_redirect;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function getExportConvertEncoding()
    {
        return $this->export_convert_encoding;
    }

    public function getForceSourceEncoding()
    {
        return $this->force_source_encoding;
    }

    public function getSupportedFieldsCheckCart()
    {
        return $this->supported_fields_check_cart;
    }

    public function getSupportedFieldsGetSettings()
    {
        return $this->supported_fields_get_settings;
    }

    public function getSupportedMethodsCron()
    {
        return $this->supported_methods_cron;
    }

    public function getSupportedResponseTypes()
    {
        return $this->supported_response_types;
    }

    public function getEnablePing()
    {
        return (int)$this->enable_ping;
    }

    public function getEnableAddOrder()
    {
        return (int)$this->enable_add_order;
    }

    public function getEnableUpdateOrder()
    {
        return (int)$this->enable_update_order;
    }

    public function getEnableCheckCart()
    {
        return (int)$this->enable_check_cart;
    }

    public function getEnableCheckStock()
    {
        return (int)$this->enable_check_stock;
    }

    public function getEnableRedeemCoupons()
    {
        return (int)$this->enable_redeem_coupons;
    }

    public function getEnableGetOrders()
    {
        return (int)$this->enable_get_orders;
    }

    public function getEnableGetCustomer()
    {
        return (int)$this->enable_get_customer;
    }

    public function getEnableRegisterCustomer()
    {
        return (int)$this->enable_register_customer;
    }

    public function getEnableGetDebugInfo()
    {
        return (int)$this->enable_get_debug_info;
    }

    public function getEnableGetItemsCsv()
    {
        return (int)$this->enable_get_items_csv;
    }

    public function getEnableGetItems()
    {
        return (int)$this->enable_get_items;
    }

    public function getEnableGetCategoriesCsv()
    {
        return (int)$this->enable_get_categories_csv;
    }

    public function getEnableGetCategories()
    {
        return (int)$this->enable_get_categories;
    }

    public function getEnableGetReviewsCsv()
    {
        return (int)$this->enable_get_reviews_csv;
    }

    public function getEnableGetReviews()
    {
        return (int)$this->enable_get_reviews;
    }

    public function getEnableGetMediaCsv()
    {
        return (int)$this->enable_get_media_csv;
    }

    public function getEnableGetLogFile()
    {
        return (int)$this->enable_get_log_file;
    }

    public function getEnableMobileWebsite()
    {
        return (int)$this->enable_mobile_website;
    }

    public function getEnableCron()
    {
        return (int)$this->enable_cron;
    }

    public function getEnableClearLogFile()
    {
        return (int)$this->enable_clear_log_file;
    }

    public function getEnableClearCache()
    {
        return (int)$this->enable_clear_cache;
    }

    public function getEnableGetSettings()
    {
        return (int)$this->enable_get_settings;
    }

    public function getEnableSetSettings()
    {
        return (int)$this->enable_set_settings;
    }

    public function getEnableSyncFavouriteList()
    {
        return (int)$this->enable_sync_favourite_list;
    }

    public function getEnableReceiveAuthorization()
    {
        return (int)$this->enable_receive_authorization;
    }

    public function getCountry()
    {
        return strtoupper($this->country);
    }

    public function getLanguage()
    {
        return strtolower($this->language);
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getMobileHeaderParent()
    {
        return $this->mobile_header_parent;
    }

    public function getMobileHeaderPrepend()
    {
        return $this->mobile_header_prepend;
    }

    public function getExportBufferCapacity()
    {
        return $this->export_buffer_capacity;
    }

    public function getMaxAttributes()
    {
        return $this->max_attributes;
    }

    public function getExportFolderPath()
    {
        return $this->export_folder_path;
    }

    public function getLogFolderPath()
    {
        return $this->log_folder_path;
    }

    public function getCacheFolderPath()
    {
        return $this->cache_folder_path;
    }

    public function getItemsCsvFilename()
    {
        return $this->items_csv_filename;
    }

    public function getItemsXmlFilename()
    {
        return $this->items_xml_filename;
    }

    public function getItemsJsonFilename()
    {
        return $this->items_json_filename;
    }

    public function getMediaCsvFilename()
    {
        return $this->media_csv_filename;
    }

    public function getCategoriesCsvFilename()
    {
        return $this->categories_csv_filename;
    }

    public function getCategoriesXmlFilename()
    {
        return $this->categories_xml_filename;
    }

    public function getCategoriesJsonFilename()
    {
        return $this->categories_json_filename;
    }

    public function getReviewsCsvFilename()
    {
        return $this->reviews_csv_filename;
    }

    public function getReviewsXmlFilename()
    {
        return $this->reviews_xml_filename;
    }

    public function getAccessLogFilename()
    {
        return $this->access_log_filename;
    }

    public function getRequestLogFilename()
    {
        return $this->request_log_filename;
    }

    public function getErrorLogFilename()
    {
        return $this->error_log_filename;
    }

    public function getDebugLogFilename()
    {
        return $this->debug_log_filename;
    }

    public function getRedirectKeywordCacheFilename()
    {
        return $this->redirect_keyword_cache_filename;
    }

    public function getRedirectSkipKeywordCacheFilename()
    {
        return $this->redirect_skip_keyword_cache_filename;
    }

    public function getItemsCsvPath()
    {
        return rtrim($this->export_folder_path . DS . $this->items_csv_filename, DS);
    }

    public function getItemsXmlPath()
    {
        return rtrim($this->export_folder_path . DS . $this->items_xml_filename, DS);
    }

    public function getItemsJsonPath()
    {
        return rtrim($this->export_folder_path . DS . $this->items_json_filename, DS);
    }

    public function getCategoriesXmlPath()
    {
        return rtrim($this->export_folder_path . DS . $this->categories_xml_filename, DS);
    }

    public function getCategoriesJsonPath()
    {
        return rtrim($this->export_folder_path . DS . $this->categories_json_filename, DS);
    }

    public function getMediaCsvPath()
    {
        return rtrim($this->export_folder_path . DS . $this->media_csv_filename, DS);
    }

    public function getCategoriesCsvPath()
    {
        return rtrim($this->export_folder_path . DS . $this->categories_csv_filename, DS);
    }

    public function getReviewsCsvPath()
    {
        return rtrim($this->export_folder_path . DS . $this->reviews_csv_filename, DS);
    }

    public function getReviewsXmlPath()
    {
        return rtrim($this->export_folder_path . DS . $this->reviews_xml_filename, DS);
    }

    public function getReviewsJsonPath()
    {
        return rtrim($this->export_folder_path . DS . $this->reviews_json_filename, DS);
    }

    public function getAccessLogPath()
    {
        return rtrim($this->log_folder_path . DS . $this->access_log_filename, DS);
    }

    public function getRequestLogPath()
    {
        return rtrim($this->log_folder_path . DS . $this->request_log_filename, DS);
    }

    public function getErrorLogPath()
    {
        return rtrim($this->log_folder_path . DS . $this->error_log_filename, DS);
    }

    public function getDebugLogPath()
    {
        return rtrim($this->log_folder_path . DS . $this->debug_log_filename, DS);
    }

    public function getRedirectKeywordCachePath()
    {
        return rtrim($this->cache_folder_path . DS . $this->redirect_keyword_cache_filename, DS);
    }

    public function getRedirectSkipKeywordCachePath()
    {
        return rtrim($this->cache_folder_path . DS . $this->redirect_skip_keyword_cache_filename, DS);
    }

    public function getIsShopgateAdapter()
    {
        return $this->is_shopgate_adapter;
    }

    public function getRedirectableGetParams()
    {
        return $this->redirectable_get_params;
    }

    public function getHtmlTags()
    {
        return $this->html_tags;
    }

    public function getDefaultExecutionTime()
    {
        return $this->default_execution_time;
    }

    public function getDefaultMemoryLimit()
    {
        return $this->default_memory_limit;
    }

    public function getExcludeItemIds()
    {
        return $this->exclude_item_ids;
    }

    public function getFacebookPixelId()
    {
        return $this->facebook_pixel_id;
    }

    public function getCronJobWhiteList()
    {
        return $this->cronJobWhiteList;
    }

    ###############
    ### Setters ###
    ###############
    public function setPluginName($value)
    {
        $this->plugin_name = $value;
    }

    public function setUseCustomErrorHandler($value)
    {
        $this->use_custom_error_handler = $value;
    }

    //	public function setSpaAuthServiceClassName($value) {
    //		$this->spa_auth_service_class_name = $value;
    //	}

    public function setSmaAuthServiceClassName($value)
    {
        $this->sma_auth_service_class_name = $value;
    }

    public function setOauthAccessToken($value)
    {
        $this->oauth_access_token = $value;
    }

    public function setCustomerNumber($value)
    {
        $this->customer_number = $value;
    }

    public function setShopNumber($value)
    {
        $this->shop_number = $value;
    }

    public function setApikey($value)
    {
        $this->apikey = $value;
    }

    public function setAlias($value)
    {
        $this->alias = $value;
    }

    public function setCname($value)
    {
        $this->cname = rtrim($value, '/');
    }

    public function setServer($value)
    {
        $this->server = $value;
    }

    public function setApiUrl($value)
    {
        $this->api_url = $value;
    }

    public function setShopIsActive($value)
    {
        $this->shop_is_active = $value;
    }

    public function setAlwaysUseSsl($value)
    {
        $this->always_use_ssl = $value;
    }

    public function setEnableRedirectKeywordUpdate($value)
    {
        $this->enable_redirect_keyword_update = $value;
    }

    public function setEnableDefaultRedirect($value)
    {
        $this->enable_default_redirect = $value;
    }

    public function setEncoding($value)
    {
        $this->encoding = $value;
    }

    public function setExportConvertEncoding($value)
    {
        $this->export_convert_encoding = $value;
    }

    public function setForceSourceEncoding($value)
    {
        $this->force_source_encoding = $value;
    }

    public function setSupportedFieldsCheckCart($value)
    {
        $this->supported_fields_check_cart = $value;
    }

    public function setSupportedFieldsGetSettings($value)
    {
        $this->supported_fields_get_settings = $value;
    }

    public function setSupportedMethodsCron($value)
    {
        $this->supported_methods_cron = $value;
    }

    public function setSupportedResponseTypes($value)
    {
        $this->supported_response_types = $value;
    }

    public function setEnablePing($value)
    {
        $this->enable_ping = $value;
    }

    public function setEnableAddOrder($value)
    {
        $this->enable_add_order = $value;
    }

    public function setEnableUpdateOrder($value)
    {
        $this->enable_update_order = $value;
    }

    public function setEnableCheckCart($value)
    {
        $this->enable_check_cart = $value;
    }

    public function setEnableCheckStock($value)
    {
        $this->enable_check_stock = $value;
    }

    public function setEnableRedeemCoupons($value)
    {
        $this->enable_redeem_coupons = $value;
    }

    public function setEnableGetOrders($value)
    {
        $this->enable_get_orders = $value;
    }

    public function setEnableGetCustomer($value)
    {
        $this->enable_get_customer = $value;
    }

    public function setEnableRegisterCustomer($value)
    {
        $this->enable_register_customer = $value;
    }

    public function setEnableGetDebugInfo($value)
    {
        $this->enable_get_debug_info = $value;
    }

    public function setEnableGetItemsCsv($value)
    {
        $this->enable_get_items_csv = $value;
    }

    public function setEnableGetItems($value)
    {
        $this->enable_get_items = $value;
    }

    public function setEnableGetCategoriesCsv($value)
    {
        $this->enable_get_categories_csv = $value;
    }

    public function setEnableGetCategories($value)
    {
        $this->enable_get_categories = $value;
    }

    public function setEnableGetReviewsCsv($value)
    {
        $this->enable_get_reviews_csv = $value;
    }

    public function setEnableGetReviews($value)
    {
        $this->enable_get_reviews = $value;
    }

    public function setEnableGetMediaCsv($value)
    {
        $this->enable_get_media_csv = $value;
    }

    public function setEnableGetLogFile($value)
    {
        $this->enable_get_log_file = $value;
    }

    public function setEnableMobileWebsite($value)
    {
        $this->enable_mobile_website = $value;
    }

    public function setEnableCron($value)
    {
        $this->enable_cron = $value;
    }

    public function setEnableClearLogFile($value)
    {
        $this->enable_clear_log_file = $value;
    }

    public function setEnableClearCache($value)
    {
        $this->enable_clear_cache = $value;
    }

    public function setEnableGetSettings($value)
    {
        $this->enable_get_settings = $value;
    }

    public function setEnableSetSettings($value)
    {
        $this->enable_set_settings = $value;
    }

    public function setEnableSyncFavouriteList($value)
    {
        $this->enable_sync_favourite_list = $value;
    }

    public function setEnableReceiveAuthorization($value)
    {
        $this->enable_receive_authorization = $value;
    }

    public function setCountry($value)
    {
        $this->country = strtoupper($value);
    }

    public function setLanguage($value)
    {
        $this->language = strtolower($value);
    }

    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    public function setMobileHeaderParent($value)
    {
        $this->mobile_header_parent = $value;
    }

    public function setMobileHeaderPrepend($value)
    {
        $this->mobile_header_prepend = $value;
    }

    public function setExportBufferCapacity($value)
    {
        $this->export_buffer_capacity = $value;
    }

    public function setMaxAttributes($value)
    {
        $this->max_attributes = $value;
    }

    public function setExportFolderPath($value)
    {
        $this->export_folder_path = $value;
    }

    public function setLogFolderPath($value)
    {
        $this->log_folder_path = $value;
    }

    public function setCacheFolderPath($value)
    {
        $this->cache_folder_path = $value;
    }

    public function setItemsCsvFilename($value)
    {
        $this->items_csv_filename = $value;
    }

    public function setItemsXmlFilename($value)
    {
        $this->items_xml_filename = $value;
    }

    public function setItemsJsonFilename($value)
    {
        $this->items_json_filename = $value;
    }

    public function setMediaCsvFilename($value)
    {
        $this->media_csv_filename = $value;
    }

    public function setCategoriesCsvFilename($value)
    {
        $this->categories_csv_filename = $value;
    }

    public function setCategoriesXmlFilename($value)
    {
        $this->categories_xml_filename = $value;
    }

    public function setCategoriesJsonFilename($value)
    {
        $this->categories_json_filename = $value;
    }

    public function setReviewsCsvFilename($value)
    {
        $this->reviews_csv_filename = $value;
    }

    public function setReviewsXmlFilename($value)
    {
        $this->reviews_xml_filename = $value;
    }

    public function setReviewsJsonFilename($value)
    {
        $this->reviews_json_filename = $value;
    }

    public function setAccessLogFilename($value)
    {
        $this->access_log_filename = $value;
    }

    public function setRequestLogFilename($value)
    {
        $this->request_log_filename = $value;
    }

    public function setErrorLogFilename($value)
    {
        $this->error_log_filename = $value;
    }

    public function setDebugLogFilename($value)
    {
        $this->debug_log_filename = $value;
    }

    public function setRedirectKeywordCacheFilename($value)
    {
        $this->redirect_keyword_cache_filename = $value;
    }

    public function setRedirectSkipKeywordCacheFilename($value)
    {
        $this->redirect_skip_keyword_cache_filename = $value;
    }

    public function setItemsCsvPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path = $dir;
            $this->items_csv_filename = $file;
        }
    }

    public function setItemsXmlPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path = $dir;
            $this->items_xml_filename = $file;
        }
    }

    public function setItemsJsonPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path  = $dir;
            $this->items_json_filename = $file;
        }
    }

    public function setMediaCsvPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path = $dir;
            $this->media_csv_filename = $file;
        }
    }

    public function setCategoriesCsvPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path      = $dir;
            $this->categories_csv_filename = $file;
        }
    }

    public function setCategoriesXmlPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path      = $dir;
            $this->categories_xml_filename = $file;
        }
    }

    public function setCategoriesJsonPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path       = $dir;
            $this->categories_json_filename = $file;
        }
    }

    public function setReviewsCsvPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path   = $dir;
            $this->reviews_csv_filename = $file;
        }
    }

    public function setReviewsXmlPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path   = $dir;
            $this->reviews_xml_filename = $file;
        }
    }

    public function setReviewsJsonPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->export_folder_path    = $dir;
            $this->reviews_json_filename = $file;
        }
    }

    public function setAccessLogPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->log_folder_path     = $dir;
            $this->access_log_filename = $file;
        }
    }

    public function setRequestLogPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->log_folder_path      = $dir;
            $this->request_log_filename = $file;
        }
    }

    public function setErrorLogPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->log_folder_path    = $dir;
            $this->error_log_filename = $file;
        }
    }

    public function setDebugLogPath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->log_folder_path    = $dir;
            $this->debug_log_filename = $file;
        }
    }

    public function setRedirectKeywordCachePath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->cache_folder_path               = $dir;
            $this->redirect_keyword_cache_filename = $file;
        }
    }

    public function setRedirectSkipKeywordCachePath($value)
    {
        $dir  = dirname($value);
        $file = basename($value);

        if (!empty($dir) && !empty($file)) {
            $this->cache_folder_path                    = $dir;
            $this->redirect_skip_keyword_cache_filename = $file;
        }
    }

    public function setIsShopgateAdapter($value)
    {
        $this->is_shopgate_adapter = $value;
    }

    public function setRedirectableGetParams($value)
    {
        $this->redirectable_get_params = $value;
    }

    public function setHtmlTags($value)
    {
        $this->html_tags = $value;
    }

    /**
     * @param int $default_execution_time
     */
    public function setDefaultExecutionTime($default_execution_time)
    {
        $this->default_execution_time = $default_execution_time;
    }

    /**
     * @param int $default_memory_limit
     */
    public function setDefaultMemoryLimit($default_memory_limit)
    {
        $this->default_memory_limit = $default_memory_limit;
    }

    /**
     * @param array|string $exclude_item_ids list of item Ids which should be excluded from the item export
     */
    public function setExcludeItemIds($exclude_item_ids)
    {
        $this->exclude_item_ids =
            is_array($exclude_item_ids)
                ? $exclude_item_ids
                : (array)$this->jsonDecode($exclude_item_ids);
    }

    /**
     * @param int $facebook_pixel_id
     */
    public function setFacebookPixelId($facebook_pixel_id)
    {
        $this->facebook_pixel_id = $facebook_pixel_id;
    }

    public function setCronJobWhiteList($cron_job_white_List)
    {
        $this->cronJobWhiteList = $cron_job_white_List;
    }

    ###############
    ### Helpers ###
    ###############
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitConfig($this);
    }

    public function returnAdditionalSetting($setting)
    {
        return (isset($this->additionalSettings[$setting]))
            ? $this->additionalSettings[$setting]
            : null;
    }

    public function returnAdditionalSettings()
    {
        return $this->additionalSettings;
    }

    public function buildProperties()
    {
        $properties = parent::buildProperties();

        // append the file paths
        $properties['items_csv_path']  = $this->getItemsCsvPath();
        $properties['items_xml_path']  = $this->getItemsXmlPath();
        $properties['items_json_path'] = $this->getItemsJsonPath();

        $properties['media_csv_path'] = $this->getMediaCsvPath();

        $properties['categories_csv_path']  = $this->getCategoriesCsvPath();
        $properties['categories_xml_path']  = $this->getCategoriesXmlPath();
        $properties['categories_json_path'] = $this->getCategoriesJsonPath();

        $properties['reviews_csv_path']  = $this->getReviewsCsvPath();
        $properties['reviews_xml_path']  = $this->getReviewsXmlPath();
        $properties['reviews_json_path'] = $this->getReviewsJsonPath();

        $properties['access_log_path']  = $this->getAccessLogPath();
        $properties['request_log_path'] = $this->getRequestLogPath();
        $properties['error_log_path']   = $this->getErrorLogPath();
        $properties['debug_log_path']   = $this->getDebugLogPath();

        $properties['redirect_keyword_cache_path']      = $this->getRedirectKeywordCachePath();
        $properties['redirect_skip_keyword_cache_path'] = $this->getRedirectSkipKeywordCachePath();

        return $properties;
    }

    /**
     * Tries to include the specified file and check for $shopgate_config.
     *
     * @param string $path The path to the configuration file.
     *
     * @return mixed[]|bool The $shopgate_config array if the file was included and defined $shopgate_config, false
     *                      otherwise.
     */
    private function includeFile($path)
    {
        $shopgate_config = null;

        // try including the file
        if (file_exists($path)) {
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include($path);
            ob_end_clean();
        } else {
            return false;
        }

        // check $shopgate_config
        if (!isset($shopgate_config) || !is_array($shopgate_config)) {
            return false;
        } else {
            return $shopgate_config;
        }
    }

    /**
     * Maps the passed data to the additional settings array.
     *
     * @param array <string, mixed> $data The data to map.
     */
    private function mapAdditionalSettings($data = array())
    {
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
     *
     * @param string  $name      Method name.
     * @param mixed[] $arguments Arguments to call the method with.
     *
     * @return mixed The return value of the called method.
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld.
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(
            array(
                'ShopgateConfigOld',
                $name,
            ),
            $arguments
        );
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function setConfig(array $newConfig, $validate = true)
    {
        ShopgateConfigOld::setConfig($newConfig, $validate);
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function validateAndReturnConfig()
    {
        return ShopgateConfigOld::validateAndReturnConfig();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getConfig()
    {
        return ShopgateConfigOld::getConfig();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getConfigField($field)
    {
        return ShopgateConfigOld::getConfigField($field);
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getLogFilePath($type = ShopgateLogger::LOGTYPE_ERROR)
    {
        return ShopgateConfigOld::getLogFilePath($type);
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getItemsCsvFilePath()
    {
        return ShopgateConfigOld::getItemsCsvFilePath();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getCategoriesCsvFilePath()
    {
        return ShopgateConfigOld::getCategoriesCsvFilePath();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getReviewsCsvFilePath()
    {
        return ShopgateConfigOld::getReviewsCsvFilePath();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getPagesCsvFilePath()
    {
        return ShopgateConfigOld::getPagesCsvFilePath();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getRedirectKeywordsFilePath()
    {
        return ShopgateConfigOld::getRedirectKeywordsFilePath();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function getSkipRedirectKeywordsFilePath()
    {
        return ShopgateConfigOld::getSkipRedirectKeywordsFilePath();
    }

    /**
     * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
     *
     * @deprecated
     * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
     */
    public static function saveConfig()
    {
        ShopgateConfigOld::saveConfig();
    }
}

/**
 * Einstellungen für das Framework
 *
 * @version 1.0.0
 * @deprecated
 * @see     ShopgateConfig
 */
class ShopgateConfigOld extends ShopgateObject
{
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
     * - enable_cron -> Cron erlaubt.
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
    private static $config = array(
        'api_url'                       => 'https://api.shopgate.com/merchant/',
        'customer_number'               => 'THE_CUSTOMER_NUMBER',
        'shop_number'                   => 'THE_SHOP_NUMBER',
        'apikey'                        => 'THE_API_KEY',
        'alias'                         => 'my-shop',
        'cname'                         => '',
        'server'                        => 'live',
        'plugin'                        => 'example',
        'plugin_language'               => 'DE',
        'plugin_currency'               => 'EUR',
        'plugin_root_dir'               => "",
        'enable_ping'                   => true,
        'enable_cron'                   => true,
        'enable_add_order'              => true,
        'enable_update_order'           => true,
        'enable_get_customer'           => true,
        'enable_get_categories_csv'     => true,
        'enable_get_orders'             => true,
        'enable_get_items_csv'          => true,
        'enable_get_reviews_csv'        => true,
        'enable_get_pages_csv'          => true,
        'enable_get_log_file'           => true,
        'enable_clear_log_file'         => true,
        'enable_mobile_website'         => true,
        'generate_items_csv_on_the_fly' => true,
        'max_attributes'                => 50,
        'use_custom_error_handler'      => false,
        'encoding'                      => 'UTF-8',
    );

    /**
     * Übergeben und überprüfen der Einstellungen.
     *
     * @deprecated
     *
     * @param array $newConfig
     * @param bool  $validate
     *
     * @throws ShopgateLibraryException
     */
    final public static function setConfig(array $newConfig, $validate = true)
    {
        self::deprecated(__METHOD__);

        if ($validate) {
            self::validateConfig($newConfig);
        }
        self::$config = array_merge(self::$config, $newConfig);
    }

    /**
     * Gibt das Konfigurations-Array zurück.
     *
     * @deprecated
     */
    final public static function validateAndReturnConfig()
    {
        self::deprecated(__METHOD__);

        try {
            self::validateConfig(self::$config);
        } catch (ShopgateLibraryException $e) {
            throw $e;
        }

        return self::getConfig();
    }

    /**
     *
     * Returnd the configuration without validating
     *
     * @deprecated
     * @return array
     */
    public static function getConfig()
    {
        self::deprecated(__METHOD__);

        return self::$config;
    }

    public static function getConfigField($field)
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config[$field])) {
            return self::$config[$field];
        } else {
            return null;
        }
    }

    final public static function getPluginName()
    {
        self::deprecated(__METHOD__);

        return self::$config["plugin"];
    }

    /**
     * Gibt den Pfad zur Error-Log-Datei zurück.
     * Für diese Datei sollten Schreib- und leserechte gewährt werden.
     *
     * @deprecated
     */
    final public static function getLogFilePath($type = ShopgateLogger::LOGTYPE_ERROR)
    {
        self::deprecated(__METHOD__);

        switch (strtolower($type)) {
            default:
                $type = 'error';
            // no break
            case "access":
            case "request":
            case "debug":
        }

        if (isset(self::$config['path_to_' . strtolower($type) . '_log_file'])) {
            return self::$config['path_to_' . strtolower($type) . '_log_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/logs/' . strtolower($type) . '.log';
        }
    }

    /**
     * Gibt den Pfad zur items-csv-Datei zurück.
     * Für diese Datei sollten Schreib- und leserechte gewährt werden.
     *
     * @deprecated
     */
    final public static function getItemsCsvFilePath()
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config['path_to_items_csv_file'])) {
            return self::$config['path_to_items_csv_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/items.csv';
        }
    }

    /**
     * @deprecated
     */
    final public static function getCategoriesCsvFilePath()
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config['path_to_categories_csv_file'])) {
            return self::$config['path_to_categories_csv_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/categories.csv';
        }
    }

    /**
     * Gibt den Pfad zur review-csv-Datei zurück
     * Für diese Datei sollten Schreib- und leserechte gewährt werden
     *
     * @deprecated
     */
    final public static function getReviewsCsvFilePath()
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config['path_to_reviews_csv_file'])) {
            return self::$config['path_to_reviews_csv_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/reviews.csv';
        }
    }

    /**
     * Gibt den Pfad zur pages-csv-Datei zurück.
     * Für diese Datei sollten Schreib- und leserechte gewährt werden.
     *
     * @deprecated
     */
    final public static function getPagesCsvFilePath()
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config['path_to_pages_csv_file'])) {
            return self::$config['path_to_pages_csv_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/pages.csv';
        }
    }

    /**
     * @return string the absolute Path for the Redirect-Keywords-Caching-File
     * @deprecated
     */
    final public static function getRedirectKeywordsFilePath()
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config['path_to_redirect_keywords_file'])) {
            return self::$config['path_to_redirect_keywords_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/cache/redirect_keywords.txt';
        }
    }

    /**
     * @return string the absolute Path for the Skip-Redirect-Keywords-Caching-File
     * @deprecated
     */
    final public static function getSkipRedirectKeywordsFilePath()
    {
        self::deprecated(__METHOD__);

        if (isset(self::$config['path_to_skip_redirect_keywords_file'])) {
            return self::$config['path_to_skip_redirect_keywords_file'];
        } else {
            return SHOPGATE_BASE_DIR . '/temp/cache/skip_redirect_keywords.txt';
        }
    }

    /**
     * Prüft, ob alle Pflichtfelder gesetzt sind und setzt die api_url.
     *
     * @deprecated
     *
     * @param array $newConfig
     *
     * @throws ShopgateLibraryException
     */
    private static function validateConfig(array $newConfig)
    {
        self::deprecated(__METHOD__);

        //Pflichtfelder überprüfen
        if (!preg_match("/^\S+/", $newConfig['apikey'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_INVALID_VALUE,
                "Field 'apikey' contains invalid value '{$newConfig['apikey']}'."
            );
        }
        if (!preg_match("/^\d{5,}$/", $newConfig['customer_number'])) {
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
        if (!empty($newConfig["server"]) && $newConfig["server"] === "pg") {
            // Playground?
            self::$config["api_url"] = "https://api.shopgatepg.com/merchant/";
        } else {
            if (!empty($newConfig["server"]) && $newConfig["server"] === "custom" && !empty($newConfig["server_custom_url"])
            ) {
                // Eigener Test-Server?
                self::$config["api_url"] = $newConfig["server_custom_url"];
            } else {
                // Live-Server?
                self::$config["api_url"] = "https://api.shopgate.com/merchant/";
            }
        }
    }

    /**
     * @deprecated
     * @throws ShopgateLibraryException
     */
    public static function saveConfig()
    {
        self::deprecated(__METHOD__);

        $config = self::getConfig();

        $returnString = "<?php" . "\r\n";

        $returnString .= "\$shopgate_config = array();\r\n";

        foreach ($config as $key => $field) {
            if ($key != 'save') {
                if (is_bool($field) || $field === "true" || $field === "false") {
                    if ($field === "true") {
                        $field = true;
                    }
                    if ($field === "false") {
                        $field = false;
                    }

                    $returnString .= '$shopgate_config["' . $key . '"] = ' . ($field
                            ? 'true'
                            : 'false') . ';' . "\r\n";
                } else {
                    if (is_numeric($field)) {
                        $returnString .= '$shopgate_config["' . $key . '"] = ' . $field . ';' . "\r\n";
                    } else {
                        $returnString .= '$shopgate_config["' . $key . '"] = "' . $field . '";' . "\r\n";
                    }
                }
            }
        }

        $handle = @fopen(dirname(__FILE__) . '/../config/myconfig.php', 'w+');
        if ($handle == false) {
            throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR);
        } else {
            if (!fwrite($handle, $returnString)) {
                throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR);
            }
        }

        fclose($handle);
    }

    /**
     * Issues a PHP deprecated warning and log entry for calls to deprecated ShopgateConfigOld methods.
     *
     * @param string $methodName The name of the called method.
     */
    private static function deprecated($methodName)
    {
        $message = 'Use of ' . $methodName . ' and the whole ShopgateConfigOld class is deprecated.';
        trigger_error($message, E_USER_DEPRECATED);
        ShopgateLogger::getInstance()->log($message);
    }
}

/**
 * Manages configuration for library _and_ plugin options.
 *
 * Classes implementing this class are used to save general library settings and specific settings for your plugin.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateConfigInterface
{
    const SHOPGATE_API_URL_LIVE                     = 'https://api.shopgate.com/merchant/';
    const SHOPGATE_API_URL_LIVE_OAUTH               = 'https://api.shopgate.com/merchant2/';
    const SHOPGATE_API_URL_SL                       = 'https://api.shopgatesl.com/merchant/';
    const SHOPGATE_API_URL_SL_OAUTH                 = 'https://api.shopgatesl.com/merchant2/';
    const SHOPGATE_API_URL_PG                       = 'https://api.shopgatepg.com/merchant/';
    const SHOPGATE_API_URL_PG_OAUTH                 = 'https://api.shopgatepg.com/merchant2/';
    const SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE = 'ShopgateAuthenticationServiceShopgate';
    const SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH    = 'ShopgateAuthenticationServiceOAuth';
    const SHOPGATE_FILE_PREFIX                      = 'shopgate_';
    const DEFAULT_MEMORY_LIMIT                      = -1;
    const DEFAULT_EXECUTION_TIME                    = 0;
    const DEFAULT_CONFIGURATION_FILE_NAME           = 'myconfig.php';

    /**
     * Builds the path to the configuration file using the passed file name or a default file name.
     *
     * @param string $fileName
     *
     * @return string
     */
    public function buildConfigFilePath($fileName = self::DEFAULT_CONFIGURATION_FILE_NAME);

    /**
     * Loads an array of key-value pairs or a permanent storage.
     *
     * @param array $settings key-value pairs of settings or null to load from a permanent storage
     *
     * @throws ShopgateLibraryException with code ShopgateLibraryException::CONFIG_READ_WRITE_ERROR
     * @throws ShopgateLibraryException with code ShopgateLibraryException::
     * @post if $settings was passed: all applicable keys and values from $settings are loaded into the configuration
     *       and can be retrieved via getter methods if $settings was null: all applicable keys and values from a
     *       permanent storage are loaded into the configuration and can be retrieved via getter methods
     */
    public function load(array $settings = null);

    /**
     * Saves the desired fields to a permanent storage.
     *
     * @param array $fieldList the list of fields to save
     * @param bool  $validate  true to validate the values to be saved
     *
     * @throws ShopgateLibraryException with code ShopgateLibraryException::CONFIG_READ_WRITE_ERROR
     * @throws ShopgateLibraryException with code ShopgateLibraryException::CONFIG_INVALID_VALUE
     * @post the configuration is saved into a permanent storage
     */
    public function save(array $fieldList, $validate = true);

    /**
     * Validates the configuration values.
     *
     * If $fieldList contains values, only these values will be validated. If it's empty, all values that have a
     * validation rule will be validated.
     *
     * In case one or more validations fail an exception is thrown. The failed fields are appended as additonal
     * information in form of a comma-separated list.
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

    //	/**
    //	 * @return string $value Class name for the PluginAPI auth service
    //	 */
    //	public function getSpaAuthServiceClassName();

    /**
     * @return string $value Class name for the MerchantAPI auth service
     */
    public function getSmaAuthServiceClassName();

    /**
     * @return string OAuth access token
     */
    public function getOauthAccessToken();

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
     * @return string Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in
     *                between are ok)
     */
    public function getAlias();

    /**
     * @return string Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or
     *                "https://" followed by any number of non-whitespace characters)
     */
    public function getCname();

    /**
     * @return string The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
     */
    public function getServer();

    /**
     * @return array => returns all possible fixed api urls
     */
    public function getApiUrls();

    /**
     * @return string If getServer() returns "live", ShopgateConfigInterface::SHOPGATE_API_URL_LIVE is returned.<br />
     *                 If getServer() returns "pg", ShopgateConfigInterface::SHOPGATE_API_URL_PG is returned.<br />
     *                 If getServer() returns "custom": A custom API url (empty or a string beginning with "http://" or
     *                 "https://" followed by any number of non-whitespace characters) is returned.<br /> If
     *                 getServer() returns a different value than the above,
     *                 ShopgateConfigInterface::SHOPGATE_API_URL_LIVE is returned.
     */
    public function getApiUrl();

    /**
     * @return bool true to indicate a shop has been activated by Shopgate
     */
    public function getShopIsActive();

    /**
     * @return bool true to always use SSL / HTTPS urls for download of external content (such as graphics for the
     *              mobile header button)
     */
    public function getAlwaysUseSsl();

    /**
     * @return bool true to enable updates of keywords that identify mobile devices
     */
    public function getEnableRedirectKeywordUpdate();

    /**
     * @return bool true to enable default redirect for mobile devices from content sites to mobile website (welcome
     *              page)
     */
    public function getEnableDefaultRedirect();

    /**
     * @return string The encoding the shop system is using internally.
     */
    public function getEncoding();

    /**
     * @return bool true to enable automatic encoding conversion to utf-8 during export
     */
    public function getExportConvertEncoding();

    /**
     * @return bool if true forces the $encoding to be the only one source encoding for all encoding operations
     */
    public function getForceSourceEncoding();

    /**
     * @return array<string, string[]> the list of response types supported by the plugin, indexed by exports
     */
    public function getSupportedResponseTypes();

    /**
     * @return array the list of fields supported by the plugin method check_cart
     */
    public function getSupportedFieldsCheckCart();

    /**
     * @return array the list of fields supported by the plugin method get_settings
     */
    public function getSupportedFieldsGetSettings();

    /**
     * @return string[] the list of methods supported by the cron action
     */
    public function getSupportedMethodsCron();

    /**
     * @return int
     */
    public function getEnablePing();

    /**
     * @return int
     */
    public function getEnableAddOrder();

    /**
     * @return int
     */
    public function getEnableUpdateOrder();

    /**
     * @return int
     */
    public function getEnableCheckCart();

    /**
     * @return int
     */
    public function getEnableCheckStock();

    /**
     * @return int
     */
    public function getEnableRedeemCoupons();

    /**
     * @return int
     */
    public function getEnableGetOrders();

    /**
     * @return int
     */
    public function getEnableGetCustomer();

    /**
     * @return int
     */
    public function getEnableRegisterCustomer();

    /**
     * @return int
     */
    public function getEnableGetDebugInfo();

    /**
     * @return int
     */
    public function getEnableGetItemsCsv();

    /**
     * @return int
     */
    public function getEnableGetItems();

    /**
     * @return int
     */
    public function getEnableGetCategoriesCsv();

    /**
     * @return int
     */
    public function getEnableGetCategories();

    /**
     * @return int
     */
    public function getEnableGetReviewsCsv();

    /**
     * @return int
     */
    public function getEnableGetReviews();

    /**
     * @return int
     */
    public function getEnableGetMediaCsv();

    /**
     * @return int
     */
    public function getEnableGetLogFile();

    /**
     * @return int
     */
    public function getEnableMobileWebsite();

    /**
     * @return int
     */
    public function getEnableCron();

    /**
     * @return int
     */
    public function getEnableClearLogFile();

    /**
     * @return int
     */
    public function getEnableClearCache();

    /**
     * @return int
     */
    public function getEnableGetSettings();

    /**
     * @return int
     */
    public function getEnableSetSettings();

    /**
     * @return int
     */
    public function getEnableSyncFavouriteList();

    /**
     * @return int
     */
    public function getEnableReceiveAuthorization();

    /**
     * @return string The ISO 3166 ALPHA-2 code of the country the plugin uses for export.
     */
    public function getCountry();

    /**
     * @return string The ISO 3166 ALPHA-2 code of the language the plugin uses for export.
     */
    public function getLanguage();

    /**
     * @return string The ISO 4217 code of the currency the plugin uses for export.
     */
    public function getCurrency();

    /**
     * @return string CSS style identifier for the parent element the Mobile Header should be attached to.
     */
    public function getMobileHeaderParent();

    /**
     * @return bool True to insert the Mobile Header as first child element, false to append it.
     */
    public function getMobileHeaderPrepend();

    /**
     * @return int The capacity (number of lines) of the buffer used for the export actions.
     */
    public function getExportBufferCapacity();

    /**
     * @return int The maximum number of attributes per product that are created. If the number is exceeded, attributes
     *             should be converted to options.
     */
    public function getMaxAttributes();

    /**
     * @return string The path to the folder where the export CSV files are stored and retrieved from.
     */
    public function getExportFolderPath();

    /**
     * @return string The path to the folder where the log files are stored and retrieved from.
     */
    public function getLogFolderPath();

    /**
     * @return string The path to the folder where the cache files are stored and retrieved from.
     */
    public function getCacheFolderPath();

    /**
     * @return string The name of the items CSV file.
     */
    public function getItemsCsvFilename();

    /**
     * @return string The name of the items XML file.
     */
    public function getItemsXmlFilename();

    /**
     * @return string The name of the items JSON file.
     */
    public function getItemsJsonFilename();

    /**
     * @return string The name of the items CSV file.
     */
    public function getMediaCsvFilename();

    /**
     * @return string The name of the categories CSV file.
     */
    public function getCategoriesCsvFilename();

    /**
     * @return string The name of the categories XML file.
     */
    public function getCategoriesXmlFilename();

    /**
     * @return string The name of the categories JSON file.
     */
    public function getCategoriesJsonFilename();

    /**
     * @return string The name of the reviews CSV file.
     */
    public function getReviewsCsvFilename();

    /**
     * @return string The name of the access log file.
     */
    public function getAccessLogFilename();

    /**
     * @return string The name of the request log file.
     */
    public function getRequestLogFilename();

    /**
     * @return string The name of the error log file.
     */
    public function getErrorLogFilename();

    /**
     * @return string The name of the debug log file.
     */
    public function getDebugLogFilename();

    /**
     * @return string The name of the cache file for mobile device detection keywords.
     */
    public function getRedirectKeywordCacheFilename();

    /**
     * @return string The name of the cache file for mobile device skip detection keywords.
     */
    public function getRedirectSkipKeywordCacheFilename();

    /**
     * @return string The path to where the items CSV file is stored and retrieved from.
     */
    public function getItemsCsvPath();

    /**
     * @return string The path to where the items XML file is stored and retrieved from.
     */
    public function getItemsXmlPath();

    /**
     * @return string The path to where the items JSON file is stored and retrieved from.
     */
    public function getItemsJsonPath();

    /**
     * @return string The path to where the categories CSV file is stored and retrieved from.
     */
    public function getCategoriesCsvPath();

    /**
     * @return string The path to where the categories XML file is stored and retrieved from.
     */
    public function getCategoriesXmlPath();

    /**
     * @return string The path to where the categories JSON file is stored and retrieved from.
     */
    public function getCategoriesJsonPath();

    /**
     * @return string The path to where the reviews CSV file is stored and retrieved from.
     */
    public function getReviewsCsvPath();

    /**
     * @return string The path to where the reviews XML file is stored and retrieved from.
     */
    public function getReviewsXmlPath();

    /**
     * @return string The path to where the reviews JSON file is stored and retrieved from.
     */
    public function getReviewsJsonPath();

    /**
     * @return string The path to where the media CSV file is stored and retrieved from.
     */
    public function getMediaCsvPath();

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
     * @return string The path to the debug log file.
     */
    public function getDebugLogPath();

    /**
     * @return string The path to the cache file for mobile device detection keywords.
     */
    public function getRedirectKeywordCachePath();

    /**
     * @return string The path to the cache file for mobile device skip detection keywords.
     */
    public function getRedirectSkipKeywordCachePath();

    /**
     * @return bool True if the plugin is an adapter between Shopgate's and a third-party-API and servers multiple
     *              shops on both ends.
     */
    public function getIsShopgateAdapter();

    /**
     * @return array<int, string> an array with a list of get params which are allowed to passthrough to the mobile
     *                    device on redirect
     */
    public function getRedirectableGetParams();

    /**
     * @return string A JSON encoded string containing the HTML tags to be placed on the desktop website.
     */
    public function getHtmlTags();

    /**
     * @return int maximum execution time in seconds
     */
    public function getDefaultExecutionTime();

    /**
     * @return int default memory limit in MB
     */
    public function getDefaultMemoryLimit();

    /**
     * @return array list of items which should be excluded from the item export
     */
    public function getExcludeItemIds();

    /**
     * @return int facebook pixel ID
     */
    public function getFacebookPixelId();

    /**
     * @return array white list for method cron jobs
     */
    public function getCronJobWhiteList();

    /**
     * @param string $value The name of the plugin / shop system the plugin is for.
     */
    public function setPluginName($value);

    /**
     * @param bool $value true to activate the Shopgate error handler.
     */
    public function setUseCustomErrorHandler($value);

    //	/**
    //	 * @param string $value Class name for the PluginAPI authentication service
    //	 */
    //	public function setSpaAuthServiceClassName($value);

    /**
     * @param string $value Class name for the MerchantAPI authentication service
     */
    public function setSmaAuthServiceClassName($value);

    /**
     * @param string $value OAuth access token
     */
    public function setOauthAccessToken($value);

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
     * @param string $value Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes
     *                      in between are ok)
     */
    public function setAlias($value);

    /**
     * @param string $value Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or
     *                      "https://" followed by any number of non-whitespace characters)
     */
    public function setCname($value);

    /**
     * @param string $value The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
     */
    public function setServer($value);

    /**
     * @param string $value If $server is set to custom, Shopgate Merchant API calls will be made to this URL (empty or
     *                      a string beginning with "http://" or "https://" followed by any number of non-whitespace
     *                      characters)
     */
    public function setApiUrl($value);

    /**
     * @param bool $value true to indicate a shop has been activated by Shopgate
     */
    public function setShopIsActive($value);

    /**
     * @param bool $value true to always use SSL / HTTPS urls for download of external content (such as graphics for
     *                    the mobile header button)
     */
    public function setAlwaysUseSsl($value);

    /**
     * @param bool $value true to enable updates of keywords that identify mobile devices
     */
    public function setEnableRedirectKeywordUpdate($value);

    /**
     * @param bool      true to enable default redirect for mobile devices from content sites to mobile website (welcome
     *                  page)
     */
    public function setEnableDefaultRedirect($value);

    /**
     * @param string $value The encoding the shop system is using internally.
     */
    public function setEncoding($value);

    /**
     * @param bool $value true to enable automatic encoding conversion to utf-8 during export
     */
    public function setExportConvertEncoding($value);

    /**
     * @param bool $value if true forces the $encoding to be the only one source encoding for all encoding operations
     */
    public function setForceSourceEncoding($value);

    /**
     * @param array $value the list of fields supported by the plugin method check_cart
     */
    public function setSupportedFieldsCheckCart($value);

    /**
     * @param array $value the list of fields supported by the plugin method get_settings
     */
    public function setSupportedFieldsGetSettings($value);

    /**
     * @param string[] $value the list of methods supported by the cron action
     */
    public function setSupportedMethodsCron($value);

    /**
     * @param array <string, string[]> $value the list of response types supported by the plugin, indexed by exports
     */
    public function setSupportedResponseTypes($value);

    /**
     * @param int $value
     */
    public function setEnablePing($value);

    /**
     * @param int $value
     */
    public function setEnableAddOrder($value);

    /**
     * @param int $value
     */
    public function setEnableUpdateOrder($value);

    /**
     * @param int $value
     */
    public function setEnableCheckCart($value);

    /**
     * @param int $value
     */
    public function setEnableCheckStock($value);

    /**
     * @param int $value
     */
    public function setEnableRedeemCoupons($value);

    /**
     * @param int $value
     */
    public function setEnableGetOrders($value);

    /**
     * @param int $value
     */
    public function setEnableGetCustomer($value);

    /**
     * @param int $value
     */
    public function setEnableRegisterCustomer($value);

    /**
     * @param int $value
     */
    public function setEnableGetDebugInfo($value);

    /**
     * @param int $value
     */
    public function setEnableGetItemsCsv($value);

    /**
     * @param int $value
     */
    public function setEnableGetItems($value);

    /**
     * @param int $value
     */
    public function setEnableGetCategoriesCsv($value);

    /**
     * @param int $value
     */
    public function setEnableGetCategories($value);

    /**
     * @param int $value
     */
    public function setEnableGetReviewsCsv($value);

    /**
     * @param int $value
     */
    public function setEnableGetReviews($value);

    /**
     * @param int $value
     */
    public function setEnableGetMediaCsv($value);

    /**
     * @param int $value
     */
    public function setEnableGetLogFile($value);

    /**
     * @param int $value
     */
    public function setEnableMobileWebsite($value);

    /**
     * @param int $value
     */
    public function setEnableCron($value);

    /**
     * @param int $value
     */
    public function setEnableClearLogFile($value);

    /**
     * @param int $value
     */
    public function setEnableClearCache($value);

    /**
     * @param int $value
     */
    public function setEnableGetSettings($value);

    /**
     * @param int $value
     */
    public function setEnableSetSettings($value);

    /**
     * @param int $value
     */
    public function setEnableSyncFavouriteList($value);

    /**
     * @param int $value
     */
    public function setEnableReceiveAuthorization($value);

    /**
     * @param string $value The ISO 3166 ALPHA-2 code of the country the plugin uses for export.
     */
    public function setCountry($value);

    /**
     * @param string $value The ISO 3166 ALPHA-2 code of the language the plugin uses for export.
     */
    public function setLanguage($value);

    /**
     * @param string $value The ISO 4217 code of the currency the plugin uses for export.
     */
    public function setCurrency($value);

    /**
     * @param string $value CSS style identifier for the parent element the Mobile Header should be attached to.
     */
    public function setMobileHeaderParent($value);

    /**
     * @param bool $value True to insert the Mobile Header as first child element, false to append it.
     */
    public function setMobileHeaderPrepend($value);

    /**
     * @param int $value The capacity (number of lines) of the buffer used for the export actions.
     */
    public function setExportBufferCapacity($value);

    /**
     * @param int $value The maximum number of attributes per product that are created. If the number is exceeded,
     *                   attributes should be converted to options.
     */
    public function setMaxAttributes($value);

    /**
     * @param string $value The path to the folder where the export CSV files are stored and retrieved from.
     */
    public function setExportFolderPath($value);

    /**
     * @param string $value The path to the folder where the log files are stored and retrieved from.
     */
    public function setLogFolderPath($value);

    /**
     * @param string $value The path to the folder where the cache files are stored and retrieved from.
     */
    public function setCacheFolderPath($value);

    /**
     * @param string $value The name of the items CSV file.
     */
    public function setItemsCsvFilename($value);

    /**
     * @param string $value The name of the items XML file.
     */
    public function setItemsXmlFilename($value);

    /**
     * @param string $value The name of the items JSON file.
     */
    public function setItemsJsonFilename($value);

    /**
     * @param string $value The name of the items CSV file.
     */
    public function setMediaCsvFilename($value);

    /**
     * @param string $value The name of the categories CSV file.
     */
    public function setCategoriesCsvFilename($value);

    /**
     * @param string $value The name of the categories XML file.
     */
    public function setCategoriesXmlFilename($value);

    /**
     * @param string $value The name of the categories JSON file.
     */
    public function setCategoriesJsonFilename($value);

    /**
     * @param string $value The name of the reviews CSV file.
     */
    public function setReviewsCsvFilename($value);

    /**
     * @param string $value The name of the reviews XML file.
     */
    public function setReviewsXmlFilename($value);

    /**
     * @param string $value The name of the reviews JSON file.
     */
    public function setReviewsJsonFilename($value);

    /**
     * @param string $value The name of the access log file.
     */
    public function setAccessLogFilename($value);

    /**
     * @param string $value The name of the request log file.
     */
    public function setRequestLogFilename($value);

    /**
     * @param string $value The name of the error log file.
     */
    public function setErrorLogFilename($value);

    /**
     * @param string $value The name of the debug log file.
     */
    public function setDebugLogFilename($value);

    /**
     * @param string $value The name of the cache file for mobile device detection keywords.
     */
    public function setRedirectKeywordCacheFilename($value);

    /**
     * @param string $value The name of the cache file for mobile device skip detection keywords.
     */
    public function setRedirectSkipKeywordCacheFilename($value);

    /**
     * @param string $value The path to where the items CSV file is stored and retrieved from.
     */
    public function setItemsCsvPath($value);

    /**
     * @param string $value The path to where the items XML file is stored and retrieved from.
     */
    public function setItemsXmlPath($value);

    /**
     * @param string $value The path to where the items JSON file is stored and retrieved from.
     */
    public function setItemsJsonPath($value);

    /**
     * @param string $value The path to where the media CSV file is stored and retrieved from.
     */
    public function setMediaCsvPath($value);

    /**
     * @param string $value The path to where the categories CSV file is stored and retrieved from.
     */
    public function setCategoriesCsvPath($value);

    /**
     * @param string $value The path to where the categories XML file is stored and retrieved from.
     */
    public function setCategoriesXmlPath($value);

    /**
     * @param string $value The path to where the categories JSON file is stored and retrieved from.
     */
    public function setCategoriesJsonPath($value);

    /**
     * @param string $value The path to where the reviews CSV file is stored and retrieved from.
     */
    public function setReviewsCsvPath($value);

    /**
     * @param string $value The path to where the reviews XML file is stored and retrieved from.
     */
    public function setReviewsXmlPath($value);

    /**
     * @param string $value The path to where the reviews JSON file is stored and retrieved from.
     */
    public function setReviewsJsonPath($value);

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
     * @param string $value The path to the debug log file.
     */
    public function setDebugLogPath($value);

    /**
     * @param string $value The path to the cache file for mobile device detection keywords.
     */
    public function setRedirectKeywordCachePath($value);

    /**
     * @param string $value The path to the cache file for mobile device skip detection keywords.
     */
    public function setRedirectSkipKeywordCachePath($value);

    /**
     * @param bool $value True if the plugin is an adapter between Shopgate's and a third-party-API and servers
     *                    multiple shops on both ends.
     */
    public function setIsShopgateAdapter($value);

    /**
     * @param array <int, string> $value an array with a list of get params which are allowed to
     *              passthrough to the mobile device on redirect
     */
    public function setRedirectableGetParams($value);

    /**
     * @param string $value A JSON encoded string containing the HTML tags to be placed on the desktop website.
     */
    public function setHtmlTags($value);

    /**
     * @param $default_execution_time int set value for maximum execution time in seconds
     */
    public function setDefaultExecutionTime($default_execution_time);

    /**
     * @param $default_memory_limit int set value for default memory limit in MB
     */
    public function setDefaultMemoryLimit($default_memory_limit);

    /**
     * @param array|string $exclude_item_ids list of item Ids which should be excluded from the item export
     */
    public function setExcludeItemIds($exclude_item_ids);

    /**
     * @param int $facebook_pixel_id
     */
    public function setFacebookPixelId($facebook_pixel_id);

    /**
     * @param array $cron_job_white_list
     */
    public function setCronJobWhiteList($cron_job_white_list);

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

    /**
     * Returns the configuration as an array.
     *
     * All properties are included as well as the additional settings. Additional settings must be represented as if
     * they were properties, e.g. the additional settings array looking like this
     *
     * array('setting1' => 'value1', 'setting2' => 'value2')
     *
     * appears in the returned array like this:
     *
     * array('plugin_name' => 'abc', 'use_custom_error_handler' => 0, ......., 'setting1' => 'value1', 'setting2' =>
     * 'value2').
     *
     * Properties overwrite additional settings.
     *
     * @return array<string, mixed> The configuration as an array of key-value-pairs.
     */
    public function toArray();

    /**
     * Creates an array of all properties that have getters.
     *
     * @return mixed[]
     */
    public function buildProperties();
}
