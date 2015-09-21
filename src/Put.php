<?php
namespace Pbc\Api;


/**
 * Class Put
 * @package Pbc\Api
 */
class Put extends ApiBootstrap implements ApiInterface
{
    /**
     * @param string $apiPath
     * @param bool|false $debug
     */
    public function __construct($apiPath = '', array $headers = [], $debug = false)
    {
        parent::__construct($apiPath, $headers, $debug);
    }

    /**
     * Do Put to API
     *
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve(array $params = [])
    {
        $curlHandler = $this->curlBootstrap();
        curl_setopt($curlHandler, CURLOPT_URL, $this->prepHttpPath());
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $this->prepPostParameters($params));

        $response = curl_exec($curlHandler);
        $getContent = $this->responseContent($response, $curlHandler);

        return $getContent;
    }


}