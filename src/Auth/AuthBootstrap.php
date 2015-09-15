<?php
/**
 * AuthBootstrap
 *
 * Created 9/14/15 2:10 PM
 * Bootstrap for Authentication classes
 *
 * @author Nate Nolting <me@natenolting.com>
 * @package Pbc\Api
 * @subpackage Auth
 */

namespace Pbc\Api\Auth;


class AuthBootstrap
{
    const IDENTITY   = 'ident';
    const PRIVATEKEY = 'pkey';
    const TIMESTAMP  = 'tps';
    const PAYLOAD    = 'payload';

    protected $identity;
    protected $privateKey;

    public function __construct($identity = 'my-identity', $privateKey = 'my-private-key')
    {
        $this->setIdentity($identity);
        $this->setPrivateKey($privateKey);
    }

    public function hash(array $list = [])
    {
        ksort($list);
        return hash('sha1', json_encode($list));
    }


    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param mixed $identity
     */
    protected function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     * @param mixed $privateKey
     */
    protected function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }
}