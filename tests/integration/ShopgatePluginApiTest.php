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

namespace shopgate\cart_integration_sdk\tests\integration;

use PHPUnit_Framework_MockObject_MockObject;
use shopgate\cart_integration_sdk\tests\helper\ShopgateTestCase;
use Shopgate_Model_Catalog_Category;
use Shopgate_Model_XmlResultObject;
use ShopgateAuthenticationServiceInterface;
use ShopgateConfigInterface;
use ShopgateFileBufferXml;
use ShopgateMerchantApiInterface;
use ShopgatePlugin;
use ShopgatePluginApi;
use ShopgatePluginApiResponseAppJson;
use ShopgatePluginApiResponseAppXmlExport;
use Yoast\PHPUnitPolyfills\Exceptions\InvalidComparisonMethodException;

/**
 * @group              Shopgate_Library
 *
 * @coversDefaultClass \ShopgateObject
 */
class ShopgatePluginApiTest extends ShopgateTestCase
{
    private static $cronJobWhiteList = array(
        ShopgatePluginApi::JOB_CANCEL_ORDERS,
        ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
    );

    /** @var string */
    private $tmpCategoriesXmlFilePath;

    /** @var ShopgatePluginApi $subjectUnderTest */
    private $subjectUnderTest;

    /** @var ShopgateConfigInterface|PHPUnit_Framework_MockObject_MockObject $shopgateConfigMock */
    private $shopgateConfigMock;

    /** @var ShopgateAuthenticationServiceInterface|PHPUnit_Framework_MockObject_MockObject $authenticationServiceMock */
    private $authenticationServiceMock;

    /** @var ShopgatePlugin|PHPUnit_Framework_MockObject_MockObject */
    private $shopgatePluginMock;

    public function set_up()
    {
        $this->tmpCategoriesXmlFilePath = '/tmp/shopgate_categories.xml';

        $this->shopgateConfigMock =
            $this->getMockBuilder('\ShopgateConfigInterface')->getMockForAbstractClass();
        $this->shopgateConfigMock->method('getCronJobWhiteList')->willReturn(self::$cronJobWhiteList);
        $this->shopgateConfigMock->method('toArray')->willReturn(array(
            'enable_cron' => true,
            'enable_get_orders' => true,
            'enable_get_categories' => true
        ));

        $this->authenticationServiceMock =
            $this->getMockBuilder('\ShopgateAuthenticationServiceInterface')->getMockForAbstractClass();
        $this->authenticationServiceMock->method('checkAuthentication')->willReturn(true);

        /** @var ShopgateMerchantApiInterface $shopgateMerchantApiMock */
        $shopgateMerchantApiMock =
            $this->getMockBuilder('\ShopgateMerchantApiInterface')->getMockForAbstractClass();

        /** @var ShopgatePlugin $shopgatePluginMock */
        $this->shopgatePluginMock =
            $this->getMockBuilder('\ShopgatePlugin')->disableOriginalConstructor()->getMock();

        $this->shopgatePluginMock->setConfig($this->shopgateConfigMock);

        $this->subjectUnderTest = new ShopgatePluginApi(
            $this->shopgateConfigMock,
            $this->authenticationServiceMock,
            $shopgateMerchantApiMock,
            $this->shopgatePluginMock
        );
    }

    public function tear_down()
    {
        if (file_exists($this->tmpCategoriesXmlFilePath)) {
            unlink($this->tmpCategoriesXmlFilePath);
        }
    }

    /**
     * @param string $expectedOutputBuffer
     * @param mixed $expectedResponse
     * @param string $action
     * @param array<string, string> $parameters
     * @param string|null $filePath
     *
     * @throws InvalidComparisonMethodException
     *
     * @dataProvider provideExternalResponseHandlingSetups
     */
    public function testExternalResponseHandling($expectedOutputBuffer, $expectedResponse, $action, $parameters, $filePath = null)
    {
        $this->shopgateConfigMock->method('getExternalResponseHandling')->willReturn(true);
        $this->shopgateConfigMock->method('getCategoriesXmlPath')->willReturn($filePath);

        $this->shopgatePluginMock->method('getOrders')->willReturn(array());

        if ($expectedResponse instanceof ShopgatePluginApiResponseAppXmlExport) {
            $xmlModel   = new Shopgate_Model_Catalog_Category();
            $xmlNode    = new Shopgate_Model_XmlResultObject($xmlModel->getItemNodeIdentifier());
            $fileBuffer = new ShopgateFileBufferXml($xmlModel, $xmlNode, 100, 'UTF-8');
            $this->shopgatePluginMock->setBuffer($fileBuffer);

            $this->shopgatePluginMock->method('createCategories')
                ->with(100, 5, array())
                // this is a workaround to fake some work typically done by a plugin
                ->willReturnCallback(function () use ($fileBuffer) {
                    $categoryItem = new Shopgate_Model_Catalog_Category();
                    $categoryItem->setUid(10);
                    $categoryItem->setName('example');
                    $categoryItem->setIsActive(true);

                    $fileBuffer->addRow($categoryItem);
                });
        }

        ob_start();
        $response = $this->subjectUnderTest->handleRequest(array_merge($parameters, array('action' => $action)));
        $outputBuffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($expectedOutputBuffer, $outputBuffer);
        $this->assertEquals($expectedResponse->getBody(), $response->getBody());
        $this->assertEquals($expectedResponse->getHeaders(), $response->getHeaders());
        $this->assertEquals($expectedResponse->isError(), $response->isError());

        if ($response instanceof ShopgatePluginApiResponseAppXmlExport) {
            $this->assertEquals($expectedResponse->isStream(), $response->isStream());
        }
    }

    public function provideExternalResponseHandlingSetups()
    {
        $traceId = '0123456789abcdef';

        return array(
            'get_orders' => array(
                'expectedOutputBuffer' => '',
                'expectedResponse' => new ShopgatePluginApiResponseAppJson($traceId, array('orders' => array())),
                'action' => 'get_orders',
                'parameters' => array(
                    'trace_id' => $traceId,
                    'customer_token' => '0123456789abcdef',
                    'customer_language' => 'en_US'
                ),
            ),
            "get_categories to file {$this->tmpCategoriesXmlFilePath}" => array(
                'expectedOutputBuffer' => '',
                'expectedResponse' => new ShopgatePluginApiResponseAppXmlExport($traceId, $this->tmpCategoriesXmlFilePath),
                'action' => 'get_categories',
                'parameters' => array(
                    'limit' => 100,
                    'offset' => 5,
                ),
                'filePath' => $this->tmpCategoriesXmlFilePath
            ),
            'get_categories to file php://output' => array(
                'expectedOutputBuffer' => '<categories xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://files.shopgate.com/xml/xsd/catalog/categories.xsd"><category uid="10" is_active="1" is_anchor="0"><name><![CDATA[example]]></name><deeplink/></category></categories>',
                'expectedResponse' => new ShopgatePluginApiResponseAppXmlExport($traceId, 'php://output'),
                'action' => 'get_categories',
                'parameters' => array(
                    'limit' => 100,
                    'offset' => 5,
                    'error_reporting' => 1 // workaround to keep Plugin API from cleaning buffer before test can get it
                ),
                'filePath' => 'php://output'
            )
        );
    }

    /**
     * @param string $expectedErrorText
     * @param array  $cronJobs
     *
     * @dataProvider provideUnsupportedCronJobs
     *
     * @runInSeparateProcess
     */
    public function testHandleRequestMethodCronThrowsUnsupportedJobsException($expectedErrorText, array $cronJobs)
    {
        $this->shopgatePluginMock->expects($this->never())->method('cron');

        ob_start();
        $this->subjectUnderTest->handleRequest(array('action' => 'cron', 'jobs' => $cronJobs));
        $json = ob_get_contents();
        ob_end_clean();

        $response = json_decode($json, true);

        $this->assertEquals(
            $response['error'],
            \ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB
        );

        $this->assertEquals(
            $response['error_text'],
            $expectedErrorText
        );
    }

    /**
     * @return array
     */
    public function provideUnsupportedCronJobs()
    {
        return array(
            'unknown job'                 => array(
                'unsupported job: unknown job',
                $this->getJobsStructured(array('unknown job')),
            ),
            'unknown jobs'                => array(
                'unsupported job: unknown job #1, unknown job #2',
                $this->getJobsStructured(array('unknown job #1', 'unknown job #2')),
            ),
            'known jobs with unknown job' => array(
                'unsupported job: unknown job #1',
                $this->getJobsStructured(
                    array(
                        ShopgatePluginApi::JOB_CANCEL_ORDERS,
                        ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
                        'unknown job #1',
                    )
                ),
            ),
            'unknown job with known jobs' => array(
                'unsupported job: unknown job #1',
                $this->getJobsStructured(
                    array(
                        'unknown job #1',
                        ShopgatePluginApi::JOB_CANCEL_ORDERS,
                        ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
                    )
                ),
            ),
        );
    }

    /**
     * @param int   $expectedNumberOfCronCalls
     * @param array $cronJobs
     *
     * @dataProvider provideSupportedCronJobs
     *
     * @runInSeparateProcess
     */
    public function testHandleRequestMethodCron($expectedNumberOfCronCalls, array $cronJobs)
    {
        $this->shopgatePluginMock->expects($this->exactly($expectedNumberOfCronCalls))->method('cron');

        ob_start();
        $this->subjectUnderTest->handleRequest(array('action' => 'cron', 'jobs' => $cronJobs));
        $json = ob_get_contents();
        ob_end_clean();

        $response = json_decode($json, true);

        $this->assertEquals(
            0,
            $response['error']
        );
    }

    /**
     * @return array
     */
    public function provideSupportedCronJobs()
    {
        return array(
            'one supported job'  => array(
                1,
                $this->getJobsStructured(array(ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED)),
            ),
            'two supported jobs' => array(
                2,
                $this->getJobsStructured(
                    array(ShopgatePluginApi::JOB_CANCEL_ORDERS, ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED)
                ),
            ),
        );
    }

    /**
     * @param array $jobs
     *
     * @return array
     */
    private function getJobsStructured(array $jobs)
    {
        $jobsStructured = array();
        foreach ($jobs as $job) {
            $jobsStructured[] = array('job_name' => $job);
        }

        return $jobsStructured;
    }
}
