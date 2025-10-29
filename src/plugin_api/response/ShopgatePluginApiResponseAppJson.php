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

class ShopgatePluginApiResponseAppJson extends ShopgatePluginApiResponse
{
    /**
     * @var string|null
     */
    protected $bodyCache;

    public function markError($code, $message)
    {
        $this->bodyCache = null;
        parent::markError($code, $message);
    }

    public function setData($data)
    {
        $this->bodyCache = null;
        $this->data = $this->recursiveToUtf8($data, ShopgateObject::$sourceEncodings);
    }

    public function getHeaders()
    {
        return array(
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($this->getBody())
        );
    }

    public function getBody()
    {
        if (!empty($this->bodyCache)) {
            return $this->bodyCache;
        }

        $data                             = array();
        $data['error']                    = $this->error;
        $data['error_text']               = $this->error_text;
        $data['trace_id']                 = $this->trace_id;
        $data['shopgate_library_version'] = $this->version;

        if (!empty($this->pluginVersion)) {
            $data['plugin_version'] = $this->pluginVersion;
        }

        $this->bodyCache = $this
            ->getHelper(self::HELPER_DATASTRUCTURE)
            ->jsonEncode(array_merge($data, $this->data));

        return $this->bodyCache;
    }

    public function send()
    {
        foreach ($this->getHeaders() as $header) {
            header($header);
        }

        echo $this->getBody();
    }
}
