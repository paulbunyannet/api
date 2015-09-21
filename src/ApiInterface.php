<?php

namespace Pbc\Api;

/**
 * Class Get
 * @package Mailblast\Api
 */
interface ApiInterface
{

    public function __construct($apiPath = '', array $headers = [], $debug = false);

    /**
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve(array $params = []);

}