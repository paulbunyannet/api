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
    use ApiSetup;
    /**
     * Curl ssl version
     */
    const SSL_VERSION = 'CURL_SSLVERSION_TLSv1_1';
    /**
     * timeout for request
     */
    const TIMEOUT = 120;

    public function __construct($apiPath = '', array $headers = [], $debug = false)
    {
        $this->setApiPath($apiPath);
        $this->setDebug($debug);
        $this->setLogFile('api' . str_replace('\\','-',get_class($this)) . 'ErrorLog.txt');
        $this->setHeaders($headers);

        return $this;
    }

    /**
     * @param $response
     * @param $curlHandler
     * @return mixed|\stdClass
     */
    public function responseContent($response, $curlHandler)
    {
        $getContent = @json_decode($response);
        if ((!$getContent && is_string($response)) || is_null($getContent)) {
            // if debugging, set debug output to the debug data field
            if($this->getDebug()) {
                $response .= " \n Debug: \n" . $this->getDebugData();
            }
            return $response;
        }
        if ($this->getDebug()) {
            if (is_array($getContent) && isset($getContent[0])) {
                $getContent[0]->response = $response;
                $getContent[0]->curl = curl_getinfo($curlHandler);
                $getContent[0]->debug = $this->getDebugData();
            } elseif (is_object($getContent)) {
                $getContent->response = $response;
                $getContent->curl = curl_getinfo($curlHandler);
                $getContent->debug = $this->getDebugData();
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
    public function curlBootstrap()
    {

        $curlHandler = curl_init($this->getApiPath());
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HEADER, false);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curlHandler, CURLOPT_USERAGENT, $this->userAgent());
        curl_setopt($curlHandler, CURLOPT_SSLVERSION, ApiBootstrap::SSL_VERSION);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, 0);

        // if debug is true then set curl to verbose and set the error handler
        curl_setopt($curlHandler, CURLOPT_VERBOSE, $this->getDebug());
        if (get_resource_type($this->getLogHandle()) == 'file'
            || get_resource_type($this->getLogHandle()) == 'stream'
        ) {
            curl_setopt($curlHandler, CURLOPT_STDERR, $this->getLogHandle());

        }

        return $curlHandler;
    }

    /**
     * @param $handler
     * @return mixed
     */
    public function curlResponse($handler)
    {
        $response = curl_exec($handler);
        $this->closeDebugData();
        return $response;
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

        if (count($this->getHeaders()) > 0
            && array_keys($this->getHeaders()) !== range(
                0,
                count($this->getHeaders()) - 1
            )
        ) {
            $holder = [];
            foreach ($this->getHeaders() as $key => $value) {
                array_push($holder, $key . ': ' . $value);
            }
            return $holder;
        } else {
            return $this->getHeaders();
        }
    }
}
