<?php

/**
 * Shopgate GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file AFL_license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to interfaces@shopgate.com so we can send you a copy immediately.
 *
 * @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright  Shopgate GmbH
 * @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 06.03.14
 * Time: 09:44
 *
 * File: Abstract.php
 */

class Shopgate_Model_Abstract
{

    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $_underscoreCache = array();

    public function clean()
    {
        $result = array();
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
     * @param array $args
     *
     * @return array|Shopgate_Model_Abstract|null
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_underscore(substr($method, 3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                return $data;
            case 'set' :
                $key = $this->_underscore(substr($method, 3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                return $result;

        }

        return null;
    }

    /** set the data by key or array
     * @param      $key
     * @param null $value
     *
     * @return Shopgate_Model_Abstract
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * returns data from key or all
     * @param string $key
     * @param null $index
     *
     * @return array|null
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->_data;
        }

        $default = null;

        if (isset($this->_data[$key])) {
            if (is_null($index)) {
                return $this->_data[$key];
            }
            $value = $this->_data[$key];
            if (is_array($value)) {
                if (isset($value[$index])) {
                    return $value[$index];
                }
                return null;
            }
            return $default;
        }
        return $default;
    }

    /**
     * @param string $var
     *
     * @return array|null|string
     */
    public function __get($var)
    {
        $var = $this->_underscore($var);
        return $this->getData($var);
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    /**
     * Set row field value
     *
     * @param  string $columnName The column key.
     * @param  mixed $value The value for the property.
     * @return void
     */
    public function __set($columnName, $value)
    {
        $this->_data[$columnName] = $value;
    }
} 