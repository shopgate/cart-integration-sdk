<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class DefaultLoggingTest extends PHPUnit_Framework_TestCase
{
    /** @var DefaultLogging */
    private $defaultLoggingStrategy;
    /** @var array */
    private $phpUnitLogFiles = array();
    
    public function setUp()
    {
        $this->phpUnitLogFiles        = array(
            ShopgateLogger::LOGTYPE_ACCESS  => SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS
                . 'phpunit_access.log',
            ShopgateLogger::LOGTYPE_REQUEST => SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS
                . 'phpunit_request.log',
            ShopgateLogger::LOGTYPE_ERROR   => SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . 'phpunit_error.log',
            ShopgateLogger::LOGTYPE_DEBUG   => SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . 'phpunit_debug.log'
        );
        $this->defaultLoggingStrategy = new DefaultLogging();
        $this->setLogPaths($this->defaultLoggingStrategy);
        $this->clearTmpFolder();
    }
    
    public function testSetLogFilePaths()
    {
        $this->defaultLoggingStrategy->setLogFilePaths(
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_ACCESS] . '.test',
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_REQUEST] . '.test',
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_ERROR] . '.test',
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_DEBUG] . '.test'
        );
        
        $logFiles = $this->defaultLoggingStrategy->getLogFiles();
        
        foreach ($logFiles as $logType => $log) {
            $this->assertEquals($this->phpUnitLogFiles[$logType] . '.test', $log['path']);
        }
    }
    
    public function testConstructorWithLogFilesParameter()
    {
        $loggingStrategy = new DefaultLogging(
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_ACCESS] . '.test',
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_REQUEST] . '.test',
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_ERROR] . '.test',
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_DEBUG] . '.test'
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
        $loggingStrategy = new DefaultLogging();
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
                true, $this->in_string($testMsg, file_get_contents($logFiles[$logType]['path']))
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
                ShopgateLogger::LOGTYPE_ACCESS,
            ),
            array(
                false,
                ShopgateLogger::LOGTYPE_DEBUG,
            ),
            array(
                true,
                ShopgateLogger::LOGTYPE_REQUEST,
            ),
            array(
                true,
                ShopgateLogger::LOGTYPE_ERROR,
            ),
        );
    }
    
    /**
     * When a unknown log type is passed to the log method the error will be logged into type ShopgateLogger::LOGTYPE_ERROR
     */
    public function testCallLogWithUnknownLogType()
    {
        $loggingMsg = 'This is a test message';
        $logFiles   = $this->defaultLoggingStrategy->getLogFiles();
        
        $this->defaultLoggingStrategy->log($loggingMsg, 'unknown log type');
        
        $this->assertEquals(true, file_exists($logFiles[ShopgateLogger::LOGTYPE_ERROR]['path']));
        $this->assertEquals(
            true, $this->in_string($loggingMsg, file_get_contents($logFiles[ShopgateLogger::LOGTYPE_ERROR]['path']))
        );
    }
    
    /**
     * In case the ShopgateLogger is unable to open the file, the log method will just return false
     */
    public function testCallLogUnableToOpenLogFile()
    {
        $loggingMsg = 'This is a test message';
        /** @var DefaultLogging|PHPUnit_Framework_MockObject_Builder_InvocationMocker $loggingStrategy */
        $loggingStrategy = $this->getMockBuilder('DefaultLogging')
                                ->setMethods(array('openLogFileHandle'))
                                ->getMock()
        ;
        $loggingStrategy->method('openLogFileHandle')->willReturn(false);
        
        $success = $loggingStrategy->log($loggingMsg, ShopgateLogger::LOGTYPE_ACCESS);
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
        $logType = ShopgateLogger::LOGTYPE_DEBUG;
        
        $firstLoggingMsg      = 'This is the first test message';
        $firstLoggingStrategy = new DefaultLogging();
        $this->setLogPaths($firstLoggingStrategy);
        $firstLoggingStrategy->keepDebugLog($keepDebugLog);
        $firstLoggingStrategy->enableDebug();
        $firstLoggingStrategy->log($firstLoggingMsg, $logType);
        
        $secondLoggingMsg      = 'This is the second test message';
        $secondLoggingStrategy = new DefaultLogging();
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
                true
            ),
            array(
                false,
                false,
                true
            ),
        );
    }
    
    public function testTailUnknownLogType()
    {
        $this->setExpectedException(
            'ShopgateLibraryException', '', ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE
        );
        $logContent = $this->defaultLoggingStrategy->tail('type not exists');
        $this->assertEquals('', $logContent);
    }
    
    public function testTailUnableToOpenLogFile()
    {
        /** @var DefaultLogging|PHPUnit_Framework_MockObject_Builder_InvocationMocker $loggingStrategy */
        $loggingStrategy = $this->getMockBuilder('DefaultLogging')
                                ->setMethods(array('openLogFileHandle'))
                                ->getMock()
        ;
        $loggingStrategy->method('openLogFileHandle')->willReturn(false);
        
        $this->setExpectedException(
            'ShopgateLibraryException', '', ShopgateLibraryException::INIT_LOGFILE_OPEN_ERROR
        );
        $logContent = $loggingStrategy->tail(ShopgateLogger::LOGTYPE_ACCESS);
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
            ShopgateLogger::LOGTYPE_ACCESS,
            ShopgateLogger::LOGTYPE_REQUEST,
            ShopgateLogger::LOGTYPE_DEBUG,
            ShopgateLogger::LOGTYPE_ERROR,
        );
    }
    
    /**
     * @param DefaultLogging $loggingStrategy
     */
    private function setLogPaths(DefaultLogging $loggingStrategy)
    {
        $loggingStrategy->setLogFilePaths(
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_ACCESS],
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_REQUEST],
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_ERROR],
            $this->phpUnitLogFiles[ShopgateLogger::LOGTYPE_DEBUG]
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