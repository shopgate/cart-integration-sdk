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

if (!defined('DS')) {
    define('DS', '/');
}

if (file_exists(dirname(__FILE__) . DS . 'dev.php')) {
    require_once(dirname(__FILE__).DS.'dev.php');
}

// core
require_once(dirname(__FILE__).DS.'classes'.DS.'core.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'apis.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'client.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'configuration.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'customers.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'orders.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'external_orders.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'items.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'redirect.php');

// models (global / abstract)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'Abstract.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'AbstractExport.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'XmlEmptyObject.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'XmlResultObject.php');

// models (catalog)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Review.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Product.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Price.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'TierPrice.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Category.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'CategoryPath.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Shipping.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Manufacturer.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Visibility.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Property.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Stock.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Identifier.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Tag.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Relation.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Attribute.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Input.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Validation.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Option.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'AttributeGroup.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Attribute.php');

// models (media)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'media' . DS . 'Image.php');

// models (redirect)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'DeeplinkSuffix.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'DeeplinkSuffixValue.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS
    . 'DeeplinkSuffixValueUnset.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS
    . 'DeeplinkSuffixValueDisabled.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'HtmlTag.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'HtmlTagAttribute.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'HtmlTagVariable.php');

// helpers
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'DataStructure.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'Pricing.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'String.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS
    . 'KeywordsManagerInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'KeywordsManager.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'LinkBuilderInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'LinkBuilder.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'MobileRedirectInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'MobileRedirect.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'RedirectorInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'Redirector.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS
    . 'SettingsManagerInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'SettingsManager.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TagsGeneratorInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TagsGenerator.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TemplateParserInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TemplateParser.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS
    . 'JsScriptBuilderInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'JsScriptBuilder.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'type' . DS
    . 'TypeInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'type' . DS . 'Js.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'type' . DS . 'Http.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'strategy' . DS
    . 'LoggingInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'legacy' . DS . 'ShopgateLogger.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'Obfuscator.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'strategy' . DS
    . 'DefaultLogging.php');

require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'GeneratorInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'GeneratorDefault.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'NamedParameterProviderInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'NamedParameterProviderReflection.php');

require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'ExceptionHandler.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'ErrorHandler.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'ShutdownHandler.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'shutdown_handler' . DS
    . 'LastErrorProvider.php');

// vendors
require_once(dirname(__FILE__).DS.'vendors'.DS.'2d_is.php');
include_once(dirname(__FILE__).DS.'vendors'.DS.'JSON.php');
