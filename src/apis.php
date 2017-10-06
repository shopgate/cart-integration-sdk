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
class ShopgatePluginApi extends ShopgateObject implements ShopgatePluginApiInterface
{
    const JOB_SET_SHIPPING_COMPLETED = 'set_shipping_completed';
    const JOB_CANCEL_ORDERS          = 'cancel_orders';
    const JOB_CLEAN_ORDERS           = 'clean_orders';

    /** @var ShopgatePlugin */
    protected $plugin;

    /** @var ShopgateConfigInterface */
    protected $config;

    /** @var ShopgateMerchantApiInterface */
    protected $merchantApi;

    /** @var ShopgateAuthenticationServiceInterface */
    protected $authService;

    /**
     * Parameters passed along the action (usually per POST)
     *
     * @var mixed[]
     */
    protected $params;

    /** @var string[] */
    protected $actionWhitelist;

    /** @var string[] */
    protected $cronJobWhiteList;

    /** @var mixed */
    protected $responseData;

    /** @var ShopgatePluginApiResponse */
    protected $response;

    /** @var bool */
    protected $preventResponseOutput;

    /**
     * this list is used for setting max_execution_time and memory_limt
     *
     * @var array of string
     */
    protected $exportActionList;

    /** @var array */
    protected $authlessActionWhitelist;

    /** @var string The trace ID of the incoming request. */
    protected $trace_id;

    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;

    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    public function __construct(
        ShopgateConfigInterface $config,
        ShopgateAuthenticationServiceInterface $authService,
        ShopgateMerchantApiInterface $merchantApi,
        ShopgatePlugin $plugin,
        ShopgatePluginApiResponse $response = null,
        Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator = null,
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging = null
    ) {
        $this->config                = $config;
        $this->authService           = $authService;
        $this->merchantApi           = $merchantApi;
        $this->plugin                = $plugin;
        $this->response              = $response;
        $this->stackTraceGenerator   = $stackTraceGenerator;
        $this->logging               = $logging;
        $this->responseData          = array();
        $this->preventResponseOutput = false;

        $this->cronJobWhiteList = $config->getCronJobWhiteList();

        // list of action that do not require authentication
        $this->authlessActionWhitelist = array(
            'receive_authorization',
        );

        // initialize action whitelist
        $this->actionWhitelist = array(
            'add_order',
            'check_cart',
            'check_stock',
            'clear_cache',
            'clear_log_file',
            'cron',
            'get_categories',
            'get_categories_csv',
            'get_customer',
            'get_debug_info',
            'get_items',
            'get_items_csv',
            'get_log_file',
            'get_media_csv',
            'get_orders',
            'get_reviews',
            'get_reviews_csv',
            'get_settings',
            'ping',
            'receive_authorization',
            'redeem_coupons',
            'register_customer',
            'set_settings',
            'sync_favourite_list',
            'update_order',
        );

        $this->exportActionList = array(
            'get_items',
            'get_items_csv',
            'get_categories',
            'get_categories_csv',
            'get_reviews',
            'get_reviews_csv',
            'get_media_csv',
        );
    }

    public function handleRequest(array $data = array())
    {
        $errortext = '';

        $this->setEnableErrorReporting();

        $processId = function_exists('posix_getpid')
            ? posix_getpid()
            : (function_exists('getmypid')
                ? getmypid()
                : 0);

        // log incoming request
        $this->log(
            'process ID: ' . $processId . ' parameters: '
            . ShopgateLogger::getInstance()->cleanParamsForLog($data),
            ShopgateLogger::LOGTYPE_ACCESS
        );

        // save the params
        $this->params = $data;

        // save trace_id
        if (isset($this->params['trace_id'])) {
            $this->trace_id = $this->params['trace_id'];
        }

        try {
            if (!in_array($this->params['action'], $this->authlessActionWhitelist)) {
                $this->authService->checkAuthentication();
            }

            // check if the request is for the correct shop number or an adapter-plugin
            if (
                !$this->config->getIsShopgateAdapter() &&
                !empty($this->params['shop_number']) &&
                ($this->params['shop_number'] != $this->config->getShopNumber())
            ) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_UNKNOWN_SHOP_NUMBER,
                    "{$this->params['shop_number']}"
                );
            }

            // check if an action to call has been passed, is known and enabled
            if (empty($this->params['action'])) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_NO_ACTION,
                    'Passed parameters: ' . var_export($this->params, true)
                );
            }

            // check if the action is white-listed
            if (!in_array($this->params['action'], $this->actionWhitelist)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_UNKNOWN_ACTION,
                    "{$this->params['action']}"
                );
            }

            // check if action is enabled in the config
            $configArray = $this->config->toArray();
            if (empty($configArray['enable_' . $this->params['action']])) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_DISABLED_ACTION,
                    "{$this->params['action']}"
                );
            }

            // response output is active by default and can be deactivated to allow actions to print a custom output
            $this->preventResponseOutput = false;

            // call the action
            $action = $this->camelize($this->params['action']);

            if (in_array($this->params['action'], $this->exportActionList)) {
                if (isset($this->params['memory_limit'])) {
                    $this->plugin->setExportMemoryLimit((int)$this->params['memory_limit']);
                } else {
                    $this->plugin->setExportMemoryLimit($this->config->getDefaultMemoryLimit());
                }

                if (isset($this->params['max_execution_time'])) {
                    $this->plugin->setExportTimeLimit((int)$this->params['max_execution_time']);
                } else {
                    $this->plugin->setExportTimeLimit($this->config->getDefaultExecutionTime());
                }
            }

            $this->{$action}();
        } catch (ShopgateLibraryException $e) {
            $error     = $e->getCode();
            $errortext = $e->getMessage();
        } catch (ShopgateMerchantApiException $e) {
            $error     = ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED;
            $errortext = ShopgateLibraryException::getMessageFor(
                    ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED
                ) . ': "' . $e->getCode() . ' - ' . $e->getMessage() . '"';
        } catch (Exception $e) {
            $message = get_class($e) . " with code: {$e->getCode()} and message: '{$e->getMessage()}'";

            // new ShopgateLibraryException to build proper error message and perform logging
            $e         = new ShopgateLibraryException($message, null, false, true, $e);
            $error     = $e->getCode();
            $errortext = $e->getMessage();
        }

        // build stack trace if generator is available
        $stackTrace = (!empty($e) && !empty($this->stackTraceGenerator))
            ? $this->stackTraceGenerator->generate($e)
            : '';

        // log error if there is any
        $this->logApiError($errortext, $stackTrace);

        // print out the response
        if (!empty($error)) {
            if (empty($this->response)) {
                $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
            }
            $this->response->markError($error, $errortext);
        }

        if (empty($this->response)) {
            trigger_error('No response object defined. This should _never_ happen.', E_USER_ERROR);
        }

        if (!$this->preventResponseOutput) {
            $this->response->setData($this->responseData);
            if (empty($this->params['error_reporting']) && ob_get_contents()) {
                ob_clean();
            }
            $this->response->send();
        }

        // return true or false
        return (empty($error));
    }

    ######################################################################
    ## Following methods represent the Shopgate Plugin API's actions:   ##
    ######################################################################

    /**
     * Represents the "ping" action.
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_ping
     */
    protected function ping()
    {
        // obfuscate data relevant for authentication
        $config                       = $this->config->toArray();
        $config['customer_number']    = ShopgateLogger::OBFUSCATION_STRING;
        $config['shop_number']        = ShopgateLogger::OBFUSCATION_STRING;
        $config['apikey']             = ShopgateLogger::OBFUSCATION_STRING;
        $config['oauth_access_token'] = ShopgateLogger::OBFUSCATION_STRING;

        // prepare response data array
        $this->responseData['pong']                     = 'OK';
        $this->responseData['configuration']            = $config;
        $this->responseData['plugin_info']              = $this->plugin->createPluginInfo();
        $this->responseData['permissions']              = $this->getPermissions();
        $this->responseData['php_version']              = phpversion();
        $this->responseData['php_config']               = $this->getPhpSettings();
        $this->responseData['php_curl']                 = function_exists('curl_version')
            ? curl_version()
            : 'No PHP-CURL installed';
        $this->responseData['php_extensions']           = get_loaded_extensions();
        $this->responseData['shopgate_library_version'] = SHOPGATE_LIBRARY_VERSION;
        $this->responseData['plugin_version']           = defined(
            'SHOPGATE_PLUGIN_VERSION'
        )
            ? SHOPGATE_PLUGIN_VERSION
            : 'UNKNOWN';
        $this->responseData['shop_info']                = $this->plugin->createShopInfo();

        // set data and return response
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
    }

    /**
     * Represents the "debug" action.
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_ping
     */
    protected function getDebugInfo()
    {
        // prepare response data array
        $this->responseData = $this->plugin->getDebugInfo();

        // set data and return response
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
    }

    /**
     * Represents the "cron" action.
     *
     * @throws ShopgateLibraryException
     */
    protected function cron()
    {
        if (empty($this->params['jobs']) || !is_array($this->params['jobs'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_CRON_NO_JOBS);
        }

        $unknownJobs = $this->getUnknownCronJobs($this->params['jobs']);
        if (!empty($unknownJobs)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB,
                implode(', ', $unknownJobs),
                true
            );
        }

        // time tracking
        $starttime = microtime(true);

        // references
        $message    = '';
        $errorcount = 0;

        // execute the jobs
        foreach ($this->params['jobs'] as $job) {
            if (empty($job['job_params'])) {
                $job['job_params'] = array();
            }

            try {
                $jobErrorcount = 0;

                // job execution
                $this->plugin->cron($job['job_name'], $job['job_params'], $message, $jobErrorcount);

                // check error count
                if ($jobErrorcount > 0) {
                    $message    .= "{$jobErrorcount} errors occured while executing cron job '{$job['job_name']}'\n";
                    $errorcount += $jobErrorcount;
                }
            } catch (Exception $e) {
                $errorcount++;
                $message .= 'Job aborted: "' . $e->getMessage() . '"';
            }
        }

        // time tracking
        $endtime = microtime(true);
        $runtime = $endtime - $starttime;
        $runtime = round($runtime, 4);

        // prepare response
        $responses                          = array();
        $responses['message']               = $message;
        $responses['execution_error_count'] = $errorcount;
        $responses['execution_time']        = $runtime;

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
        $this->responseData = $responses;
    }

    /**
     * @param array $cronJobs
     *
     * @return array
     *
     * @throws ShopgateLibraryException
     */
    protected function getUnknownCronJobs(array $cronJobs)
    {
        $unknownCronJobs = array();

        foreach ($cronJobs as $cronJob) {
            if (empty($cronJob['job_name'])) {
                throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_CRON_NO_JOB_NAME);
            }

            if (!in_array($cronJob['job_name'], $this->cronJobWhiteList, true)) {
                $unknownCronJobs[] = $cronJob['job_name'];
            }
        }

        return $unknownCronJobs;
    }

    /**
     * Represents the "add_order" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_add_order
     */
    protected function addOrder()
    {
        if (!isset($this->params['order_number'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
        }
        /** @var ShopgateOrder[] $orders */
        $orders = $this->merchantApi->getOrders(
            array(
                'order_numbers[0]' => $this->params['order_number'],
                'with_items'       => 1,
            )
        )->getData();
        if (empty($orders)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                '"orders" not set or empty. Response: ' . var_export($orders, true)
            );
        }
        if (count($orders) > 1) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                'more than one order in response. Response: ' . var_export($orders, true)
            );
        }

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $orderData = $this->plugin->addOrder($orders[0]);
        if (is_array($orderData)) {
            $this->responseData = $orderData;
        } else {
            $this->responseData['external_order_id']     = $orderData;
            $this->responseData['external_order_number'] = null;
        }
    }

    /**
     * Represents the "update_order" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order
     */
    protected function updateOrder()
    {
        if (!isset($this->params['order_number'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
        }
        /** @var ShopgateOrder[] $orders */
        $orders = $this->merchantApi->getOrders(
            array(
                'order_numbers[0]' => $this->params['order_number'],
                'with_items'       => 1,
            )
        )->getData();

        if (empty($orders)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                '"order" not set or empty. Response: ' . var_export($orders, true)
            );
        }

        if (count($orders) > 1) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                'more than one order in response. Response: ' . var_export($orders, true)
            );
        }

        $payment  = 0;
        $shipping = 0;

        if (isset($this->params['payment'])) {
            $payment = (int)$this->params['payment'];
        }
        if (isset($this->params['shipping'])) {
            $shipping = (int)$this->params['shipping'];
        }

        $orders[0]->setUpdatePayment($payment);
        $orders[0]->setUpdateShipping($shipping);

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $orderData = $this->plugin->updateOrder($orders[0]);
        if (is_array($orderData)) {
            $this->responseData = $orderData;
        } else {
            $this->responseData['external_order_id']     = $orderData;
            $this->responseData['external_order_number'] = null;
        }
    }

    /**
     * Represents the "redeem_coupons" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_redeem_coupons
     */
    protected function redeemCoupons()
    {
        if (!isset($this->params['cart'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_CART);
        }

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $cart       = new ShopgateCart($this->params['cart']);
        $couponData = $this->plugin->redeemCoupons($cart);

        if (!is_array($couponData)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                'Plugin Response: ' . var_export($couponData, true)
            );
        }

        // Workaround:
        // $couponData was specified to be a ShopgateExternalCoupon[].
        // Now supports the same format as checkCart(), i.e. array('external_coupons' => ShopgateExternalCoupon[]).
        if (!empty($couponData['external_coupons']) && is_array($couponData['external_coupons'])) {
            $couponData = $couponData['external_coupons'];
        }

        $responseData = array("external_coupons" => array());
        foreach ($couponData as $coupon) {
            if (!is_object($coupon) || !($coupon instanceof ShopgateExternalCoupon)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    'Plugin Response: ' . var_export($coupon, true)
                );
            }

            $coupon = $coupon->toArray();
            unset($coupon["order_index"]);

            $responseData["external_coupons"][] = $coupon;
        }

        $this->responseData = $responseData;
    }

    /**
     * Represents the "check_cart" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart
     */
    protected function checkCart()
    {
        if (!isset($this->params['cart'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_CART);
        }

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $cart         = new ShopgateCart($this->params['cart']);
        $cartData     = $this->plugin->checkCart($cart);
        $responseData = array();

        $responseData['internal_cart_info'] = (isset($cartData['internal_cart_info']))
            ? $cartData['internal_cart_info']
            : null;

        if (!is_array($cartData)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                '$cartData is of type: ' . is_object($cartData)
                    ? get_class($cartData)
                    : gettype($cartData)
            );
        }

        $responseData['currency'] = '';
        if ($cart->getCurrency()) {
            $responseData['currency'] = $cart->getCurrency();
        }

        if (!empty($cartData['currency'])) {
            $responseData['currency'] = $cartData['currency'];
        }

        if (!empty($cartData['customer']) && $cartCustomer = $cartData['customer']) {
            /** @var ShopgateCartCustomer $cartCustomer */
            if (!is_object($cartCustomer) || !($cartCustomer instanceof ShopgateCartCustomer)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    '$cartCustomer is of type: ' . is_object($cartCustomer)
                        ? get_class($cartCustomer)
                        : gettype($cartCustomer)
                );
            }
            foreach ($cartCustomer->getCustomerGroups() as $cartCustomerGroup) {
                /** @var ShopgateCartCustomerGroup $cartCustomerGroup */
                if (!is_object($cartCustomerGroup) || !($cartCustomerGroup instanceof ShopgateCartCustomerGroup)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$cartCustomerGroup is of type: ' . is_object($cartCustomerGroup)
                            ? get_class($cartCustomerGroup)
                            : gettype($cartCustomerGroup)
                    );
                }
            }
            $responseData["customer"] = $cartCustomer->toArray();
        }

        $shippingMethods = array();
        if (!empty($cartData['shipping_methods'])) {
            foreach ($cartData["shipping_methods"] as $shippingMethod) {
                /** @var ShopgateShippingMethod $shippingMethod */
                if (!is_object($shippingMethod) || !($shippingMethod instanceof ShopgateShippingMethod)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$shippingMethod is of type: ' . is_object($shippingMethod)
                            ? get_class($shippingMethod)
                            : gettype($shippingMethod)
                    );
                }
                $shippingMethods[] = $shippingMethod->toArray();
            }
        }
        $responseData["shipping_methods"] = $shippingMethods;

        $paymentMethods = array();
        if (!empty($cartData['payment_methods'])) {
            foreach ($cartData["payment_methods"] as $paymentMethod) {
                /** @var ShopgatePaymentMethod $paymentMethod */
                if (!is_object($paymentMethod) || !($paymentMethod instanceof ShopgatePaymentMethod)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$paymentMethod is of type: ' . is_object($paymentMethod)
                            ? get_class($paymentMethod)
                            : gettype($paymentMethod)
                    );
                }
                $paymentMethods[] = $paymentMethod->toArray();
            }
        }
        $responseData["payment_methods"] = $paymentMethods;

        $cartItems = array();
        if (!empty($cartData['items'])) {
            foreach ($cartData["items"] as $cartItem) {
                /** @var ShopgateCartItem $cartItem */
                if (!is_object($cartItem) || !($cartItem instanceof ShopgateCartItem)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$cartItem is of type: ' . is_object($cartItem)
                            ? get_class($cartItem)
                            : gettype($cartItem)
                    );
                }
                $cartItems[] = $cartItem->toArray();
            }
        }
        $responseData["items"] = $cartItems;

        $coupons = array();
        if (!empty($cartData['external_coupons'])) {
            foreach ($cartData["external_coupons"] as $coupon) {
                /** @var ShopgateExternalCoupon $coupon */
                if (!is_object($coupon) || !($coupon instanceof ShopgateExternalCoupon)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$coupon is of type: ' . is_object($coupon)
                            ? get_class($coupon)
                            : gettype($coupon)
                    );
                }
                $coupon = $coupon->toArray();
                unset($coupon["order_index"]);
                $coupons[] = $coupon;
            }
        }
        $responseData["external_coupons"] = $coupons;

        $this->responseData = $responseData;
    }

    /**
     * Represents the "check_stock" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_stock
     */
    protected function checkStock()
    {
        if (!isset($this->params['items'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ITEMS);
        }

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $cart = new ShopgateCart();
        $cart->setItems($this->params['items']);
        $items        = $this->plugin->checkStock($cart);
        $responseData = array();

        if (!is_array($items)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                '$cartData Is type of : ' . is_object($items)
                    ? get_class($items)
                    : gettype($items)
            );
        }

        $cartItems = array();
        if (!empty($items)) {
            foreach ($items as $cartItem) {
                /** @var ShopgateCartItem $cartItem */
                if (!is_object($cartItem) || !($cartItem instanceof ShopgateCartItem)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$cartItem Is type of : ' . is_object($cartItem)
                            ? get_class($cartItem)
                            : gettype($cartItem)
                    );
                }
                $item               = $cartItem->toArray();
                $notNeededArrayKeys = array('qty_buyable', 'unit_amount', 'unit_amount_with_tax');
                foreach ($notNeededArrayKeys as $key) {
                    if (array_key_exists($key, $item)) {
                        unset($item[$key]);
                    }
                }

                $cartItems[] = $item;
            }
        }

        $responseData["items"] = $cartItems;

        $this->responseData = $responseData;
    }

    /**
     * Represents the "get_settings" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_settings
     */
    protected function getSettings()
    {
        $this->responseData = $this->plugin->getSettings();

        // set data and return response
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
    }

    /**
     * Represents the "set_settings" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_set_settings
     */
    protected function setSettings()
    {
        if (empty($this->params['shopgate_settings']) || !is_array($this->params['shopgate_settings'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_NO_SHOPGATE_SETTINGS,
                'Request: ' . var_export($this->params, true)
            );
        }
        // settings that may never be changed:
        $shopgateSettingsBlacklist = array(
            'shop_number',
            'customer_number',
            'apikey',
            'plugin_name',
            'export_folder_path',
            'log_folder_path',
            'cache_folder_path',
            'items_csv_filename',
            'categories_csv_filename',
            'reviews_csv_filename',
            'access_log_filename',
            'error_log_filename',
            'request_log_filename',
            'debug_log_filename',
            'redirect_keyword_cache_filename',
            'redirect_skip_keyword_cache_filename',
        );

        // filter the new settings
        $shopgateSettingsNew = array();
        $shopgateSettingsOld = $this->config->toArray();
        foreach ($this->params['shopgate_settings'] as $setting) {
            if (!isset($setting['name'])) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_NO_SHOPGATE_SETTINGS,
                    'Wrong format: ' . var_export($setting, true)
                );
            }

            if (in_array($setting['name'], $shopgateSettingsBlacklist)) {
                continue;
            }

            if (!in_array($setting['name'], array_keys($shopgateSettingsOld))) {
                continue;
            }

            $shopgateSettingsNew[$setting['name']] = isset($setting['value'])
                ? $setting['value']
                : null;
        }

        $this->config->load($shopgateSettingsNew);
        $this->config->save(array_keys($shopgateSettingsNew), true);

        $diff = array();
        foreach ($shopgateSettingsNew as $setting => $value) {
            $diff[] = array('name' => $setting, 'old' => $shopgateSettingsOld[$setting], 'new' => $value);
        }

        // set data and return response
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
        $this->responseData['shopgate_settings'] = $diff;
    }

    protected function getOrders()
    {
        if (!isset($this->params['customer_token'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_TOKEN);
        }
        if (!isset($this->params['customer_language'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_LANGUAGE);
        }

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $orders = $this->plugin->getOrders(
            $this->params['customer_token'],
            $this->params['customer_language'],
            isset($this->params['limit'])
                ? $this->params['limit']
                : 10,
            isset($this->params['offset'])
                ? $this->params['offset']
                : 0,
            isset($this->params['order_date_from'])
                ? $this->params['order_date_from']
                : '',
            isset($this->params['sort_order'])
                ? $this->params['sort_order']
                : 'created_desc'
        );

        if (!is_array($orders)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                'Plugin Response: ' . var_export($orders, true)
            );
        }

        $resOrders = array();
        foreach ($orders as $order) {
            if (!($order instanceof ShopgateExternalOrder)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    'Plugin Response: ' . var_export($orders, true)
                );
            }
            $resOrders[] = $order->toArray();
        }

        $this->responseData['orders'] = $resOrders;
    }

    protected function syncFavouriteList()
    {
        if (!isset($this->params['customer_token'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_TOKEN);
        }
        if (empty($this->params['items'])) {
            $this->params['items'] = array(); // a hack because there is no "empty array" representation in POST
        }
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }

        $syncItems = array();
        foreach ($this->params['items'] as $syncItem) {
            if (!isset($syncItem['item_number'])) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_ITEM_FORMAT,
                    'missing required param "item_number"'
                );
            }
            if (!isset($syncItem['status'])) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_ITEM_FORMAT,
                    'missing required param "status"'
                );
            }

            $syncItems[] = new ShopgateSyncItem($syncItem);
        }

        $updatedSyncItems = $this->plugin->syncFavouriteList($this->params['customer_token'], $syncItems);

        if (!is_array($updatedSyncItems)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                'Plugin Response: ' . var_export($updatedSyncItems, true)
            );
        }

        $resItems = array();
        foreach ($updatedSyncItems as $updatedSyncItem) {
            if (!($updatedSyncItem instanceof ShopgateSyncItem)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    'Plugin Response: ' . var_export($updatedSyncItem, true)
                );
            }
            $resItem = $updatedSyncItem->toArray();
            unset($resItem['status']);

            $resItems[] = $resItem;
        }

        $this->responseData['items'] = $resItems;
    }

    /**
     * Represents the "get_customer" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_customer
     */
    protected function getCustomer()
    {
        if (!isset($this->params['user'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_USER);
        }

        if (!isset($this->params['pass'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_PASS);
        }

        $customer = $this->plugin->getCustomer($this->params['user'], $this->params['pass']);
        if (!is_object($customer) || !($customer instanceof ShopgateCustomer)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                'Plugin Response: ' . var_export($customer, true)
            );
        }

        foreach ($customer->getCustomerGroups() as $customerGroup) {
            /** @var ShopgateCustomerGroup $customerGroup */
            if (!is_object($customerGroup) || !($customerGroup instanceof ShopgateCustomerGroup)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    '$customerGroup is of type: ' . is_object($customerGroup)
                        ? get_class($customerGroup)
                        : gettype($customerGroup)
                );
            }
        }

        $customerData = $customer->toArray();
        $addressList  = $customerData['addresses'];
        unset($customerData['addresses']);

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
        $this->responseData["user_data"] = $customerData;
        $this->responseData["addresses"] = $addressList;
    }

    /**
     *
     * Represents the "register_customer" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_register_customer
     */
    protected function registerCustomer()
    {
        if (!isset($this->params['user'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_USER);
        }

        if (!isset($this->params['pass'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_PASS);
        }

        if (!isset($this->params['user_data'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_NO_USER_DATA,
                "missing user_data",
                true
            );
        }

        if (!$this->config->getEnableGetCustomer()) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_DISABLED_ACTION,
                "Action 'get_customer' is not activated but is needed by register_customer",
                true
            );
        }

        $user     = $this->params['user'];
        $pass     = $this->params['pass'];
        $customer = new ShopgateCustomer($this->params['user_data']);

        $userData = $this->params["user_data"];

        if (isset($userData['addresses']) && is_array($userData['addresses'])) {
            $addresses = array();
            foreach ($userData['addresses'] as $address) {
                $addresses[] = new ShopgateAddress($address);
            }
            $customer->setAddresses($addresses);
        }

        $this->plugin->registerCustomer($user, $pass, $customer);

        $newCustomer = $this->plugin->getCustomer($user, $pass);

        $customerData = $newCustomer->toArray();
        $addressList  = $customerData['addresses'];
        unset($customerData['addresses']);

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
        $this->responseData["user_data"] = $customerData;
        $this->responseData["addresses"] = $addressList;
    }

    /**
     * Represents the "get_media_csv" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_media_csv
     */
    protected function getMediaCsv()
    {
        if (isset($this->params['limit']) && isset($this->params['offset'])) {
            $this->plugin->setExportLimit((int)$this->params['limit']);
            $this->plugin->setExportOffset((int)$this->params['offset']);
            $this->plugin->setSplittedExport(true);
        }

        // generate / update items csv file if requested
        $this->plugin->startGetMediaCsv();

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseTextCsvExport($this->trace_id);
        }
        $this->responseData = $this->config->getMediaCsvPath();
    }

    /**
     * Represents the "get_items_csv" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items_csv
     */
    protected function getItemsCsv()
    {
        if (isset($this->params['limit']) && isset($this->params['offset'])) {
            $this->plugin->setExportLimit((int)$this->params['limit']);
            $this->plugin->setExportOffset((int)$this->params['offset']);
            $this->plugin->setSplittedExport(true);
        }

        // generate / update items csv file if requested
        $this->plugin->startGetItemsCsv();

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseTextCsvExport($this->trace_id);
        }
        $this->responseData = $this->config->getItemsCsvPath();
    }

    /**
     * Represents the "get_items" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items
     */
    protected function getItems()
    {
        $limit        = isset($this->params['limit'])
            ? (int)$this->params['limit']
            : null;
        $offset       = isset($this->params['offset'])
            ? (int)$this->params['offset']
            : null;
        $uids         = isset($this->params['uids'])
            ? (array)$this->params['uids']
            : array();
        $responseType = isset($this->params['response_type'])
            ? $this->params['response_type']
            : false;

        $supportedResponseTypes = $this->config->getSupportedResponseTypes();
        if (!empty($responseType) && !in_array($responseType, $supportedResponseTypes['get_items'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_UNSUPPORTED_RESPONSE_TYPE,
                'Requested type: "' . $responseType . '"'
            );
        }

        $this->plugin->startGetItems($limit, $offset, $uids, $responseType);

        switch ($responseType) {
            default:
            case 'xml':
                $response     = new ShopgatePluginApiResponseAppXmlExport($this->trace_id);
                $responseData = $this->config->getItemsXmlPath();
                break;

            case 'json':
                $response     = new ShopgatePluginApiResponseAppJsonExport($this->trace_id);
                $responseData = $this->config->getItemsJsonPath();
                break;
        }

        if (empty($this->response)) {
            $this->response = $response;
        }

        $this->responseData = $responseData;
    }

    /**
     * Represents the "get_categories" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories
     */
    protected function getCategories()
    {
        $limit        = isset($this->params['limit'])
            ? (int)$this->params['limit']
            : null;
        $offset       = isset($this->params['offset'])
            ? (int)$this->params['offset']
            : null;
        $uids         = isset($this->params['uids'])
            ? (array)$this->params['uids']
            : array();
        $responseType = isset($this->params['response_type'])
            ? $this->params['response_type']
            : false;

        $supportedResponseTypes = $this->config->getSupportedResponseTypes();
        if (!empty($responseType) && !in_array($responseType, $supportedResponseTypes['get_categories'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_UNSUPPORTED_RESPONSE_TYPE,
                'Requested type: "' . $responseType . '"'
            );
        }

        $this->plugin->startGetCategories($limit, $offset, $uids, $responseType);

        switch ($responseType) {
            default:
            case 'xml':
                $response     = new ShopgatePluginApiResponseAppXmlExport($this->trace_id);
                $responseData = $this->config->getCategoriesXmlPath();
                break;

            case 'json':
                $response     = new ShopgatePluginApiResponseAppJsonExport($this->trace_id);
                $responseData = $this->config->getCategoriesJsonPath();
                break;
        }

        if (empty($this->response)) {
            $this->response = $response;
        }

        $this->responseData = $responseData;
    }

    /**
     * Represents the "get_categories_csv" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories_csv
     */
    protected function getCategoriesCsv()
    {
        if (isset($this->params['limit']) && isset($this->params['offset'])) {
            $this->plugin->setExportLimit((int)$this->params['limit']);
            $this->plugin->setExportOffset((int)$this->params['offset']);
            $this->plugin->setSplittedExport(true);
        }

        // generate / update categories csv file
        $this->plugin->startGetCategoriesCsv();

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseTextCsvExport($this->trace_id);
        }
        $this->responseData = $this->config->getCategoriesCsvPath();
    }

    /**
     * Represents the "get_reviews_csv" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_reviews_csv
     */
    protected function getReviewsCsv()
    {
        if (isset($this->params['limit']) && isset($this->params['offset'])) {
            $this->plugin->setExportLimit((int)$this->params['limit']);
            $this->plugin->setExportOffset((int)$this->params['offset']);
            $this->plugin->setSplittedExport(true);
        }

        // generate / update reviews csv file
        $this->plugin->startGetReviewsCsv();

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseTextCsvExport($this->trace_id);
        }
        $this->responseData = $this->config->getReviewsCsvPath();
    }

    /**
     * Represents the "get_categories" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories
     */
    protected function getReviews()
    {
        $limit        = isset($this->params['limit'])
            ? (int)$this->params['limit']
            : null;
        $offset       = isset($this->params['offset'])
            ? (int)$this->params['offset']
            : null;
        $uids         = isset($this->params['uids'])
            ? (array)$this->params['uids']
            : array();
        $responseType = isset($this->params['response_type'])
            ? $this->params['response_type']
            : false;

        $supportedResponseTypes = $this->config->getSupportedResponseTypes();
        if (!empty($responseType) && !in_array($responseType, $supportedResponseTypes['get_reviews'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_UNSUPPORTED_RESPONSE_TYPE,
                'Requested type: "' . $responseType . '"'
            );
        }

        $this->plugin->startGetReviews($limit, $offset, $uids, $responseType);

        switch ($responseType) {
            default:
            case 'xml':
                $response     = new ShopgatePluginApiResponseAppXmlExport($this->trace_id);
                $responseData = $this->config->getReviewsXmlPath();
                break;

            case 'json':
                $response     = new ShopgatePluginApiResponseAppJsonExport($this->trace_id);
                $responseData = $this->config->getReviewsJsonPath();
                break;
        }

        if (empty($this->response)) {
            $this->response = $response;
        }

        $this->responseData = $responseData;
    }

    /**
     * Represents the "get_log_file" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_log_file
     */
    protected function getLogFile()
    {
        // disable debug log for this action
        $logger = ShopgateLogger::getInstance();
        $logger->disableDebug();
        $logger->keepDebugLog(true);

        $type  = (empty($this->params['log_type']))
            ? ShopgateLogger::LOGTYPE_ERROR
            : $this->params['log_type'];
        $lines = (!isset($this->params['lines']))
            ? null
            : $this->params['lines'];

        $log = $logger->tail($type, $lines);

        // return the requested log file content and end the script
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseTextPlain($this->trace_id);
        }
        $this->responseData = $log;
    }

    /**
     * Represents the "clear_log_file" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_clear_log_file
     */

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function clearLogFile()
    {
        if (empty($this->params['log_type'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE);
        }

        switch ($this->params['log_type']) {
            case ShopgateLogger::LOGTYPE_ACCESS:
                $logFilePath = $this->config->getAccessLogPath();
                break;
            case ShopgateLogger::LOGTYPE_REQUEST:
                $logFilePath = $this->config->getRequestLogPath();
                break;
            case ShopgateLogger::LOGTYPE_ERROR:
                $logFilePath = $this->config->getErrorLogPath();
                break;
            case ShopgateLogger::LOGTYPE_DEBUG:
                $logFilePath = $this->config->getDebugLogPath();
                break;
            default:
                throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE);
        }

        $logFilePointer = @fopen($logFilePath, 'w');
        if ($logFilePointer === false) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR,
                "File: $logFilePath",
                true
            );
        }
        fclose($logFilePointer);

        // return the path of the deleted log file
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
    }

    /**
     * Represents the "clear_cache" action.
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_clear_cache
     */

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function clearCache()
    {
        $files = $this->plugin->clearCache();
        if (!is_array($files)) {
            $files = array();
        }

        $files[] = $this->config->getRedirectKeywordCachePath();
        $files[] = $this->config->getRedirectSkipKeywordCachePath();

        $errorFiles = array();
        foreach ($files as $file) {
            if (@file_exists($file) && is_file($file)) {
                if (!@unlink($file)) {
                    $errorFiles[] = $file;
                }
            }
        }

        if (!empty($errorFiles)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_FILE_DELETE_ERROR,
                "Cannot delete files (" . implode(', ', $errorFiles) . ")",
                true
            );
        }

        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
    }

    /**
     * Represents the "receive_authorization" action (OAUTH ONLY!).
     * Please make sure to allow calls to this action only for admin users with proper rights (only call inside of the
     * admin area or check for admin-login set when providing an action from outside of the admin area)
     *
     * @see ShopgatePlugin::checkAdminLogin method
     *
     * @throws ShopgateLibraryException
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_receive_authorization
     */
    protected function receiveAuthorization()
    {
        if ($this->config->getSmaAuthServiceClassName(
            ) != ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_INVALID_ACTION,
                '=> "receive_authorization" action can only be called for plugins with SMA-AuthService set to "OAuth" type',
                true
            );
        }

        if (empty($this->params['code'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_AUTHORIZATION_CODE);
        }

        $tokenRequestUrl = $this->buildShopgateOAuthUrl('token');
        // the "receive_authorization" action url is needed (again) for requesting an access token
        $calledScriptUrl = $this->plugin->getActionUrl($this->params['action']);

        // Re-initialize the OAuth auth service object and the ShopgateMerchantAPI object
        $smaAuthService = new ShopgateAuthenticationServiceOAuth();
        $smaAuthService->requestOAuthAccessToken($this->params['code'], $calledScriptUrl, $tokenRequestUrl);

        // at this Point there is a valid access token available, since this point would not be reached otherwise
        // -> get a new ShopgateMerchantApi object, containing a fully configured OAuth auth service including the access token
        $this->merchantApi = new ShopgateMerchantApi($smaAuthService, null, $this->config->getApiUrl());

        // load all shop info via the MerchantAPI and store it in the config (via OAuth and a valid access token)
        $shopInfo = $this->merchantApi->getShopInfo()->getData();
        if (empty($shopInfo)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                '-> "shop info" not set. Response data: ' . var_export($shopInfo, true)
            );
        }

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
        $this->config->load($shopgateSettingsNew);
        $this->config->save(array_keys($shopgateSettingsNew), true);

        // no specific data needs to be returned
        if (empty($this->response)) {
            $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
        }
        $this->responseData = array();
    }

    ###############
    ### Helpers ###
    ###############

    public function buildShopgateOAuthUrl($shopgateOAuthActionName)
    {
        // based on the oauth action name the subdomain can differ
        switch ($shopgateOAuthActionName) {
            case 'authorize':
                $subdomain = 'admin';
                break;
            case 'token':
                $subdomain = 'api';
                break;
            default:
                $subdomain = 'www';
                break;
        }

        // the access token needs to be requested first (compute a request target url for this)
        $merchantApiUrl = $this->config->getApiUrl();
        if ($this->config->getServer() == 'custom') {
            // defaults to https://<subdomain>.<hostname>/api[controller]/<merchant-action-name> for custom server
            $requestServerHost    = explode('/api/', $merchantApiUrl);
            $requestServerHost[0] = str_replace('://api.', "://{$subdomain}.", $requestServerHost[0]);
            $requestServerHost    = trim($requestServerHost[0], '/');
        } else {
            // defaults to https://<subdomain>.<hostname>/<merchant-action-name> for live, pg and sl server
            $matches = array();
            preg_match(
                '/^(?P<protocol>http(s)?:\/\/)api.(?P<hostname>[^\/]+)\/merchant.*$/',
                $merchantApiUrl,
                $matches
            );
            $protocol          = (!empty($matches['protocol'])
                ? $matches['protocol']
                : 'https://');
            $hostname          = (!empty($matches['hostname'])
                ? $matches['hostname']
                : 'shopgate.com');
            $requestServerHost = "{$protocol}{$subdomain}.{$hostname}";
        }

        return $requestServerHost . '/oauth/' . $shopgateOAuthActionName;
    }

    private function getPhpSettings()
    {
        $settingDetails = array();

        $allSettings = function_exists('ini_get_all')
            ? ini_get_all()
            : array();

        $settings = array(
            'max_execution_time',
            'memory_limit',
            'allow_call_time_pass_reference',
            'disable_functions',
            'display_errors',
            'file_uploads',
            'include_path',
            'register_globals',
            'safe_mode',
        );

        foreach ($settings as $setting) {
            $settingDetails[$setting] = (!empty($allSettings[$setting]))
                ? $allSettings[$setting]
                : 'undefined';
        }

        return $settingDetails;
    }

    private function getPermissions()
    {
        $permissions = array();
        $files       = array(
            # default paths
            SHOPGATE_BASE_DIR . '/config/myconfig.php',
            $this->config->getExportFolderPath(),
            $this->config->getLogFolderPath(),
            $this->config->getCacheFolderPath(),

            # csv files
            $this->config->getItemsCsvPath(),
            $this->config->getCategoriesCsvPath(),
            $this->config->getReviewsCsvPath(),

            # xml files
            $this->config->getItemsXmlPath(),
            $this->config->getCategoriesXmlPath(),

            # log files
            $this->config->getAccessLogPath(),
            $this->config->getRequestLogPath(),
            $this->config->getErrorLogPath(),
            $this->config->getDebugLogPath(),

            # cache files
            $this->config->getRedirectKeywordCachePath(),
            $this->config->getRedirectSkipKeywordCachePath(),
        );

        foreach ($files as $file) {
            $permissions[] = $this->_getFileMeta($file, 1);
        }

        return $permissions;
    }

    /**
     * get meta data for given file.
     * if file doesn't exists, move up to parent directory
     *
     * @param string $file (max numbers of parent directory lookups)
     * @param int    $parentLevel
     *
     * @return array with file meta data
     */
    private function _getFileMeta($file, $parentLevel = 0)
    {
        $meta = array('file' => $file);

        if ($meta['exist'] = (bool)file_exists($file)) {
            $meta['writeable'] = (bool)is_writable($file);

            $uid = fileowner($file);
            if (function_exists('posix_getpwuid')) {
                $uinfo = posix_getpwuid($uid);
                $uid   = $uinfo['name'];
            }

            $gid = filegroup($file);
            if (function_exists('posix_getgrgid')) {
                $ginfo = posix_getgrgid($gid);
                $gid   = $ginfo['name'];
            }

            $meta['owner']                  = $uid;
            $meta['group']                  = $gid;
            $meta['permission']             = substr(sprintf('%o', fileperms($file)), -4);
            $meta['last_modification_time'] = date('d.m.Y H:i:s', filemtime($file));

            if (is_file($file)) {
                $meta['filesize'] = round(filesize($file) / (1024 * 1024), 4) . ' MB';
            }
        } elseif ($parentLevel > 0) {
            $fInfo = pathinfo($file);
            if (file_exists($fInfo['dirname'])) {
                $meta['parent_dir'] = $this->_getFileMeta($fInfo['dirname'], --$parentLevel);
            }
        }

        return $meta;
    }

    /**
     * enable error reporting to show exeption on request
     */
    private function setEnableErrorReporting()
    {
        @error_reporting(E_ERROR | E_CORE_ERROR | E_USER_ERROR);
        @ini_set('display_errors', 1);
    }

    /**
     * @param string $stackTrace
     * @param string $errortext
     */
    private function logApiError($errortext, $stackTrace)
    {
        if (empty($stackTrace) && empty($errortext)) {
            return;
        }

        if (!empty($this->logging)) {
            $this->logging->log(
                $errortext,
                Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
                $stackTrace
            );
        } else {
            ShopgateLogger::getInstance()
                ->log($errortext . ' ### Stack trace omitted due to use of deprecated ShopgateLogger.');
        }
    }
}

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

        $opt[CURLOPT_HEADER]         = false;
        $opt[CURLOPT_USERAGENT]      = 'ShopgatePlugin/' . (defined(
                'SHOPGATE_PLUGIN_VERSION'
            )
                ? SHOPGATE_PLUGIN_VERSION
                : 'called outside plugin');
        $opt[CURLOPT_SSL_VERIFYPEER] = false;
        $opt[CURLOPT_RETURNTRANSFER] = true;
        $opt[CURLOPT_HTTPHEADER]     = array(
            'X-Shopgate-Library-Version: ' . SHOPGATE_LIBRARY_VERSION,
            'X-Shopgate-Plugin-Version: ' . (defined(
                'SHOPGATE_PLUGIN_VERSION'
            )
                ? SHOPGATE_PLUGIN_VERSION
                : 'called outside plugin'),
        );
        $opt[CURLOPT_HTTPHEADER]     = !empty($opt[CURLOPT_HTTPHEADER])
            ? ($this->authService->getAuthHttpHeaders() + $opt[CURLOPT_HTTPHEADER])
            : $this->authService->getAuthHttpHeaders();
        $opt[CURLOPT_TIMEOUT]        = 30; // Default timeout 30sec
        $opt[CURLOPT_POST]           = true;

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
        if ($config->getCustomerNumber() && $config->getShopNumber() && $config->getApikey(
            ) && !$config->getOauthAccessToken()) {
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
            CURLOPT_SSL_VERIFYPEER => false,
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

        if (empty($response)) {
            // exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
            throw new ShopgateLibraryException(
                ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE,
                'Response: ' . $response,
                true,
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
 * Wrapper for responses by the Shopgate Plugin API.
 *
 * Each content type is represented by a subclass.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgatePluginApiResponse extends ShopgateObject
{
    protected $error;

    protected $error_text;

    protected $trace_id;

    protected $version;

    protected $pluginVersion;

    protected $data;

    public function __construct($traceId, $version = SHOPGATE_LIBRARY_VERSION, $pluginVersion = null)
    {
        $this->error         = 0;
        $this->error_text    = null;
        $this->trace_id      = $traceId;
        $this->version       = $version;
        $this->pluginVersion = (empty($pluginVersion) && defined(
                'SHOPGATE_PLUGIN_VERSION'
            ))
            ? SHOPGATE_PLUGIN_VERSION
            : $pluginVersion;
    }

    /**
     * Marks the response as error.
     *
     * @param $code
     * @param $message
     */
    public function markError($code, $message)
    {
        $this->error      = $code;
        $this->error_text = $message;
    }

    public function setData($data)
    {
        $this->data = $this->recursiveToUtf8($data, ShopgateObject::$sourceEncodings);
    }

    abstract public function send();
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseTextPlain extends ShopgatePluginApiResponse
{
    public function send()
    {
        header('HTTP/1.0 200 OK');
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Length: ' . strlen($this->data));
        echo $this->data;
        exit;
    }
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseAppJson extends ShopgatePluginApiResponse
{
    public function send()
    {
        $data                             = array();
        $data['error']                    = $this->error;
        $data['error_text']               = $this->error_text;
        $data['trace_id']                 = $this->trace_id;
        $data['shopgate_library_version'] = $this->version;

        if (!empty($this->pluginVersion)) {
            $data['plugin_version'] = $this->pluginVersion;
        }

        $this->data      = array_merge($data, $this->data);
        $jsonEncodedData = $this->jsonEncode($this->data);

        header("HTTP/1.0 200 OK");
        header("Content-Type: application/json");
        header('Content-Length: ' . strlen($jsonEncodedData));
        echo $jsonEncodedData;
    }
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgatePluginApiResponseExport extends ShopgatePluginApiResponse
{
    public function setData($data)
    {
        if (!file_exists($data) && !preg_match("/^php/", $data)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: ' . $data, true);
        }

        $this->data = $data;
    }

    public function send()
    {
        if (preg_match("/^php/", $this->data)) { // don't output files when the "file" is a stream
            exit;
        }

        $fp = @fopen($this->data, 'r');
        if (!$fp) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR,
                'File: ' . $this->data,
                true
            );
        }

        // output headers ...
        header('HTTP/1.0 200 OK');
        $headers = $this->getHeaders();
        foreach ($headers as $header) {
            header($header);
        }

        // ... and the file
        while ($line = fgets($fp, 4096)) {
            echo $line;
        }

        // clean up and leave
        fclose($fp);
        exit;
    }

    /**
     * Returns all except the "200 OK" HTTP headers to send before outputting the file.
     *
     * @return string[]
     */
    abstract protected function getHeaders();
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseTextCsvExport extends ShopgatePluginApiResponseExport
{
    protected function getHeaders()
    {
        return array(
            'Content-Type: text/csv',
            'Content-Length: ' . filesize($this->data),
            'Content-Disposition: attachment; filename="' . basename($this->data) . '"',
        );
    }
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseAppXmlExport extends ShopgatePluginApiResponseExport
{
    protected function getHeaders()
    {
        return array(
            'Content-Type: application/xml',
            'Content-Length: ' . filesize($this->data),
            'Content-Disposition: attachment; filename="' . basename($this->data) . '"',
        );
    }
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseAppJsonExport extends ShopgatePluginApiResponseExport
{
    protected function getHeaders()
    {
        return array(
            'Content-Type: application/json',
            'Content-Length: ' . filesize($this->data),
            'Content-Disposition: attachment; filename="' . basename($this->data) . '"',
        );
    }
}

class ShopgatePluginApiResponseAppGzipExport extends ShopgatePluginApiResponseExport
{
    protected function getHeaders()
    {
        return array(
            'Content-Type: application/gzip',
            'Content-Disposition: attachment; filename="' . basename($this->data) . '"',
        );
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
     * @param $value mixed
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
 * This interface represents the Shopgate Plugin API as described in our wiki.
 *
 * It provides all available actions and calls the plugin implementation's callback methods for data retrieval if
 * necessary.
 *
 * @see    http://wiki.shopgate.com/Shopgate_Plugin_API
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgatePluginApiInterface
{
    /**
     * Inspects an incoming request, performs the requested actions, prepares and prints out the response to the
     * requesting entity.
     *
     * Note that the method usually returns true or false on completion, depending on the success of the operation.
     * However, some actions such as the get_*_csv actions, might stop the script after execution to prevent invalid
     * data being appended to the output.
     *
     * @param mixed[] $data The incoming request's parameters.
     *
     * @return bool false if an error occured, otherwise true.
     */
    public function handleRequest(array $data = array());
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
     * @param        array ('item_number' => string, 'quantity' => int)[] $cancellationItems
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
