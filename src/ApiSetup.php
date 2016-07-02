<?php
/**
 * ApiSetup
 *
 * Created 6/28/16 4:48 PM
 * Setup for ApiBootstrap
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\Api
 */

namespace Pbc\Api;

/**
 * Class ApiSetup
 * @package Pbc\Api
 */

trait ApiSetup
{

    /**
     * @var null path to send request to
     */
    protected $apiPath = null;
    /**
     * @var bool debug  debugging flag
     */
    protected $debug = false;

    /**
     * @var mixed debugData holder
     */

    protected $debugData = null;


    /** @var array */
    protected $payload = [];

    /**
     * @var array
     */
    protected $headers = [];

    /** @var string */

    protected $logFile = 'apiLog.txt';
    /**
     * @var resource
     */
    protected $logHandle;

    /**
     * Get debugData
     */
    public function getDebugData()
    {
        return $this->debugData;
    }

    /**
     * Set debugData
     * @param string $value
     * @return $this
     */
    public function setDebugData($value = '')
    {
        $this->debugData = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload payload auth to be sent with request
     */
    public function setPayload(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers = [])
    {
        $this->headers = array_merge($this->getHeaders(), $headers);
    }

    /**
     * Close down the log handler
     */
    public function closeDebugData()
    {
        if ($this->getDebug()) {
            fclose($this->getLogHandle());
            $this->setDebugData(nl2br(file_get_contents($this->getLogFile())));
            fopen($this->getLogFile(), 'w+');
        }
    }

    /**
     * @return mixed
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param $debug
     * @return Get
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $filePath path to log log file or stream
     */
    public function setLogFile($filePath)
    {
        if (is_resource($this->getLogHandle())) {
            fclose($this->getLogHandle());
        }
        $this->logFile = $filePath;
        $this->setLogHandle(fopen($this->getLogFile(), 'w+'));
    }

    /**
     * @return resource
     */
    public function getLogHandle()
    {
        return $this->logHandle;
    }

    /**
     * @param resource $logHandle
     * @return ApiSetup
     */
    public function setLogHandle($logHandle)
    {
        $this->logHandle = $logHandle;
        return $this;
    }

    /**
     * @return null
     */
    public function getApiPath()
    {
        return $this->apiPath;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setApiPath($path)
    {
        $this->apiPath = $path;
        return $this;
    }
}
