<?php
/**
 * Api Bootstrap
 *
 * Created 9/21/15 1:17 PM
 * Methods used through all API classes
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\Api
 */

namespace Pbc\Api;


/**
 * Class ApiBootstrap
 * @package Pbc\Api
 */
use Pbc\Api\Auth\AuthBootstrap;

/**
 * Class ApiBootstrap
 * @package Pbc\Api
 */
class ApiBootstrap
{

    /**
     * Curl ssl version
     */
    const SSL_VERSION = 'CURL_SSLVERSION_TLSv1_1';
    /**
     * timeout for request
     */
    const TIMEOUT = 120;
    /**
     * @var null path to send request to
     */
    protected $apiPath = null;
    /**
     * @var bool Debug flag
     */
    protected $debug = false;

    /**
     * @var string
     */
    protected $logFile = 'apiGetErrorLog.txt';

    /** @var array */
    protected $payload = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @param string $apiPath
     * @param bool|false $debug
     */
    public function __construct($apiPath = '', array $headers = [], $debug = false)
    {
        $this->setApiPath($apiPath);
        $this->setDebug($debug);
        $this->setLogFile('api' . get_class($this) . 'ErrorLog.txt');
        $this->setHeaders($headers);

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
     * @param array $payload payload auth to be sent with request
     */
    public function setPayload(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
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
    protected function setDebug($debug)
    {
        $this->debug = (bool)$debug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiPath()
    {
        return $this->apiPath;
    }

    /**
     * @param $path
     */
    public function setApiPath($path)
    {
        $this->apiPath = $path;
    }

    /**
     * @param $response
     * @param $curlHandler
     * @return mixed|\stdClass
     */
    protected function responseContent($response = null, $curlHandler)
    {
        $getContent = @json_decode($response);
        if (!$getContent && is_string($response)) {
            return $response;
        }
        if ($this->getDebug() === true) {
            if (is_array($getContent) && isset($getContent[0])) {
                $getContent[0]->response = $response;
                $getContent[0]->curl = curl_getinfo($curlHandler);
            } elseif (is_string($getContent)) {
                $getContent .= "Response: " . $response . "\n\n" . "Curl: " . curl_getinfo($curlHandler);
            } elseif (is_object($getContent)) {
                $getContent->response = $response;
                $getContent->curl = curl_getinfo($curlHandler);
            }
        }
        return $getContent;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function prepHttpPath(array $params = [])
    {

        return $this->addScheme($this->getApiPath()) . $this->prepUrlParams($params);

    }

    /**
     * @param $url
     * @param string $scheme
     * @return string
     */
    protected function addScheme($url, $scheme = 'http://')
    {
        return parse_url($url, PHP_URL_SCHEME) === null ?
            $scheme . $url : $url;
    }

    /**
     * @param $params
     * @return string
     */
    protected function prepUrlParams($params)
    {
        return (parse_url($this->getApiPath(), PHP_URL_QUERY) ? '&' : '?') . $this->prepPostParameters($params, true);
    }

    /**
     * Prep post parameters
     * @param $params
     * @param bool $urlEncode
     * @return string
     */
    protected function prepPostParameters($params, $urlEncode = false)
    {
        $postValues = null;
        if (count($params) > 0) {
            $this->prepPayload($this->getPayload(), $params);
            foreach ($params as $key => $value) {
                $value = $urlEncode ? $value : urlencode($value);
                $postValues .= $key . '=' . $value . '&';
            }
            $postValues = rtrim($postValues, '&');
            return $postValues;
        }
        return $postValues;
    }

    /**
     * @param array $payloadCheck
     * @param array $params
     */
    protected function prepPayload(array $payloadCheck = [], array &$params = [])
    {
        /**
         * Check that the identity key and private key are in the payload list
         * and that the payload key isn't already in the parameter list
         */
        if (array_key_exists(AuthBootstrap::IDENTITY, $payloadCheck)
            && array_key_exists(AuthBootstrap::PRIVATEKEY, $payloadCheck)
            && !array_key_exists(AuthBootstrap::PAYLOAD, $params)

        ) {
            $send = new Auth\Send($payloadCheck[AuthBootstrap::IDENTITY], $payloadCheck[AuthBootstrap::PRIVATEKEY]);
            $send->generateHash($params);
            $this->setPayload(
                array_merge(
                    $this->getPayload(),
                    [
                        AuthBootstrap::PAYLOAD => $params[AuthBootstrap::PAYLOAD],
                        AuthBootstrap::TIMESTAMP => $params[AuthBootstrap::TIMESTAMP]
                    ]
                )
            );
        }
    }


    /**
     * Bootstrap curl request
     * @return resource
     */
    protected function curlBootstrap()
    {

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HEADER, false);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curlHandler, CURLOPT_USERAGENT, $this->userAgent());
        curl_setopt($curlHandler, CURLOPT_SSLVERSION, ApiBootstrap::SSL_VERSION);
        $errorFile = fopen(dirname(__FILE__) . '/' . $this->getLogFile(), 'a+');
        curl_setopt($curlHandler, CURLOPT_STDERR, $errorFile);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        if ($this->getDebug() === true) {
            curl_setopt($curlHandler, CURLOPT_VERBOSE, true);
        }

        return $curlHandler;
    }

    /**
     * @return string
     */
    protected function userAgent()
    {
        return getenv('HTTP_USER_AGENT');
    }

    /**
     * Setup headers for request
     *
     * @return array
     */
    protected function headers()
    {

        if (count($this->getHeaders()) > 0 && array_keys($this->getHeaders()) !== range(0, count($this->getHeaders()) - 1)) {
            $holder = [];
            foreach ($this->getHeaders() as $key => $value) {
                array_push($holder, $key . ': ' . $value);
            }
            return $holder;
        } else {
            return $this->getHeaders();
        }
    }

    /**
     * @param string $string
     */
    private function setLogFile($string = 'errorLog.txt')
    {
        $this->logFile = $string;
    }


}