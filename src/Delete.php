<?php
namespace Pbc\Api;


class Delete extends Get implements ApiInterface
{

    protected $logFile = 'apiPutErrorLog.txt';

    public function __construct($apiPath = null, array $apiKey = [], $debug = false)
    {
        parent::__construct($apiPath, $apiKey, $debug);
    }


    /**
     * Do Delete to API
     *
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve($params = [])
    {
        $postValues = $this->prepPostParameters($params);
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $this->prepHttpPath());
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $postValues);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HEADER, false);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, true);
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
}