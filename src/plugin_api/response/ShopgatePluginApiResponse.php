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

abstract class ShopgatePluginApiResponse extends ShopgateObject
{
    protected $error;

    protected $error_text;

    protected $trace_id;

    protected $version;

    protected $pluginVersion;

    protected $data;

    public function __construct($traceId, $data = array(), $version = SHOPGATE_LIBRARY_VERSION, $pluginVersion = null)
    {
        $this->error         = 0;
        $this->error_text    = null;
        $this->trace_id      = $traceId;
        $this->data          = $this->recursiveToUtf8($data, ShopgateObject::$sourceEncodings);
        $this->version       = $version;
        $this->pluginVersion = (empty($pluginVersion) && defined('SHOPGATE_PLUGIN_VERSION'))
            ? SHOPGATE_PLUGIN_VERSION
            : $pluginVersion;
    }

    /**
     * Marks the response as error with given code & message.
     *
     * @param int $code
     * @param string $message
     */
    public function markError($code, $message)
    {
        $this->error      = $code;
        $this->error_text = $message;
    }

    public function isError()
    {
        return $this->error !== 0;
    }

    /**
     * Set the response data.
     *
     * Type may depend on the actual implementation.
     *
     * Current classes provided by the SDK will for example have:
     * - a string for plain text responses
     * - an object or array for JSON responses
     * - a file path on larger response bodies like the ones generated in catalog exports
     *
     * @param mixed $data
     *
     * @throws ShopgateLibraryException
     */
    abstract public function setData($data);

    /**
     * A list of headers that would be sent by the send() method.
     *
     * @return string[]
     *
     * @throws ShopgateLibraryException
     */
    abstract public function getHeaders();

    /**
     * Get the body that would be sent by the send() method if applicable.
     *
     * This may return null if the body is streamed or if the response doesn't have a body.
     *
     * @return string|null
     *
     * @throws ShopgateLibraryException
     */
    abstract public function getBody();

    /**
     * Sends headers and flushes the body to stdout if applicable.
     *
     * This may do nothing if the response is streamed.
     *
     * @throws ShopgateLibraryException
     */
    abstract public function send();
}
