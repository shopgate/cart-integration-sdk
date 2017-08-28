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
 * @group              Shopgate_Library
 *
 * @coversDefaultClass ShopgateObject
 */
class ShopgateObjectTest extends PHPUnit_Framework_TestCase
{
    /** @var ShopgateObject $subjectUnderTest */
    protected $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockBuilder('\ShopgateObject')->getMockForAbstractClass();
    }

    /**
     * @param bool  $expectedResult
     * @param mixed $jsonInput
     *
     * @dataProvider provideJsonDecodeExamples
     */
    public function testJsonDecode($expectedResult, $jsonInput)
    {
        $this->assertEquals($expectedResult, $this->subjectUnderTest->jsonDecode($jsonInput));
    }

    /**
     * @return array
     */
    public function provideJsonDecodeExamples()
    {
        return array(
            'integer - zero'    => array(
                0,
                0,
            ),
            'empty string'      => array(
                null,
                '',
            ),
            'string'            => array(
                null,
                'abcd',
            ),
            'integer'           => array(
                123,
                123,
            ),
            'float'             => array(
                2.5,
                2.5,
            ),
            'bool - true'       => array(
                true,
                true,
            ),
            'bool - false'      => array(
                false,
                false,
            ),
            'null'              => array(
                null,
                null,
            ),
            'array'             => array(
                array(),
                '[]',
            ),
            'serialized string' => array(
                null,
                serialize(array('testKey' => 'testValue')),
            ),
        );
    }
}
