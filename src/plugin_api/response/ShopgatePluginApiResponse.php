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
     * @param int $code
     * @param string $message
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
