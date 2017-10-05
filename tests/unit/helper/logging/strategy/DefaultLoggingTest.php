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

namespace shopgate\cart_integration_sdk\tests\unit\logging\strategy;

class DefaultLoggingTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Shopgate_Helper_Logging_Strategy_DefaultLogging */
    private $defaultLoggingStrategy;

    /** @var array */
    private $phpUnitLogFiles = array();

    public function setUp()
    {
        $this->phpUnitLogFiles        = array(
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS  => SHOPGATE_BASE_DIR . DS . 'temp' . DS
                . 'logs' . DS . 'phpunit_access.log',
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_REQUEST => SHOPGATE_BASE_DIR . DS . 'temp' . DS
                . 'logs' . DS . 'phpunit_request.log',
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR   => SHOPGATE_BASE_DIR . DS . 'temp' . DS
                . 'logs' . DS . 'phpunit_error.log',
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG   => SHOPGATE_BASE_DIR . DS . 'temp' . DS
                . 'logs' . DS . 'phpunit_debug.log',
        );
        $this->defaultLoggingStrategy = new \Shopgate_Helper_Logging_Strategy_DefaultLogging();
        $this->setLogPaths($this->defaultLoggingStrategy);
        $this->clearTmpFolder();
    }

    public function testSetLogFilePaths()
    {
        $this->defaultLoggingStrategy->setLogFilePaths(
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS] . '.test',
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_REQUEST] . '.test',
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR] . '.test',
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG] . '.test'
        );

        $logFiles = $this->defaultLoggingStrategy->getLogFiles();

        foreach ($logFiles as $logType => $log) {
            $this->assertEquals($this->phpUnitLogFiles[$logType] . '.test', $log['path']);
        }
    }

    public function testConstructorWithLogFilesParameter()
    {
        $loggingStrategy = new \Shopgate_Helper_Logging_Strategy_DefaultLogging(
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS] . '.test',
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_REQUEST] . '.test',
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR] . '.test',
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG] . '.test'
        );

        $logFiles = $loggingStrategy->getLogFiles();

        foreach ($logFiles as $logType => $log) {
            $this->assertEquals($this->phpUnitLogFiles[$logType] . '.test', $log['path']);
        }
    }

    /**
     * @param string $logType
     *
     * @dataProvider logTypeProvider
     */
    public function testDefaultLoggingPathsNotEmpty($logType)
    {
        $loggingStrategy = new \Shopgate_Helper_Logging_Strategy_DefaultLogging();
        $files           = $loggingStrategy->getLogFiles();
        $this->assertEquals(true, !empty($files[$logType]['path']));
    }

    /**
     * @param string $logType
     *
     * @dataProvider logTypeProvider
     */
    public function testCallLogEnableDebug($logType)
    {
        $loggingMsg = 'This is a test message';
        $logFiles   = $this->defaultLoggingStrategy->getLogFiles();

        $this->defaultLoggingStrategy->enableDebug();
        $this->defaultLoggingStrategy->log($loggingMsg, $logType);

        $this->assertEquals(true, file_exists($logFiles[$logType]['path']));
        $this->assertEquals(true, $this->in_string($loggingMsg, file_get_contents($logFiles[$logType]['path'])));
    }

    /**
     * @param bool   $expectedResult
     * @param string $logType
     *
     * @dataProvider callLogDisableDebugProvider
     */
    public function testCallLogDisableDebug($expectedResult, $logType)
    {
        $testMsg  = 'This is a test message';
        $logFiles = $this->defaultLoggingStrategy->getLogFiles();

        $this->defaultLoggingStrategy->disableDebug();
        $this->defaultLoggingStrategy->log($testMsg, $logType);

        $this->assertEquals($expectedResult, file_exists($logFiles[$logType]['path']));
        if (file_exists($logFiles[$logType]['path'])) {
            $this->assertEquals(
                true,
                $this->in_string($testMsg, file_get_contents($logFiles[$logType]['path']))
            );
        }
    }

    /**
     * @return array
     */
    public function callLogDisableDebugProvider()
    {
        return array(
            array(
                true,
                \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS,
            ),
            array(
                false,
                \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG,
            ),
            array(
                true,
                \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_REQUEST,
            ),
            array(
                true,
                \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR,
            ),
        );
    }

    /**
     * When a unknown log type is passed to the log method the error will be logged into type
     * Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR
     */
    public function testCallLogWithUnknownLogType()
    {
        $loggingMsg = 'This is a test message';
        $logFiles   = $this->defaultLoggingStrategy->getLogFiles();

        $this->defaultLoggingStrategy->log($loggingMsg, 'unknown log type');

        $this->assertEquals(
            true,
            file_exists($logFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR]['path'])
        );
        $this->assertEquals(
            true,
            $this->in_string(
                $loggingMsg,
                file_get_contents($logFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR]['path'])
            )
        );
    }

    /**
     * In case the ShopgateLogger is unable to open the file, the log method will just return false
     */
    public function testCallLogUnableToOpenLogFile()
    {
        $loggingMsg = 'This is a test message';
        /** @var \Shopgate_Helper_Logging_Strategy_DefaultLogging|\PHPUnit_Framework_MockObject_Builder_InvocationMocker $loggingStrategy */
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_DefaultLogging')
            ->setMethods(array('openLogFileHandle'))
            ->getMock();
        $loggingStrategy->method('openLogFileHandle')->willReturn(false);

        $success = $loggingStrategy->log(
            $loggingMsg,
            \Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ACCESS
        );
        $this->assertEquals(false, $success);
    }

    /**
     * In case log files are not yet created the tail method will return an empty string
     *
     * @param string $logType
     *
     * @dataProvider logTypeProvider
     */
    public function testTailNoFilesAvailable($logType)
    {
        $logContent = $this->defaultLoggingStrategy->tail($logType);
        $this->assertEquals('', $logContent);
    }

    /**
     * @param string $logType
     *
     * @dataProvider logTypeProvider
     */
    public function testTailAfterLogging($logType)
    {
        $testMsg = 'This is a test message';
        $this->defaultLoggingStrategy->enableDebug();
        $this->defaultLoggingStrategy->log($testMsg, $logType);

        $logContent = $this->defaultLoggingStrategy->tail($logType);
        $this->assertEquals(true, $this->in_string($testMsg, $logContent));
    }

    /**
     * We simulate two requests by initiate two DefaultLogging strategy objects. If keepDebugLog is true
     * the "old" debug_log should not be erased. If keepDebugLog is false the first entry must be erased.
     *
     * @param bool $keepDebugLog
     * @param bool $isFirstMessageInLogFile
     * @param bool $isSecondMessageInLogFile
     *
     * @dataProvider keepDebugLogProvider
     */
    public function testKeepDebugLog($keepDebugLog, $isFirstMessageInLogFile, $isSecondMessageInLogFile)
    {
        $logType = \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG;

        $firstLoggingMsg      = 'This is the first test message';
        $firstLoggingStrategy = new \Shopgate_Helper_Logging_Strategy_DefaultLogging();
        $this->setLogPaths($firstLoggingStrategy);
        $firstLoggingStrategy->keepDebugLog($keepDebugLog);
        $firstLoggingStrategy->enableDebug();
        $firstLoggingStrategy->log($firstLoggingMsg, $logType);

        $secondLoggingMsg      = 'This is the second test message';
        $secondLoggingStrategy = new \Shopgate_Helper_Logging_Strategy_DefaultLogging();
        $this->setLogPaths($secondLoggingStrategy);
        $secondLoggingStrategy->keepDebugLog($keepDebugLog);
        $secondLoggingStrategy->enableDebug();
        $secondLoggingStrategy->log($secondLoggingMsg, $logType);

        $logFiles   = $secondLoggingStrategy->getLogFiles();
        $logContent = file_get_contents($logFiles[$logType]['path']);
        $this->assertEquals($isFirstMessageInLogFile, $this->in_string($firstLoggingMsg, $logContent));
        $this->assertEquals($isSecondMessageInLogFile, $this->in_string($secondLoggingMsg, $logContent));
    }

    /**
     * @return array
     */
    public function keepDebugLogProvider()
    {
        return array(
            array(
                true,
                true,
                true,
            ),
            array(
                false,
                false,
                true,
            ),
        );
    }

    public function testTailUnknownLogType()
    {
        $this->setExpectedException(
            'ShopgateLibraryException',
            '',
            \ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE
        );
        $logContent = $this->defaultLoggingStrategy->tail('type not exists');
        $this->assertEquals('', $logContent);
    }

    public function testTailUnableToOpenLogFile()
    {
        /** @var \Shopgate_Helper_Logging_Strategy_DefaultLogging|\PHPUnit_Framework_MockObject_Builder_InvocationMocker $loggingStrategy */
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_DefaultLogging')
            ->setMethods(array('openLogFileHandle'))
            ->getMock();
        $loggingStrategy->method('openLogFileHandle')->willReturn(false);

        $this->setExpectedException(
            'ShopgateLibraryException',
            '',
            \ShopgateLibraryException::INIT_LOGFILE_OPEN_ERROR
        );
        $logContent = $loggingStrategy->tail(\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS);
        $this->assertEquals('', $logContent);
    }

    /**
     * @return array
     */
    public function logTypeProvider()
    {
        $logTypes    = $this->getLogTypes();
        $returnTypes = array();
        foreach ($logTypes as $logType) {
            $returnTypes[] = array($logType);
        }

        return $returnTypes;
    }

    public function in_string($needle, $string)
    {
        return mb_strpos($string, $needle) !== false;
    }

    /**
     * @return string[]
     */
    public function getLogTypes()
    {
        return array(
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS,
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_REQUEST,
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG,
            \Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR,
        );
    }

    /**
     * @param \Shopgate_Helper_Logging_Strategy_DefaultLogging $loggingStrategy
     */
    private function setLogPaths(\Shopgate_Helper_Logging_Strategy_DefaultLogging $loggingStrategy)
    {
        $loggingStrategy->setLogFilePaths(
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ACCESS],
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_REQUEST],
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_ERROR],
            $this->phpUnitLogFiles[\Shopgate_Helper_Logging_Strategy_DefaultLogging::LOGTYPE_DEBUG]
        );
    }

    /**
     * Deletes all log files which are currently handled by the logging strategy
     */
    private function clearTmpFolder()
    {
        $logFiles = $this->defaultLoggingStrategy->getLogFiles();
        foreach ($this->getLogTypes() as $logType) {
            $logFilePath = $logFiles[$logType]['path'];
            if (file_exists($logFilePath)) {
                unlink($logFilePath);
            }
        }
    }
}
