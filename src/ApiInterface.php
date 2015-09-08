<?php

namespace Pbc\Api;

/**
 * Class Get
 * @package Mailblast\Api
 */
interface ApiInterface
{
    /**
     * @param array $params
     * @return mixed|\stdClass
     */
    public function retrieve($params = []);

}