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
class Shopgate_Model_Abstract extends ShopgateObject
{
    /**
     * Object attributes
     *
     * @var array
     */
    protected $data = array();

    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array();

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $underscoreCache = array();

    /**
     * @return array An array with all properties of $this that don't contain an array or an object.
     */
    public function clean()
    {
        $result = array();
        /* @phpstan-ignore-next-line */
        foreach ($this as $k => $v) {
            if (!is_object($v) && !is_array($v)) {
                $result[$k] = $v;
            }
        }

        return $result;
    }

    /**
     * magic get / set
     *
     * @param string $method
     * @param array  $args
     *
     * @return array|null|Shopgate_Model_Abstract
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if (!in_array(substr($method, 3), $this->allowedMethods)) {
            trigger_error('Call to undefined magic method ' . get_class($this) . '::' . $method . '()', E_USER_ERROR);
        }

        switch (substr($method, 0, 3)) {
            case 'get':
                $key  = $this->underscore(substr($method, 3));
                $data = $this->getData(
                    $key,
                    isset($args[0])
                        ? $args[0]
                        : null
                );

                return $data;
            case 'set':
                $key    = $this->underscore(substr($method, 3));
                $result = $this->setData(
                    $key,
                    isset($args[0])
                        ? $args[0]
                        : null
                );

                return $result;
        }

        return null;
    }

    /**
     * Set the data by key (property) or array
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return Shopgate_Model_Abstract
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $arrayKey => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $this->$arrayKey = $value;
                }
            }
        } else {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Returns this object's data.
     *
     * When $key is specified, the data of that property/key is returned or null, if not set.
     *
     * When $key and $index are specified, the data of that property/key and its underlying sub-entity is returned or
     * null if the key or index of the sub-entity is not set or if the sub-entity is not an array.
     *
     * When nothing is passed ($key === ''), the complete internal data is returned as an array. Calling this without
     * parameters will never result in null.
     *
     * @param string|array    $key
     * @param int|string|null $index
     *
     * @return Shopgate_Model_Abstract|array|mixed|null
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->data;
        }

        if (!isset($this->data[$key])) {
            return null;
        }

        $value = $this->data[$key];
        if ($index !== null && is_array($value)) {
            return isset($value[$index])
                ? $value[$index]
                : null;
        }

        return $this->data[$key];
    }

    /**
     * @param string $var
     *
     * @return Shopgate_Model_Abstract|array|mixed|null
     */
    public function __get($var)
    {
        $var = $this->underscore($var);

        return $this->getData($var);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function underscore($name)
    {
        if (isset(self::$underscoreCache[$name])) {
            return self::$underscoreCache[$name];
        }
        $result                       = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$underscoreCache[$name] = $result;

        return $result;
    }

    /**
     * Set row field value
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     *
     * @return void
     */
    public function __set($columnName, $value)
    {
        $this->data[$columnName] = $value;
    }
}
