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

use shopgate\cart_integration_sdk\tests\helper\ShopgateTestCase;

/**
 * @group              Shopgate_Library
 *
 * @coversDefaultClass ShopgateObject
 */
class ShopgatePluginApiTest extends ShopgateTestCase
{
    static private $cronJobWhiteList = array(
        \ShopgatePluginApi::JOB_CANCEL_ORDERS,
        \ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
    );

    /** @var \ShopgatePluginApi $subjectUnderTest */
    private $subjectUnderTest;

    /** @var \ShopgateConfigInterface|\PHPUnit_Framework_MockObject_MockObject $shopgateConfigMock */
    private $shopgateConfigMock;

    /** @var \ShopgateAuthenticationServiceInterface|\PHPUnit_Framework_MockObject_MockObject $authenticationServiceMock */
    private $authenticationServiceMock;

    public function setUp()
    {
        $this->shopgateConfigMock =
            $this->getMockBuilder('\ShopgateConfigInterface')->getMockForAbstractClass();
        $this->shopgateConfigMock->method('getCronJobWhiteList')->willReturn(self::$cronJobWhiteList);

        $this->authenticationServiceMock =
            $this->getMockBuilder('\ShopgateAuthenticationServiceInterface')->getMockForAbstractClass();

        /** @var \ShopgateMerchantApiInterface $shopgateMerchantApiMock */
        $shopgateMerchantApiMock =
            $this->getMockBuilder('\ShopgateMerchantApiInterface')->getMockForAbstractClass();

        /** @var \ShopgatePlugin $shopgatePluginMock */
        $shopgatePluginMock =
            $this->getMockBuilder('\ShopgatePlugin')->disableOriginalConstructor()->getMock();

        $this->subjectUnderTest = new \ShopgatePluginApi(
            $this->shopgateConfigMock, $this->authenticationServiceMock, $shopgateMerchantApiMock, $shopgatePluginMock
        );
    }

    /**
     * @param array $cronJobs
     *
     * @dataProvider provideUnsupportedCronJobs
     */
    public function testHandleRequestMethodCronThrowsUnsupportedJobsException(array $cronJobs)
    {
        $this->shopgateConfigMock->method('toArray')->willReturn(array('enable_cron' => true));

        $this->authenticationServiceMock->method('checkAuthentication')->willReturn(true);

        ob_start();
        $this->subjectUnderTest->handleRequest(array('action' => 'cron', 'jobs' => $cronJobs));
        $json = ob_get_clean();

        $response = json_decode($json, true);

        $this->assertEquals(
            $response['error'],
            \ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB
        );

        $this->assertEquals(
            $response['error_text'],
            'unsupported job'
        );
    }

    /**
     * @return array
     */
    public function provideUnsupportedCronJobs()
    {
        return array(
            'unknown job'                 => array(
                array(
                    $this->getJobsStrctured(array('unknown job')),
                ),
            ),
            'unknown jobs'                => array(
                $this->getJobsStrctured(array('unknown job #1', 'unknown job #2')),
            ),
            'known jobs with unknown job' => array(
                $this->getJobsStrctured(
                    array(
                        \ShopgatePluginApi::JOB_CANCEL_ORDERS,
                        \ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
                        'unknown job #1',
                    )
                ),
            ),
            'unknown job with known jobs' => array(
                $this->getJobsStrctured(
                    array(
                        'unknown job #1',
                        \ShopgatePluginApi::JOB_CANCEL_ORDERS,
                        \ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED,
                    )
                ),
            ),
        );
    }

    /**
     * @param array $cronJobs
     *
     * @dataProvider provideSupportedCronJobs
     */
    public function testHandleRequestMethodCron(array $cronJobs)
    {
        $this->shopgateConfigMock->method('toArray')->willReturn(array('enable_cron' => true));

        $this->authenticationServiceMock->method('checkAuthentication')->willReturn(true);

        ob_start();
        $this->subjectUnderTest->handleRequest(array('action' => 'cron', 'jobs' => $cronJobs));
        $json = ob_get_clean();

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
                $this->getJobsStrctured(array(\ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED)),
            ),
            'two supported jobs' => array(
                $this->getJobsStrctured(
                    array(\ShopgatePluginApi::JOB_CANCEL_ORDERS, \ShopgatePluginApi::JOB_SET_SHIPPING_COMPLETED)
                ),
            ),
        );
    }

    /**
     * @param array $jobs
     *
     * @return array
     */
    private function getJobsStrctured(array $jobs)
    {
        $jobsStructured = array();
        foreach ($jobs as $job) {
            $jobsStructured[] = array('job_name' => $job);
        }

        return $jobsStructured;
    }
}
