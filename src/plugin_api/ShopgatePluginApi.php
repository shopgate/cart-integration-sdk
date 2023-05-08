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
     * @var array
     */
    protected $params;

    /** @var string[] */
    protected $actionWhitelist;

    /** @var string[] */
    protected $cronJobWhiteList;

    /**
     * this list is used for setting max_execution_time and memory_limit
     *
     * @var array of string
     */
    protected $exportActionList;

    /** @var array */
    protected $authlessActionWhitelist;

    /** @var string The trace ID of the incoming request. */
    protected $trace_id;

    /** @var ?Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;

    /** @var ?Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    public function __construct(
        ShopgateConfigInterface $config,
        ShopgateAuthenticationServiceInterface $authService,
        ShopgateMerchantApiInterface $merchantApi,
        ShopgatePlugin $plugin,
        Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator = null,
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging = null
    ) {
        $this->config                = $config;
        $this->authService           = $authService;
        $this->merchantApi           = $merchantApi;
        $this->plugin                = $plugin;
        $this->stackTraceGenerator   = $stackTraceGenerator;
        $this->logging               = $logging;

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
        $errorText = '';

        $this->setEnableErrorReporting();

        $processId = 0;
        if (function_exists('posix_getpid')) {
            $processId = posix_getpid();
        } elseif (function_exists('getmypid')) {
            $processId =  getmypid();
        }

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

        $response = null;
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

            $response = $this->{$action}();
        } catch (ShopgateLibraryException $sge) {
            $error     = $sge->getCode();
            $errorText = $sge->getMessage();
        } catch (ShopgateMerchantApiException $sge) {
            $error     = ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED;
            $errorText = ShopgateLibraryException::getMessageFor(
                    ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED
                ) . ': "' . $sge->getCode() . ' - ' . $sge->getMessage() . '"';
        } catch (Exception $e) {
            if ($this->config->getExternalExceptionHandling() === ShopgateConfig::EXTERNAL_EXCEPTION_HANDLING_NONE) {
                throw $e;
            }

            $message = get_class($e) . " with code: {$e->getCode()} and message: '{$e->getMessage()}'";

            // new ShopgateLibraryException to build proper error message and perform logging
            $sge       = new ShopgateLibraryException($message, null, false, true, $e);
            $error     = $sge->getCode();
            $errorText = $sge->getMessage();
        }

        // build stack trace if generator is available
        $stackTrace = (!empty($sge) && !empty($this->stackTraceGenerator))
            ? $this->stackTraceGenerator->generate($sge)
            : '';

        // log error if there is any
        $this->logApiError($errorText, $stackTrace);

        // if external exception handling is set to logging only, let the original exception bubble up
        if (!empty($e) && $this->config->getExternalExceptionHandling() === ShopgateConfig::EXTERNAL_EXCEPTION_HANDLING_LOG) {
            throw $e;
        }

        // print out the response
        if (!empty($error)) {
            if (empty($response)) {
                $response = new ShopgatePluginApiResponseAppJson($this->trace_id);
            }
            $response->markError($error, $errorText);
        }

        if (!($response instanceof ShopgatePluginApiResponse)) {
            // Just a meaningful error message for a programming error where one of the "action functions" doesn't return a response object.
            trigger_error('No response object defined. This should _never_ happen.', E_USER_ERROR);
        }

        // filter out any output that would mess up our response, unless error reporting is active
        if (empty($this->params['error_reporting']) && ob_get_contents()) {
            ob_clean();
        }

        if (!$this->config->getExternalResponseHandling()) {
            $response->send();

            // Keep the "old" behavior of exiting after flushing some response types. It used to help with invalid XML
            // in some cases. If not exiting, true or false was returned, depending on whether on error occurred or not.
            if ($response instanceof ShopgatePluginApiResponseTextPlain || $response instanceof ShopgatePluginApiResponseExport) {
                exit;
            } else {
                return !$response->isError();
            }
        }

        return $response;
    }

    ######################################################################
    ## Following methods represent the Shopgate Plugin API's actions:   ##
    ######################################################################

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/system-info/ping
     */
    protected function ping()
    {
        // obfuscate data relevant for authentication
        $config                       = $this->config->toArray();
        $config['customer_number']    = ShopgateLogger::OBFUSCATION_STRING;
        $config['shop_number']        = ShopgateLogger::OBFUSCATION_STRING;
        $config['apikey']             = ShopgateLogger::OBFUSCATION_STRING;
        $config['oauth_access_token'] = ShopgateLogger::OBFUSCATION_STRING;

        // set data and return response
        return new ShopgatePluginApiResponseAppJson($this->trace_id, array(
            'pong' => 'OK',
            'configuration' => $config,
            'plugin_info' => $this->plugin->createPluginInfo(),
            'permissions' => $this->getPermissions(),
            'php_version' => phpversion(),
            'php_config' => $this->getPhpSettings(),
            'php_curl' => function_exists('curl_version') ? curl_version() : 'No PHP-CURL installed',
            'php_extensions' => get_loaded_extensions(),
            'shopgate_library_version' => SHOPGATE_LIBRARY_VERSION,
            'plugin_version' => defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'UNKNOWN',
            'shop_info' => $this->plugin->createShopInfo()
        ));
    }

    /**
     * Represents the "debug" action.
     *
     * @return ShopgatePluginApiResponseAppJson
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/system-info/ping
     */
    protected function getDebugInfo()
    {
        return new ShopgatePluginApiResponseAppJson($this->trace_id, $this->plugin->getDebugInfo());
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
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
        $startTime = microtime(true);

        // references
        $message    = '';
        $errorCount = 0;

        // execute the jobs
        foreach ($this->params['jobs'] as $job) {
            if (empty($job['job_params'])) {
                $job['job_params'] = array();
            }

            try {
                $jobErrorCount = 0;

                // job execution
                $this->plugin->cron($job['job_name'], $job['job_params'], $message, $jobErrorCount);

                // check error count
                if ($jobErrorCount > 0) {
                    $message    .= "{$jobErrorCount} errors occurred while executing cron job '{$job['job_name']}'\n";
                    $errorCount += $jobErrorCount;
                }
            } catch (Exception $e) {
                $errorCount++;
                $message .= 'Job aborted: "' . $e->getMessage() . '"';
            }
        }

        // time tracking
        $endTime = microtime(true);
        $runTime = $endTime - $startTime;
        $runTime = round($runTime, 4);

        // prepare response
        $responses                          = array();
        $responses['message']               = $message;
        $responses['execution_error_count'] = $errorCount;
        $responses['execution_time']        = $runTime;

        return new ShopgatePluginApiResponseAppJson($this->trace_id, $responses);
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
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     * @throws ShopgateMerchantApiException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/order/add-order
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

        $orderData = $this->plugin->addOrder($orders[0]);
        $responseData = is_array($orderData)
            ? $orderData
            : array('external_order_id' => $orderData, 'external_order_number' => null);

        return new ShopgatePluginApiResponseAppJson($this->trace_id, $responseData);
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     * @throws ShopgateMerchantApiException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/order/update-order
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

        $orderData = $this->plugin->updateOrder($orders[0]);
        $responseData = is_array($orderData)
            ? $orderData
            : array('external_order_id' => $orderData, 'external_order_number' => null);

        return new ShopgatePluginApiResponseAppJson($this->trace_id, $responseData);
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @deprecated
     */
    protected function redeemCoupons()
    {
        if (!isset($this->params['cart'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_CART);
        }

        $cart       = new ShopgateCart($this->params['cart']);
        $couponData = $this->plugin->redeemCoupons($cart);

        /** @noinspection PhpUnreachableStatementInspection redeemCoupons() might still have been overridden */
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id, $responseData);
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/cart/check-cart
     */
    protected function checkCart()
    {
        if (!isset($this->params['cart'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_CART);
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
                /* @phpstan-ignore-next-line */
                '$cartData is of type: ' . (is_object($cartData) ? get_class($cartData) : gettype($cartData))
            );
        }

        $responseData['currency'] = '';
        if ($cart->getCurrency()) {
            $responseData['currency'] = $cart->getCurrency();
        }

        if (!empty($cartData['currency'])) {
            $responseData['currency'] = $cartData['currency'];
        }

        if (!empty($cartData['customer'])) {
            $cartCustomer = $cartData['customer'];

            if (!is_object($cartCustomer) || !($cartCustomer instanceof ShopgateCartCustomer)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    '$cartCustomer is of type: ' . (is_object($cartCustomer) ? get_class($cartCustomer) : gettype($cartCustomer))
                );
            }
            foreach ($cartCustomer->getCustomerGroups() as $cartCustomerGroup) {
                /* @phpstan-ignore-next-line */
                if (!is_object($cartCustomerGroup) || !($cartCustomerGroup instanceof ShopgateCartCustomerGroup)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        /* @phpstan-ignore-next-line */
                        '$cartCustomerGroup is of type: ' . (is_object($cartCustomerGroup) ? get_class($cartCustomerGroup) : gettype($cartCustomerGroup))
                    );
                }
            }
            $responseData["customer"] = $cartCustomer->toArray();
        }

        $shippingMethods = array();
        if (!empty($cartData['shipping_methods'])) {
            foreach ($cartData["shipping_methods"] as $shippingMethod) {
                if (!is_object($shippingMethod) || !($shippingMethod instanceof ShopgateShippingMethod)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$shippingMethod is of type: ' . (is_object($shippingMethod) ? get_class($shippingMethod) : gettype($shippingMethod))
                    );
                }
                $shippingMethods[] = $shippingMethod->toArray();
            }
        }
        $responseData["shipping_methods"] = $shippingMethods;

        $paymentMethods = array();
        if (!empty($cartData['payment_methods'])) {
            foreach ($cartData["payment_methods"] as $paymentMethod) {
                if (!is_object($paymentMethod) || !($paymentMethod instanceof ShopgatePaymentMethod)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$paymentMethod is of type: ' . (is_object($paymentMethod) ? get_class($paymentMethod) : gettype($paymentMethod))
                    );
                }
                $paymentMethods[] = $paymentMethod->toArray();
            }
        }
        $responseData["payment_methods"] = $paymentMethods;

        $cartItems = array();
        if (!empty($cartData['items'])) {
            foreach ($cartData["items"] as $cartItem) {
                if (!is_object($cartItem) || !($cartItem instanceof ShopgateCartItem)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$cartItem is of type: ' . (is_object($cartItem) ? get_class($cartItem) : gettype($cartItem))
                    );
                }
                $cartItems[] = $cartItem->toArray();
            }
        }
        $responseData["items"] = $cartItems;

        $coupons = array();
        if (!empty($cartData['external_coupons'])) {
            foreach ($cartData["external_coupons"] as $coupon) {
                if (!is_object($coupon) || !($coupon instanceof ShopgateExternalCoupon)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        '$coupon is of type: ' . (is_object($coupon) ? get_class($coupon) : gettype($coupon))
                    );
                }
                $coupon = $coupon->toArray();
                unset($coupon["order_index"]);
                $coupons[] = $coupon;
            }
        }
        $responseData["external_coupons"] = $coupons;

        return new ShopgatePluginApiResponseAppJson($this->trace_id, $responseData);
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/stock/check-stock
     */
    protected function checkStock()
    {
        if (!isset($this->params['items'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ITEMS);
        }

        $cart = new ShopgateCart();
        $cart->setItems($this->params['items']);
        $items        = $this->plugin->checkStock($cart);

        if (!is_array($items)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                /* @phpstan-ignore-next-line */
                '$cartData Is type of : ' . (is_object($items) ? get_class($items) : gettype($items))
            );
        }

        $cartItems = array();
        if (!empty($items)) {
            foreach ($items as $cartItem) {
                /* @phpstan-ignore-next-line */
                if (!is_object($cartItem) || !($cartItem instanceof ShopgateCartItem)) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                        /* @phpstan-ignore-next-line */
                        '$cartItem Is type of : ' . (is_object($cartItem) ? get_class($cartItem) : gettype($cartItem))
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id, array('items' => $cartItems));
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_settings
     */
    protected function getSettings()
    {
        return new ShopgatePluginApiResponseAppJson($this->trace_id, $this->plugin->getSettings());
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id, array('shopgate_settings' => $diff));
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/order/get-orders
     */
    protected function getOrders()
    {
        if (!isset($this->params['customer_token'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_TOKEN);
        }
        if (!isset($this->params['customer_language'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_LANGUAGE);
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id, array('orders' => $resOrders));
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     */
    protected function syncFavouriteList()
    {
        if (!isset($this->params['customer_token'])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_TOKEN);
        }
        if (empty($this->params['items'])) {
            $this->params['items'] = array(); // a hack because there is no "empty array" representation in POST
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id, array('items' => $resItems));
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/customer/get-customer
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
        /* @phpstan-ignore-next-line */
        if (!is_object($customer) || !($customer instanceof ShopgateCustomer)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                'Plugin Response: ' . var_export($customer, true)
            );
        }

        foreach ($customer->getCustomerGroups() as $customerGroup) {
            /* @phpstan-ignore-next-line */
            if (!is_object($customerGroup) || !($customerGroup instanceof ShopgateCustomerGroup)) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT,
                    /* @phpstan-ignore-next-line */
                    '$customerGroup is of type: ' . (is_object($customerGroup) ? get_class($customerGroup) : gettype($customerGroup))
                );
            }
        }

        $customerData = $customer->toArray();
        $addressList  = $customerData['addresses'];
        unset($customerData['addresses']);

        return new ShopgatePluginApiResponseAppJson($this->trace_id, array('user_data'=> $customerData, 'addresses' => $addressList));
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/customer/register-customer
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id, array('user_data'=> $customerData, 'addresses' => $addressList));
    }

    /**
     * @return ShopgatePluginApiResponseTextCsvExport
     *
     * @throws ShopgateLibraryException
     *
     * @deprecated
     */
    protected function getMediaCsv()
    {
        if (isset($this->params['limit']) && isset($this->params['offset'])) {
            $this->plugin->setExportLimit((int)$this->params['limit']);
            $this->plugin->setExportOffset((int)$this->params['offset']);
            $this->plugin->setSplittedExport(true);
        }

        $this->plugin->startGetMediaCsv();

        return new ShopgatePluginApiResponseTextCsvExport($this->trace_id, $this->config->getMediaCsvPath());
    }

    /**
     * @return ShopgatePluginApiResponseTextCsvExport
     *
     * @throws ShopgateLibraryException
     */
    protected function getItemsCsv()
    {
        if (isset($this->params['limit']) && isset($this->params['offset'])) {
            $this->plugin->setExportLimit((int)$this->params['limit']);
            $this->plugin->setExportOffset((int)$this->params['offset']);
            $this->plugin->setSplittedExport(true);
        }

        $this->plugin->startGetItemsCsv();

        return new ShopgatePluginApiResponseTextCsvExport($this->trace_id, $this->config->getItemsCsvPath());
    }

    /**
     * @return ShopgatePluginApiResponseAppXmlExport|ShopgatePluginApiResponseAppJsonExport
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/export/get-items
     */
    protected function getItems()
    {
        $limit        = isset($this->params['limit']) ? (int)$this->params['limit'] : null;
        $offset       = isset($this->params['offset']) ? (int)$this->params['offset'] : null;
        $uids         = isset($this->params['uids']) ? (array)$this->params['uids'] : array();
        $responseType = isset($this->params['response_type']) ? $this->params['response_type'] : false;

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
                return new ShopgatePluginApiResponseAppXmlExport($this->trace_id, $this->config->getItemsXmlPath());

            case 'json':
                return new ShopgatePluginApiResponseAppJsonExport($this->trace_id, $this->config->getItemsJsonPath());
        }
    }

    /**
     * @return ShopgatePluginApiResponseAppXmlExport|ShopgatePluginApiResponseAppJsonExport
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/export/get-categories
     */
    protected function getCategories()
    {
        $limit        = isset($this->params['limit']) ? (int)$this->params['limit'] : null;
        $offset       = isset($this->params['offset']) ? (int)$this->params['offset'] : null;
        $uids         = isset($this->params['uids']) ? (array)$this->params['uids'] : array();
        $responseType = isset($this->params['response_type']) ? $this->params['response_type'] : false;

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
                return new ShopgatePluginApiResponseAppXmlExport($this->trace_id, $this->config->getCategoriesXmlPath());

            case 'json':
                return new ShopgatePluginApiResponseAppJsonExport($this->trace_id, $this->config->getCategoriesJsonPath());
        }
    }

    /**
     * @return ShopgatePluginApiResponseTextCsvExport
     *
     * @throws ShopgateLibraryException
     *
     * @deprecated
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

        return new ShopgatePluginApiResponseTextCsvExport($this->trace_id, $this->config->getCategoriesCsvPath());
    }

    /**
     * @return ShopgatePluginApiResponseTextCsvExport
     *
     * @throws ShopgateLibraryException
     *
     * @deprecated
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

        return new ShopgatePluginApiResponseTextCsvExport($this->trace_id, $this->config->getReviewsCsvPath());
    }

    /**
     * @return ShopgatePluginApiResponseAppXmlExport|ShopgatePluginApiResponseAppJsonExport
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/export/get-reviews
     */
    protected function getReviews()
    {
        $limit        = isset($this->params['limit']) ? (int)$this->params['limit'] : null;
        $offset       = isset($this->params['offset']) ? (int)$this->params['offset'] : null;
        $uids         = isset($this->params['uids']) ? (array)$this->params['uids'] : array();
        $responseType = isset($this->params['response_type']) ? $this->params['response_type'] : false;

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
                return new ShopgatePluginApiResponseAppXmlExport($this->trace_id, $this->config->getReviewsXmlPath());

            case 'json':
                return new ShopgatePluginApiResponseAppJsonExport($this->trace_id, $this->config->getReviewsJsonPath());
        }
    }

    /**
     * @return ShopgatePluginApiResponseTextPlain
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/system-info/get-log-file
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

        return new ShopgatePluginApiResponseTextPlain($this->trace_id, $logger->tail($type, $lines));
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/system-info/clear-log-file
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id);
    }

    /**
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     *
     * @see https://developer.shopgate.com/references/cart-integration/plugin-api/system-info/clear-cache
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

        return new ShopgatePluginApiResponseAppJson($this->trace_id);
    }

    /**
     * Represents the "receive_authorization" action (OAUTH ONLY!).
     * Please make sure to allow calls to this action only for admin users with proper rights (only call inside of the
     * admin area or check for admin-login set when providing an action from outside of the admin area)
     *
     * @return ShopgatePluginApiResponseAppJson
     *
     * @throws ShopgateLibraryException
     * @throws ShopgateMerchantApiException
     *
     * @see ShopgatePlugin::checkAdminLogin method
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
        return new ShopgatePluginApiResponseAppJson($this->trace_id, array());
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
     * enable error reporting to show exception on request
     */
    private function setEnableErrorReporting()
    {
        @error_reporting(E_ERROR | E_CORE_ERROR | E_USER_ERROR);
        @ini_set('display_errors', '1');
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
