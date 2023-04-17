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

abstract class ShopgatePluginApiResponseExport extends ShopgatePluginApiResponse
{
    public function setData($data)
    {
        if (!file_exists($data) && !preg_match("/^php/", $data)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: ' . $data, true);
        }

        $this->data = $data;
    }

    public function isStream()
    {
        return preg_match("/^php/", $this->data);
    }

    /**
     * Returns the path to the export file or null if the export is streamed.
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->isStream() ? null : $this->data;
    }

    public function send()
    {
        // streams (php://...) are already constantly putting out their content, nothing to do here
        if ($this->isStream()) {
            return;
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
    }
}
