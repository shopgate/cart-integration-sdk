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

/**
 * Global class (Singleton) to manage log files.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateLogger implements LoggingInterface
{
    const LOGTYPE_ACCESS = 'access';
    const LOGTYPE_REQUEST = 'request';
    const LOGTYPE_ERROR = 'error';
    const LOGTYPE_DEBUG = 'debug';
    
    /** @var string */
    private $memoryAnalyserLoggingSizeUnit;
    
    /** @var ShopgateLogger */
    private static $singleton;
    
    /** @var Obfuscator */
    private $obfuscator;
    
    /** @var LoggingInterface */
    private $loggingStrategy;
    
    /**
     * @param string $accessLogPath
     * @param string $requestLogPath
     * @param string $errorLogPath
     * @param string $debugLogPath
     *
     * @return ShopgateLogger
     */
    public static function getInstance(
        $accessLogPath = null, $requestLogPath = null, $errorLogPath = null, $debugLogPath = null
    ) {
        if (empty(self::$singleton)) {
            self::$singleton = new self();
            
            self::$singleton->setLoggingStrategy(
                new DefaultLogging($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath)
            );
        }
        
        if (self::$singleton->loggingStrategy instanceof DefaultLogging) {
            /** @noinspection PhpUndefinedMethodInspection */
            self::$singleton->loggingStrategy->setLogFilePaths(
                $accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath
            );
        }
        
        return self::$singleton;
    }
    
    
    public function __construct()
    {
        $this->obfuscator = new Obfuscator();
    }
    
    /**
     * @param LoggingInterface $loggingStrategy
     */
    public function setLoggingStrategy($loggingStrategy)
    {
        $this->loggingStrategy = $loggingStrategy;
    }
    
    /**
     * @param Obfuscator $obfuscator
     */
    public function setObfuscator($obfuscator)
    {
        $this->obfuscator = $obfuscator;
    }
    
    /**
     * @return LoggingInterface
     */
    public function getLoggingStrategy()
    {
        return $this->loggingStrategy;
    }
    
    public function enableDebug()
    {
        $this->loggingStrategy->enableDebug();
    }
    
    public function disableDebug()
    {
        $this->loggingStrategy->disableDebug();
    }
    
    public function isDebugEnabled()
    {
        return $this->loggingStrategy->isDebugEnabled();
    }
    
    public function log($msg, $type = ShopgateLogger::LOGTYPE_ERROR)
    {
        return $this->loggingStrategy->log($msg, $type);
    }
    
    public function tail($type = ShopgateLogger::LOGTYPE_ERROR, $lines = 20)
    {
        return $this->loggingStrategy->tail($type, $lines);
    }
    
    public function keepDebugLog($keep)
    {
        $this->loggingStrategy->keepDebugLog($keep);
    }
    
    /**
     * Sets the unit in which the memory usage logger outputs its values in
     *
     * @param string $sizeUnit ('MB', 'BYTES', 'GB', 'KB', ...)
     */
    public function setMemoryAnalyserLoggingSizeUnit($sizeUnit)
    {
        switch (strtoupper(trim($sizeUnit))) {
            case 'GB':
            case 'GIGABYTE':
            case 'GIGABYTES':
                $this->memoryAnalyserLoggingSizeUnit = 'GB';
                break;
            case 'MB':
            case 'MEGABYTE':
            case 'MEGABYTES':
                $this->memoryAnalyserLoggingSizeUnit = 'MB';
                break;
            case 'KB':
            case 'KILOBYTE':
            case 'KILOBYTES':
                $this->memoryAnalyserLoggingSizeUnit = 'KB';
                break;
            default:
                $this->memoryAnalyserLoggingSizeUnit = 'BYTES';
                break;
        }
    }
    
    /**
     * returns the unit in which the memory usage logger outputs its values in
     *
     * @return string
     */
    public function getMemoryAnalyserLoggingSizeUnit()
    {
        return $this->memoryAnalyserLoggingSizeUnit;
    }
    
    /**
     * Function to prepare the parameters of an API request for logging.
     *
     * Strips out critical request data like the password of a get_customer request.
     *
     * @param mixed[] $data The incoming request's parameters.
     *
     * @return string The cleaned parameters as string ready to log.
     */
    public function cleanParamsForLog($data)
    {
        return $this->obfuscator->cleanParamsForLog($data);
    }
    
    
}