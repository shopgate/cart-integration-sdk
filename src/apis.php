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

class ShopgateMerchantApi extends ShopgateObject implements ShopgateMerchantApiInterface
{
    /**
     * @var ShopgateAuthenticationServiceInterface
     */
    private $authService;

    /**
     * @var string
     */
    private $shopNumber;

    /**
     * @var string
     */
    private $apiUrl;

    public function __construct(ShopgateAuthenticationServiceInterface $authService, $shopNumber = null, $apiUrl = null)
    {
        $this->authService = $authService;
        $this->shopNumber  = $shopNumber;
        $this->apiUrl      = $apiUrl;
    }

    /**
     * Returns an array of curl-options for requests
     *
     * @param mixed[] $override cURL options to override for this request.
     *
     * @return mixed[] The default cURL options for a Shopgate Merchant API request merged with the options in
     *                 $override.
     */
    protected function getCurlOptArray($override = array())
    {
        $opt = array();

        $pluginVersion = defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'called outside plugin';

        $opt[CURLOPT_HEADER]         = false;
        $opt[CURLOPT_USERAGENT]      = 'ShopgatePlugin/' . $pluginVersion;
        $opt[CURLOPT_RETURNTRANSFER] = true;
        $opt[CURLOPT_SSL_VERIFYPEER] = true; // *always* verify peers, otherwise MITM attacks are trivial
        // Use value of CURL_SSLVERSION_TLSv1_2 for CURLOPT_SSLVERSION, because it is not available before PHP 5.5.19 / 5.6.3
        // Actual usage of TLS 1.2 (which is required by PCI DSS) depends on PHP cURL extension and underlying SSL lib
        $opt[CURLOPT_SSLVERSION] = 6;
        $opt[CURLOPT_HTTPHEADER] = $this->authService->getAuthHttpHeaders() + array(
            'X-Shopgate-Library-Version: ' . SHOPGATE_LIBRARY_VERSION,
            'X-Shopgate-Plugin-Version: ' . (defined(
                'SHOPGATE_PLUGIN_VERSION'
            )
                ? SHOPGATE_PLUGIN_VERSION
                : 'called outside plugin'),
        );
        $opt[CURLOPT_TIMEOUT]    = 30; // Default timeout 30sec
        $opt[CURLOPT_POST]       = true;

        return ($override + $opt);
    }

    /**
     * Prepares the request and sends it to the configured Shopgate Merchant API.
     *
     * @param mixed[] $parameters      The parameters to send.
     * @param mixed[] $curlOptOverride cURL options to override for this request.
     *
     * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an
     *                                  error occured.
     * @throws ShopgateMerchantApiException
     *
     * @return ShopgateMerchantApiResponse The response object.
     **/
    protected function sendRequest($parameters = array(), $curlOptOverride = array())
    {
        if (!empty($this->shopNumber)) {
            $parameters['shop_number'] = $this->shopNumber;
        }
        $parameters             = !empty($parameters)
            ? array_merge($this->authService->getAuthPostParams(), $parameters)
            : $this->authService->getAuthPostParams();
        $parameters['trace_id'] = 'spa-' . uniqid();

        $this->log(
            'Sending request to "' . $this->apiUrl . '": ' . ShopgateLogger::getInstance()->cleanParamsForLog(
                $parameters
            ),
            ShopgateLogger::LOGTYPE_REQUEST
        );

        // init new auth session and generate cURL options
        $this->authService->startNewSession();
        $curlOpt = $this->getCurlOptArray($curlOptOverride);

        // init cURL connection and send the request
        $curl = curl_init($this->apiUrl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt_array($curl, $curlOpt);
        $response = curl_exec($curl);
        curl_close($curl);

        // check the result
        if (!$response) {
            // exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_NO_CONNECTION,
                null,
                false,
                false
            );
        }

        $decodedResponse = $this->jsonDecode($response, true);

        if (empty($decodedResponse)) {
            // exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                'Response: ' . $response,
                true,
                false
            );
        }

        $responseObject = new ShopgateMerchantApiResponse($decodedResponse);

        if ($decodedResponse['error'] != 0) {
            throw new ShopgateMerchantApiException(
                $decodedResponse['error'],
                $decodedResponse['error_text'],
                $responseObject
            );
        }

        return $responseObject;
    }

    ######################################################################
    ## Following methods represent the Shopgate Merchant API's actions: ##
    ######################################################################

    ######################################################################
    ## Shop                                                             ##
    ######################################################################
    public function getShopInfo($parameters = array())
    {
        $request = array(
            'action' => 'get_shop_info',
        );

        $request = array_merge($request, $parameters);

        return $this->sendRequest($request);
    }

    ######################################################################
    ## Orders                                                           ##
    ######################################################################
    public function getOrders($parameters)
    {
        $request = array(
            'action' => 'get_orders',
        );

        $request  = array_merge($request, $parameters);
        $response = $this->sendRequest($request);

        // check and reorganize the data of the SMA response
        $data = $response->getData();
        if (!isset($data['orders']) || !is_array($data['orders'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                '"orders" is not set or not an array. Response: ' . var_export($data, true)
            );
        }

        $orders = array();
        foreach ($data['orders'] as $order) {
            $orders[] = new ShopgateOrder($order);
        }

        // put the reorganized data into the response object and return ist
        $response->setData($orders);

        return $response;
    }

    public function addOrderDeliveryNote(
        $orderNumber,
        $shippingServiceId,
        $trackingNumber = '',
        $markAsCompleted = false,
        $sendCustomerEmail = false
    ) {
        $request = array(
            'action'              => 'add_order_delivery_note',
            'order_number'        => $orderNumber,
            'shipping_service_id' => $shippingServiceId,
            'tracking_number'     => (string)$trackingNumber,
            'mark_as_completed'   => $markAsCompleted,
            'send_customer_email' => $sendCustomerEmail,
        );

        return $this->sendRequest($request);
    }

    public function setOrderShippingCompleted($orderNumber, $sendCustomerEmail = false)
    {
        $request = array(
            'action'              => 'set_order_shipping_completed',
            'order_number'        => $orderNumber,
            'send_customer_email' => $sendCustomerEmail,
        );

        return $this->sendRequest($request);
    }

    public function cancelOrder(
        $orderNumber,
        $cancelCompleteOrder = true,
        $cancellationItems = array(),
        $cancelShipping = false,
        $cancellationNote = ''
    ) {
        $request = array(
            'action'                => 'cancel_order',
            'order_number'          => $orderNumber,
            'cancel_complete_order' => $cancelCompleteOrder,
            'cancellation_items'    => $cancellationItems,
            'cancel_shipping'       => $cancelShipping,
            'cancellation_note'     => $cancellationNote,
        );

        return $this->sendRequest($request);
    }

    ######################################################################
    ## Mobile Redirect                                                  ##
    ######################################################################

    /**
     * This method is deprecated, please use getMobileRedirectUserAgents().
     *
     * @deprecated
     */
    public function getMobileRedirectKeywords()
    {
        $request = array(
            'action' => 'get_mobile_redirect_keywords',
        );

        $response = $this->sendRequest($request, array(CURLOPT_TIMEOUT => 1));

        return $response->getData();
    }

    public function getMobileRedirectUserAgents()
    {
        $request = array(
            'action' => 'get_mobile_redirect_user_agents',
        );

        $response = $this->sendRequest($request, array(CURLOPT_TIMEOUT => 1));

        $responseData = $response->getData();
        if (!isset($responseData["keywords"]) || !isset($responseData["skip_keywords"])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                "\"keyword\" or \"skip_keyword\" is not set. Response: " . var_export($responseData, true)
            );
        }

        return $response->getData();
    }

    ######################################################################
    ## Items                                                            ##
    ######################################################################
    public function getItems($parameters)
    {
        $parameters['action'] = 'get_items';

        $response = $this->sendRequest($parameters);

        // check and reorganize the data of the SMA response
        $data = $response->getData();
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                '"items" is not set or not an array. Response: ' . var_export($data, true)
            );
        }

        $items = array();
        foreach ($data['items'] as $item) {
            $items[] = new ShopgateItem($item);
        }

        // put the reorganized data into the response object and return ist
        $response->setData($items);

        return $response;
    }

    public function addItem($item)
    {
        $request = ($item instanceof ShopgateItem)
            ? $item->toArray()
            : $item;

        $request['action'] = 'add_item';

        return $this->sendRequest($request);
    }

    public function updateItem($item)
    {
        $request = ($item instanceof ShopgateItem)
            ? $item->toArray()
            : $item;

        $request['action'] = 'update_item';

        return $this->sendRequest($request);
    }

    public function deleteItem($itemNumber)
    {
        $request = array(
            'action'      => 'delete_item',
            'item_number' => $itemNumber,
        );

        return $this->sendRequest($request);
    }

    public function batchAddItems($items)
    {
        $request = array(
            'items'  => array(),
            'action' => 'batch_add_items',
        );

        foreach ($items as $item) {
            $request['items'][] = ($item instanceof ShopgateItem)
                ? $item->toArray()
                : $item;
        }

        return $this->sendRequest($request);
    }

    public function batchUpdateItems($items)
    {
        $request = array(
            'items'  => array(),
            'action' => 'batch_update_items',
        );

        foreach ($items as $item) {
            $request['items'][] = ($item instanceof ShopgateItem)
                ? $item->toArray()
                : $item;
        }

        return $this->sendRequest($request);
    }

    ######################################################################
    ## Categories                                                       ##
    ######################################################################
    public function getCategories($parameters)
    {
        $parameters['action'] = 'get_categories';

        $response = $this->sendRequest($parameters);

        // check and reorganize the data of the SMA response
        $data = $response->getData();
        if (!isset($data['categories']) || !is_array($data['categories'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                '"categories" is not set or not an array. Response: ' . var_export($data, true)
            );
        }

        $categories = array();
        foreach ($data['categories'] as $category) {
            $categories[] = new ShopgateCategory($category);
        }

        // put the reorganized data into the response object and return ist
        $response->setData($categories);

        return $response;
    }

    public function addCategory($category)
    {
        $request = ($category instanceof ShopgateCategory)
            ? $category->toArray()
            : $category;

        $request['action'] = 'add_category';

        return $this->sendRequest($request);
    }

    public function updateCategory($category)
    {
        $request = ($category instanceof ShopgateCategory)
            ? $category->toArray()
            : $category;

        $request['action'] = 'update_category';

        return $this->sendRequest($request);
    }

    public function deleteCategory($categoryNumber, $deleteSubCategories = false, $deleteItems = false)
    {
        $request = array(
            'action'               => 'delete_category',
            'category_number'      => $categoryNumber,
            'delete_subcategories' => $deleteSubCategories
                ? 1
                : 0,
            'delete_items'         => $deleteItems
                ? 1
                : 0,
        );

        return $this->sendRequest($request);
    }

    public function addItemToCategory($itemNumber, $categoryNumber, $orderIndex = null)
    {
        $request = array(
            'action'          => 'add_item_to_category',
            'category_number' => $categoryNumber,
            'item_number'     => $itemNumber,
        );

        if (isset($orderIndex)) {
            $request['order_index'] = $orderIndex;
        }

        return $this->sendRequest($request);
    }

    public function deleteItemFromCategory($itemNumber, $categoryNumber)
    {
        $request = array(
            'action'          => 'delete_item_from_category',
            'category_number' => $categoryNumber,
            'item_number'     => $itemNumber,
        );

        return $this->sendRequest($request);
    }
}

class ShopgateAuthenticationServiceShopgate extends ShopgateObject implements ShopgateAuthenticationServiceInterface
{
    private $customerNumber;

    private $apiKey;

    private $timestamp;

    public function __construct($customerNumber, $apiKey)
    {
        $this->customerNumber = $customerNumber;
        $this->apiKey         = $apiKey;

        $this->startNewSession();
    }

    /**
     * @param ShopgateConfigInterface $config
     */
    public function setup(ShopgateConfigInterface $config)
    {
        // nothing to do here
    }

    public function startNewSession()
    {
        $this->timestamp = time();
    }

    public function getAuthPostParams()
    {
        return array();
    }

    public function getAuthHttpHeaders()
    {
        return array(
            $this->buildAuthUserHeader(),
            $this->buildMerchantApiAuthTokenHeader(),
        );
    }

    public function buildAuthUser()
    {
        return $this->customerNumber . '-' . $this->getTimestamp();
    }

    public function buildAuthUserHeader()
    {
        return self::HEADER_X_SHOPGATE_AUTH_USER . ': ' . $this->buildAuthUser();
    }

    public function buildAuthToken($prefix = 'SMA')
    {
        return $this->buildCustomAuthToken($prefix, $this->customerNumber, $this->getTimestamp(), $this->apiKey);
    }

    public function buildAuthTokenHeader($prefix = 'SMA')
    {
        return self::HEADER_X_SHOPGATE_AUTH_TOKEN . ': ' . $this->buildAuthToken($prefix);
    }

    public function buildMerchantApiAuthTokenHeader()
    {
        return $this->buildAuthTokenHeader('SMA');
    }

    public function buildPluginApiAuthTokenHeader()
    {
        return $this->buildAuthTokenHeader('SPA');
    }

    public function checkAuthentication()
    {
        if (defined('SHOPGATE_DEBUG') && SHOPGATE_DEBUG === 1) {
            return;
        }

        if (empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_USER]) || empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'No authentication data present.'
            );
        }

        // for convenience
        $name  = $_SERVER[self::PHP_X_SHOPGATE_AUTH_USER];
        $token = $_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN];

        // extract customer number and timestamp from username
        $matches = array();
        if (!preg_match('/(?P<customer_number>[1-9][0-9]+)-(?P<timestamp>[1-9][0-9]+)/', $name, $matches)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'Cannot parse: ' . $name . '.'
            );
        }

        // for convenience
        $customer_number = $matches['customer_number'];
        $timestamp       = $matches['timestamp'];

        // request shouldn't be older than 30 minutes or more than 30 minutes in the future
        if (abs($this->getTimestamp() - $timestamp) > (30 * 60)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'Request too old or too far in the future.'
            );
        }

        // create the authentication-password
        $generatedPassword = $this->buildCustomAuthToken('SPA', $customer_number, $timestamp, $this->apiKey);

        // compare customer-number and auth-password and make sure, the API key was set in the configuration
        if (($customer_number != $this->customerNumber) || ($token != $generatedPassword) || (empty($this->apiKey))) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'Invalid authentication data.'
            );
        }
    }

    /**
     * Return current timestamp
     *
     * @return int
     */
    protected function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Generates the auth token with the given parameters.
     *
     * @param string $prefix
     * @param string $customerNumber
     * @param int    $timestamp
     * @param string $apiKey
     *
     * @throws ShopgateLibraryException when no customer number or API key is set
     * @return string The SHA-1 hash Auth Token for Shopgate's Authentication
     */
    protected function buildCustomAuthToken($prefix, $customerNumber, $timestamp, $apiKey)
    {
        if (empty($customerNumber) || empty($apiKey)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_INVALID_VALUE,
                'Shopgate customer number or  API key not set.',
                true,
                false
            );
        }

        return sha1("{$prefix}-{$customerNumber}-{$timestamp}-{$apiKey}");
    }
}

class ShopgateAuthenticationServiceOAuth extends ShopgateObject implements ShopgateAuthenticationServiceInterface
{
    /** @var string */
    private $accessToken;

    /** @var int */
    private $timestamp;

    /** @var string */
    public $apiKey;

    /** @var int */
    public $customerNumber;

    public function __construct($accessToken = null)
    {
        $this->accessToken = $accessToken;

        $this->startNewSession();
    }

    /**
     * @param ShopgateConfigInterface $config
     */
    public function setup(ShopgateConfigInterface $config)
    {
        // needs to check if an old config is present without any access token
        if ($config->getCustomerNumber() && $config->getShopNumber() && $config->getApikey() && !$config->getOauthAccessToken()) {
            // needs to load the non-oauth-url since the new access token needs to be generated using the classic shopgate merchant api authentication
            $apiUrls                = $config->getApiUrls();
            $apiUrl                 = $config->getServer() == 'custom'
                ? str_replace(
                    '/api/merchant2',
                    '/api/merchant',
                    $config->getApiUrl()
                )
                : $apiUrls[$config->getServer()][ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE];
            $smaAuthServiceShopgate = new ShopgateAuthenticationServiceShopgate(
                $config->getCustomerNumber(),
                $config->getApikey()
            );
            $smaAuthServiceShopgate->setup($config);
            $classicSma = new ShopgateMerchantApi($smaAuthServiceShopgate, $config->getShopNumber(), $apiUrl);

            // the "get_shop_info" returns an oauth access token
            $shopInfo = $classicSma->getShopInfo()->getData();

            // set newly generated access token
            $this->accessToken = $shopInfo['oauth_access_token'];

            // create a new settings array
            $shopgateSettingsNew = array(
                $field = 'oauth_access_token' => $shopInfo[$field],
                $field = 'customer_number'    => $shopInfo[$field],
                $field = 'shop_number'        => $shopInfo[$field],
                $field = 'apikey'             => $shopInfo[$field],
                $field = 'alias'              => $shopInfo[$field],
                $field = 'cname'              => $shopInfo[$field],
            );

            // save all shop config data to plugin-config using the configs save method
            $config->load($shopgateSettingsNew);
            $config->save(array_keys($shopgateSettingsNew), true);
        } elseif (!$this->accessToken && $config->getOauthAccessToken()) {
            // this would mean the data was somehow not in sync (should no be happening by default)
            $this->accessToken = $config->getOauthAccessToken();
        } else {
            // skip this since the connection is fully functional or there has not been made any connection at all, yet
            // -> missing data (except the oauth access token) is treated as "no valid connection available"
            // -> in either case there is nothing to do here
        }
    }

    public function startNewSession()
    {
        $this->timestamp = time();
    }

    public function getAuthPostParams()
    {
        return array('access_token' => $this->accessToken);
    }

    public function getAuthHttpHeaders()
    {
        return array();
    }

    public function checkAuthentication()
    {
        if (defined('SHOPGATE_DEBUG') && SHOPGATE_DEBUG === 1) {
            return;
        }

        if (empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_USER]) || empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'No authentication data present.'
            );
        }

        // for convenience
        $name  = $_SERVER[self::PHP_X_SHOPGATE_AUTH_USER];
        $token = $_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN];

        // extract customer number and timestamp from username
        $matches = array();
        if (!preg_match('/(?P<customer_number>[1-9][0-9]+)-(?P<timestamp>[1-9][0-9]+)/', $name, $matches)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'Cannot parse: ' . $name . '.'
            );
        }

        // for convenience
        $customer_number = $matches['customer_number'];
        $timestamp       = $matches['timestamp'];

        // request shouldn't be older than 30 minutes or more than 30 minutes in the future
        if (abs($this->getTimestamp() - $timestamp) > (30 * 60)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'Request too old or too far in the future.'
            );
        }

        // create the authentication-password
        $generatedPassword = $this->buildCustomAuthToken('SPA', $customer_number, $timestamp, $this->apiKey);

        // compare customer-number and auth-password and make sure, the API key was set in the configuration
        if (($customer_number != $this->customerNumber) || ($token != $generatedPassword) || (empty($this->apiKey))) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::AUTHENTICATION_FAILED,
                'Invalid authentication data.'
            );
        }
    }

    public function requestOAuthAccessToken($code, $calledScriptUrl, $tokenRequestUrl)
    {
        // setup request POST parameters
        $parameters = array(
            'client_id'    => 'ShopgatePlugin',
            'grant_type'   => 'authorization_code',
            'redirect_uri' => $calledScriptUrl,
            'code'         => $code,
        );
        // -> setup request headers
        $curlOpt = array(
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => 'ShopgatePlugin/' . (defined(
                'SHOPGATE_PLUGIN_VERSION'
            )
                    ? SHOPGATE_PLUGIN_VERSION
                    : 'called outside plugin'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSLVERSION     => 6,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array(
                'X-Shopgate-Library-Version: ' . SHOPGATE_LIBRARY_VERSION,
                'X-Shopgate-Plugin-Version: ' . (defined(
                    'SHOPGATE_PLUGIN_VERSION'
                )
                    ? SHOPGATE_PLUGIN_VERSION
                    : 'called outside plugin'),
            ),
            CURLOPT_TIMEOUT        => 30, // Default timeout 30sec
            CURLOPT_POST           => true,
        );

        // -> init cURL connection and send the request
        $curl = curl_init($tokenRequestUrl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt_array($curl, $curlOpt);
        $response = curl_exec($curl);
        curl_close($curl);

        // check the curl-result
        if (!$response) {
            // exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
            throw new ShopgateLibraryException(
                ShopgateLibraryException::SHOPGATE_OAUTH_NO_CONNECTION,
                null,
                false,
                false
            );
        }

        // convert returned json string
        $decodedResponse = $this->jsonDecode($response, true);

        // check for valid access token
        $this->accessToken = !empty($decodedResponse['access_token'])
            ? $decodedResponse['access_token']
            : '';
        if (empty($this->accessToken)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::SHOPGATE_OAUTH_MISSING_ACCESS_TOKEN,
                (
                    (!empty($decodedResponse['error']) && !empty($decodedResponse['error_description']))
                    ? ' [Shopgate authorization failure "' . $decodedResponse['error'] . '": ' . $decodedResponse['error_description'] . ']'
                    : ' [Shopgate authorization failure: Unexpected server response]'
                ),
                true
            );
        }

        return $this->accessToken;
    }

    /**
     * Return current timestamp
     *
     * @return int
     */
    protected function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Generates the auth token with the given parameters.
     *
     * @param string $prefix
     * @param string $customerNumber
     * @param int    $timestamp
     * @param string $apiKey
     *
     * @throws ShopgateLibraryException when no customer number or API key is set
     * @return string The SHA-1 hash Auth Token for Shopgate's Authentication
     */
    protected function buildCustomAuthToken($prefix, $customerNumber, $timestamp, $apiKey)
    {
        if (empty($customerNumber) || empty($apiKey)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::CONFIG_INVALID_VALUE,
                'Shopgate customer number or  API key not set.',
                true,
                false
            );
        }

        return sha1("{$prefix}-{$customerNumber}-{$timestamp}-{$apiKey}");
    }
}


/**
 * Wrapper for responses by the Shopgate Merchant API
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateMerchantApiResponse extends ShopgateContainer
{
    protected $sma_version;

    protected $trace_id;

    protected $limit;

    protected $offset;

    protected $has_more_results;

    protected $errors;

    protected $data;

    public function __construct($data = array())
    {
        $this->sma_version      = '';
        $this->trace_id         = '';
        $this->limit            = 1;
        $this->offset           = 1;
        $this->has_more_results = false;
        $this->errors           = array();
        $this->data             = array();

        $unmappedData = $this->loadArray($data);

        if (!empty($unmappedData)) {
            $this->data = $unmappedData;
        }
    }

    /**
     * @param integer $value
     */
    protected function setSmaVersion($value)
    {
        $this->sma_version = $value;
    }

    /**
     * @param integer $value
     */
    protected function setTraceId($value)
    {
        $this->trace_id = $value;
    }

    /**
     * @param integer $value
     */
    protected function setLimit($value)
    {
        $this->limit = $value;
    }

    /**
     * @param integer $value
     */
    protected function setOffset($value)
    {
        $this->offset = $value;
    }

    /**
     * @param bool $value
     */
    protected function setHasMoreResults($value)
    {
        $this->has_more_results = $value;
    }

    /**
     *
     * @param string[] $value
     */
    protected function setErrors($value)
    {
        $this->errors = $value;
    }

    /**
     * @param mixed $value
     */
    public function setData($value)
    {
        $this->data = $value;
    }

    /**
     * @return string
     */
    public function getSmaVersion()
    {
        return $this->sma_version;
    }

    /**
     * @return string
     */
    public function getTraceId()
    {
        return $this->trace_id;
    }

    /**
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return bool
     */
    public function getHasMoreResults()
    {
        return $this->has_more_results;
    }

    /**
     * @return mixed[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function accept(ShopgateContainerVisitor $v)
    {
        return; // not implemented
    }
}

/**
 * This class represents the Shopgate Merchant API as described in our wiki.
 *
 * It provides all available actions, calls to the configured API, retrieves, parses and formats the data.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateMerchantApiInterface
{
    ######################################################################
    ## Shop                                                             ##
    ######################################################################

    /**
     * Represents the "get_shop_info" action.
     *
     * @param mixed[] $parameters
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_orders
     */
    public function getShopInfo($parameters = array());

    ######################################################################
    ## Orders                                                           ##
    ######################################################################

    /**
     * Represents the "get_orders" action.
     *
     * @param mixed[] $parameters
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_orders
     */
    public function getOrders($parameters);

    /**
     * Represents the "add_order_delivery_note" action.
     *
     * @param string $orderNumber
     * @param string $shippingServiceId
     * @param string $trackingNumber
     * @param bool   $markAsCompleted
     * @param bool   $sendCustomerMail
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_add_order_delivery_note
     */
    public function addOrderDeliveryNote(
        $orderNumber,
        $shippingServiceId,
        $trackingNumber = '',
        $markAsCompleted = false,
        $sendCustomerMail = true
    );

    /**
     * Represents the "set_order_shipping_completed" action.
     *
     * @param string $orderNumber
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_set_order_shipping_completed
     */
    public function setOrderShippingCompleted($orderNumber);

    /**
     * Represents the "cancel_order" action.
     *
     * @param string $orderNumber
     * @param bool   $cancelCompleteOrder
     * @param array  $cancellationItems ('item_number' => string, 'quantity' => int)[]
     * @param bool   $cancelShipping
     * @param string $cancellationNote
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_cancel_order
     */
    public function cancelOrder(
        $orderNumber,
        $cancelCompleteOrder = false,
        $cancellationItems = array(),
        $cancelShipping = false,
        $cancellationNote = ''
    );

    ######################################################################
    ## Mobile Redirect                                                  ##
    ######################################################################

    /**
     * Represents the "get_mobile_redirect_keywords" action.
     *
     * This method is deprecated, please use getMobileRedirectUserAgents().
     *
     * @return array('keywords' => string[], 'skipKeywords' => string[])
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @deprecated
     */
    public function getMobileRedirectKeywords();

    /**
     * Represents the "get_mobile_user_agents" action.
     *
     * @return array 'keywords' => string[], 'skip_keywords' => string[]
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_mobile_redirect_user_agents
     */
    public function getMobileRedirectUserAgents();

    ######################################################################
    ## Items                                                            ##
    ######################################################################

    /**
     * Represents the "get_items" action.
     *
     * @param mixed[] $parameters
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_items
     */
    public function getItems($parameters);

    /**
     * Represents the "add_item" action.
     *
     * @param mixed[]|ShopgateItem $item
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_add_item
     */
    public function addItem($item);

    /**
     * Represents the "update_item" action.
     *
     * @param mixed[]|ShopgateItem $item
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_update_item
     */
    public function updateItem($item);

    /**
     * Represents the "delete_item" action.
     *
     * @param string $itemNumber
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_delete_item
     */
    public function deleteItem($itemNumber);

    /**
     * Represents the "batch_add_items" action.
     *
     * @param mixed[]|ShopgateItem[] $items
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_batch_add_items
     */
    public function batchAddItems($items);

    /**
     * Represents the "batch_update_items" action.
     *
     * @param mixed[]|ShopgateItem[] $items
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_batch_update_items
     */
    public function batchUpdateItems($items);

    ######################################################################
    ## Categories                                                       ##
    ######################################################################

    /**
     * Represents the "get_categories" action.
     *
     * @param mixed[] $parameters
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_get_categories
     */
    public function getCategories($parameters);

    /**
     * Represents the "add_category" action.
     *
     * @param mixed[]|ShopgateCategory $category
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_add_category
     */
    public function addCategory($category);

    /**
     * Represents the "update_category" action.
     *
     * @param mixed[]|ShopgateCategory $category
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_update_category
     */
    public function updateCategory($category);

    /**
     * Represents the "delete_category" action.
     *
     * @param string $categoryNumber
     * @param bool   $deleteSubCategories
     * @param bool   $deleteItems
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_delete_category
     */
    public function deleteCategory($categoryNumber, $deleteSubCategories = false, $deleteItems = false);

    /**
     * Represents the "add_item_to_category" action.
     *
     * @param string $itemNumber
     * @param string $categoryNumber
     * @param int    $orderIndex
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_add_item_to_category
     */
    public function addItemToCategory($itemNumber, $categoryNumber, $orderIndex = null);

    /**
     * Represents the "delete_item_from_category" action.
     *
     * @param string $itemNumber
     * @param string $categoryNumber
     *
     * @return ShopgateMerchantApiResponse
     *
     * @throws ShopgateLibraryException in case the connection can't be established
     * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
     *
     * @see http://wiki.shopgate.com/Merchant_API_delete_item_from_category
     */
    public function deleteItemFromCategory($itemNumber, $categoryNumber);
}

/**
 * This class provides methods to check and generate authentication strings.
 *
 * It is used internally by the Shopgate Cart Integration SDK to send requests or check incoming requests.
 *
 * To check authentication on incoming request it accesses the $_SERVER variable which should contain the required X
 * header fields for authentication.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateAuthenticationServiceInterface
{
    const HEADER_X_SHOPGATE_AUTH_USER  = 'X-Shopgate-Auth-User';
    const HEADER_X_SHOPGATE_AUTH_TOKEN = 'X-Shopgate-Auth-Token';
    const PHP_X_SHOPGATE_AUTH_USER     = 'HTTP_X_SHOPGATE_AUTH_USER';
    const PHP_X_SHOPGATE_AUTH_TOKEN    = 'HTTP_X_SHOPGATE_AUTH_TOKEN';

    /**
     * @param ShopgateConfigInterface $config
     */
    public function setup(ShopgateConfigInterface $config);

    /**
     * @return array A list of all necessary post parameters for the authentication process
     */
    public function getAuthPostParams();

    /**
     * @return array A list of all necessary http headers for the authentication process
     */
    public function getAuthHttpHeaders();

    /**
     * @throws ShopgateLibraryException if authentication fails
     */
    public function checkAuthentication();

    /**
     * Start a new Authentication session
     */
    public function startNewSession();
}
