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
     * @param array $data The incoming request's parameters.
     *
     * @return bool false if an error occurred, otherwise true.
     *
     * @throws Exception only if ShopgateConfig::getExternalExceptionHandling() returns "log" or "none"
     */
    public function handleRequest(array $data = array());

    /**
     * @param string $shopgateOAuthActionName
     * @return string
     */
    public function buildShopgateOAuthUrl($shopgateOAuthActionName);
}
