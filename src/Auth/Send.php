<?php
/**
 * Send
 *
 * Created 9/14/15 1:57 PM
 * Create hash to send for authentication over API
 *
 * @author Nate Nolting <me@natenolting.com>
 * @package Pbc\Api
 * @subpackage Auth
 */

namespace Pbc\Api\Auth;


class Send extends AuthBootstrap implements AuthInterface
{

    public function __construct($identity = 'my-identity', $privateKey='my-private-key')
    {
        parent::__construct($identity, $privateKey);

    }

    public function generateHash(array $list = [])
    {
        $list[AuthBootstrap::IDENTITY] = $this->getIdentity();
        $list[AuthBootstrap::PRIMARYKEY] = $this->getPrivateKey();
        $list[AuthBootstrap::TIMESTAMP] = intval($this->timeStamp());
        return $this->hash($list);
    }

    private function timeStamp()
    {
        return gmdate('U');
    }

}