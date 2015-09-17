<?php
namespace Pbc\Api;


/**
 * Class Get
 * @package Mailblast\Api
 */
/**
 * Class Get
 * @package Pbc\Api
 */
class Get implements ApiInterface
{
    /**
     * timeout for request
     */
    const TIMEOUT = 120;
    /**
     * Curl ssl version
     */
    const SSL_VERSION = 'CURL_SSLVERSION_TLSv1_1';
    /**
     * @var null path to send request to
     */
    protected $apiPath = null;
    /**
     * @var null token to send with request for authentication
     */
    protected $apiKey = null;
    /**
     * @var string name of authentication token
     */
    protected $apiKeyName = 'X-API-KEY';

    /**
     * @var bool Debug flag
     */
    protected $debug = false;

    /**
     * @var string
     */
    protected $logFile = 'apiGetErrorLog.txt';

    /**
     * @param null $apiPath
     * @param array $apiKey
     * @param bool|false $debug
     */
    public function __construct($apiPath = null, array $apiKey = [], $debug = false)
    {
        $this->setApiPath($apiPath);
        $this->setApiKey($apiKey);
        $this->setDebug($debug);

        return $this;
    }

    /**
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve($params = [])
    {
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $this->prepHttpPath($params));
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HEADER, false);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandler, CURLOPT_HTTPGET, true);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curlHandler, CURLOPT_USERAGENT, getenv('HTTP_USER_AGENT'));
        curl_setopt($curlHandler, CURLOPT_SSLVERSION, self::SSL_VERSION);
        $errorFile = fopen(dirname(__FILE__) . '/' . $this->getLogFile(), 'a+');
        curl_setopt($curlHandler, CURLOPT_STDERR, $errorFile);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, array(
            $this->getApiKeyName() . ': ' . $this->getApiKey(),
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        if ($this->getDebug() === true) {
            curl_setopt($curlHandler, CURLOPT_VERBOSE, true);
        }
        $response = curl_exec($curlHandler);
        $getContent = $this->responseContent($response, $curlHandler);

        return $getContent;
    }

    /**
     * @param $debug
     * @return $this
     */
    protected function setDebug($debug)
    {
        $this->debug = (bool)$debug;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getApiPath()
    {
        return $this->apiPath;
    }

    /**
     * @param $path
     */
    protected function setApiPath($path)
    {
        $this->apiPath = $path;
    }

    /**
     * @param array $apiKey
     * @return $this
     */
    protected function setApiKey(array $apiKey)
    {
        $apiKey = array_values($apiKey);
        if (count($apiKey) > 1) {
            $this->apiKeyName = $apiKey[0];
            $this->apiKey = $apiKey[1];
        } else {
            $this->apiKey = isset($apiKey[0]) ? $apiKey[0] : null;
        }
        return $this;

    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return mixed
     */
    public function getDebug()
    {
        return $this->debug;
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
     * @return string
     */
    public function getApiKeyName()
    {
        return $this->apiKeyName;
    }

    /**
     * @param string $apiKeyName
     */
    protected function setApiKeyName($apiKeyName)
    {
        $this->apiKeyName = $apiKeyName;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
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
    private function addScheme($url, $scheme = 'http://')
    {
        return parse_url($url, PHP_URL_SCHEME) === null ?
            $scheme . $url : $url;
    }

    /**
     * @param $params
     * @return string
     */
    private function prepUrlParams($params)
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
            foreach ($params as $key => $value) {
                $value = $urlEncode ? $value : urlencode($value);
                $postValues .= $key . '=' . $value . '&';
            }
            $postValues = rtrim($postValues, '&');
            return $postValues;
        }
        return $postValues;
    }

}