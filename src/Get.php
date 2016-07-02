<?php
namespace Pbc\Api;

/**
 * Class Get
 * @package Pbc\Api
 */
class Get extends ApiBootstrap implements ApiInterface
{
    /**
     * @param string $apiPath
     * @param array $headers
     * @param bool|false $debug
     */
    public function __construct($apiPath = '', array $headers = [], $debug = false)
    {
        parent::__construct($apiPath, $headers, $debug);
    }

    /**
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve(array $params = [])
    {
        $curlHandler = $this->curlBootstrap();
        curl_setopt($curlHandler, CURLOPT_URL, $this->prepHttpPath($params));
        curl_setopt($curlHandler, CURLOPT_HTTPGET, true);
        $response = $this->curlResponse($curlHandler);
        return $this->responseContent($response, $curlHandler);

    }
}
