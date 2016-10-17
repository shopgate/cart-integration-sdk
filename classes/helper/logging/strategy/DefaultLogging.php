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
class DefaultLogging implements LoggingInterface
{
    /** @var bool */
    private $debug;
    /** @var mixed[] */
    private $logFiles = array(
        ShopgateLogger::LOGTYPE_ACCESS  => array('path' => '', 'handle' => null, 'mode' => 'a+'),
        ShopgateLogger::LOGTYPE_REQUEST => array('path' => '', 'handle' => null, 'mode' => 'a+'),
        ShopgateLogger::LOGTYPE_ERROR   => array('path' => '', 'handle' => null, 'mode' => 'a+'),
        ShopgateLogger::LOGTYPE_DEBUG   => array('path' => '', 'handle' => null, 'mode' => 'w+'),
    );
    
    public function __construct(
        $accessLogPath = null, $requestLogPath = null, $errorLogPath = null, $debugLogPath = null
    ) {
        // fall back to default log paths if none are specified
        if (empty($accessLogPath)) {
            $accessLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'access.log';
        }
        if (empty($requestLogPath)) {
            $requestLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'request.log';
        }
        if (empty($errorLogPath)) {
            $errorLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'error.log';
        }
        if (empty($debugLogPath)) {
            $debugLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'debug.log';
        }
        
        $this->setLogFilePaths($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath);
        
        $this->debug = false;
    }
    
    /**
     * Enables logging messages to debug log file.
     */
    public function enableDebug()
    {
        $this->debug = true;
    }
    
    /**
     * Disables logging messages to debug log file.
     */
    public function disableDebug()
    {
        $this->debug = false;
    }
    
    /**
     * @return true if logging messages to debug log file is enabled, false otherwise.
     */
    public function isDebugEnabled()
    {
        return $this->debug;
    }
    
    /**
     * Logs a message to the according log file.
     *
     * This produces a log entry of the form<br />
     * <br />
     * [date] [time]: [message]\n<br />
     * <br />
     * to the selected log file. If an unknown log type is passed the message will be logged to the error log file.<br />
     * <br />
     * Logging to LOGTYPE_DEBUG only is done after $this->enableDebug() has been called and $this->disableDebug() has not
     * been called after that. The debug log file will be truncated on opening by default. To prevent this call
     * $this->keepDebugLog(true).
     *
     * @param string $msg  The error message.
     * @param string $type The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
     *
     * @return bool True on success, false on error.
     */
    public function log($msg, $type = ShopgateLogger::LOGTYPE_ERROR)
    {
        // build log message
        $msg = gmdate('d-m-Y H:i:s: ') . $msg . "\n";
        
        // determine log file type and append message
        switch (strtolower($type)) {
            // write to error log if type is unknown
            default:
                $type = ShopgateLogger::LOGTYPE_ERROR;
            
            // allowed types:
            case ShopgateLogger::LOGTYPE_ERROR:
            case ShopgateLogger::LOGTYPE_ACCESS:
            case ShopgateLogger::LOGTYPE_REQUEST:
            case ShopgateLogger::LOGTYPE_DEBUG:
        }
        
        // if debug logging is requested but not activated, simply return
        if (($type === ShopgateLogger::LOGTYPE_DEBUG) && !$this->debug) {
            return true;
        }
        
        // open log files if necessary
        if (!$this->openLogFileHandle($type)) {
            return false;
        }
        
        
        // try to log
        $success = false;
        if (fwrite($this->logFiles[$type]['handle'], $msg) !== false) {
            $success = true;
        }
        
        return $success;
    }
    
    /**
     * Returns the requested number of lines of the requested log file's end.
     *
     * @param string $type  The log file to be read
     * @param int    $lines Number of lines to return
     *
     * @return string The requested log file content
     * @throws ShopgateLibraryException
     *
     * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
     */
    public function tail($type = ShopgateLogger::LOGTYPE_ERROR, $lines = 20)
    {
        if (!isset($this->logFiles[$type])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE, 'Type: ' . $type);
        }
        
        if (!$this->openLogFileHandle($type)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::INIT_LOGFILE_OPEN_ERROR, 'Type: ' . $type);
        }
        
        if (empty($lines)) {
            $lines = 20;
        }
        
        $handle      = $this->logFiles[$type]['handle'];
        $lineCounter = $lines;
        $pos         = -2;
        $beginning   = false;
        $text        = '';
        
        while ($lineCounter > 0) {
            $t = '';
            while ($t !== "\n") {
                if (@fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = @fgetc($handle);
                $pos--;
            }
            
            $lineCounter--;
            if ($beginning) {
                @rewind($handle);
            }
            $text = @fgets($handle) . $text;
            if ($beginning) {
                break;
            }
        }
        
        return $text;
    }
    
    /**
     * Sets the paths to the log files.
     *
     * @param string $accessLogPath
     * @param string $requestLogPath
     * @param string $errorLogPath
     * @param string $debugLogPath
     */
    public function setLogFilePaths($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath)
    {
        if (!empty($accessLogPath)) {
            $this->logFiles[ShopgateLogger::LOGTYPE_ACCESS]['path'] = $accessLogPath;
        }
        
        if (!empty($requestLogPath)) {
            $this->logFiles[ShopgateLogger::LOGTYPE_REQUEST]['path'] = $requestLogPath;
        }
        
        if (!empty($errorLogPath)) {
            $this->logFiles[ShopgateLogger::LOGTYPE_ERROR]['path'] = $errorLogPath;
        }
        
        if (!empty($debugLogPath)) {
            $this->logFiles[ShopgateLogger::LOGTYPE_DEBUG]['path'] = $debugLogPath;
        }
    }
    
    /**
     * Opens log file handles for the requested log type if necessary.
     *
     * Already opened file handles will not be opened again.
     *
     * @param string $type The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
     *
     * @return bool true if opening succeeds or the handle is already open; false on error.
     */
    protected function openLogFileHandle($type)
    {
        // don't open file handle if already open
        if (!empty($this->logFiles[$type]['handle'])) {
            return true;
        }
        
        // set the file handle
        $this->logFiles[$type]['handle'] = @fopen($this->logFiles[$type]['path'], $this->logFiles[$type]['mode']);
        
        // if log files are not writeable continue silently to the next handle
        // TODO: This seems a bit too silent... How could we get notice of the error?
        if ($this->logFiles[$type]['handle'] === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Set the file handler mode to a+ (keep) or to w+ (reverse) the debug log file
     *
     * @param bool $keep
     */
    public function keepDebugLog($keep)
    {
        if ($keep) {
            $this->logFiles[ShopgateLogger::LOGTYPE_DEBUG]["mode"] = "a+";
        } else {
            $this->logFiles[ShopgateLogger::LOGTYPE_DEBUG]["mode"] = "w+";
        }
    }
    
    /**
     * @return mixed[]
     */
    public function getLogFiles()
    {
        return $this->logFiles;
    }
}