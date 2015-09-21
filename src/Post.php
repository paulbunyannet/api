<?php
namespace Pbc\Api;


/**
 * Class Post
 * @package Pbc\Api
 */
class Post extends ApiBootstrap implements ApiInterface
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
     * Do Post to API
     *
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve(array $params = [])
    {
        $postValues = $this->prepPostParameters($params);

        $curlHandler = $this->curlBootstrap();
        curl_setopt($curlHandler, CURLOPT_URL, $this->prepHttpPath());
        curl_setopt($curlHandler, CURLOPT_POST, count($params) > 0 ? true : false);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $postValues);

        $response = curl_exec($curlHandler);
        $getContent = $this->responseContent($response, $curlHandler);

        return $getContent;
    }
}