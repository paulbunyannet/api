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


/**
 * Class Send
 * @package Pbc\Api\Auth
 */
class Send extends AuthBootstrap implements AuthInterface
{

    /**
     * @var bool
     */
    protected $requestPayloadFieldPrepared = false;

    /**
     * @param string $identity
     * @param string $privateKey
     */
    public function __construct($identity = 'my-identity', $privateKey='my-private-key')
    {
        parent::__construct($identity, $privateKey);

    }

    /**
     * @param array $list
     * @return $this
     */
    public function prepareRequestFields(&$list = [])
    {
        $this->identityRequestField($list);
        $this->timeStampRequestField($list);
        return $list;
    }

    /**
     * @param array $list
     * @return string
     */
    public function generateHash(array &$list = [])
    {
        $this->prepareRequestFields($list);
        if(!$this->requestPayloadFieldPrepared) {
            $this->payloadRequestField($list);
        }

        return $list[AuthBootstrap::PAYLOAD];
    }

    /**
     * @return string
     */
    private function timeStamp()
    {
        return gmdate('U');
    }

    /**
     * @param array $list
     * @return $this
     */
    private function identityRequestField(&$list = [])
    {
        $list[AuthBootstrap::IDENTITY] = $this->getIdentity();
        return $this;
    }

    /**
     * @param array $list
     * @return $this
     */
    private function timeStampRequestField(&$list = [])
    {
        $list[AuthBootstrap::TIMESTAMP] = intval($this->timeStamp());
        return $this;
    }

    /**
     * Create payload hash, this is where the primary key is used to generate final hash
     * @param array $list
     * @return $this
     */
    public function payloadRequestField(&$list = [])
    {
        $newList = array_merge($list, [AuthBootstrap::PRIVATEKEY => $this->getPrivateKey()]);
        $list[AuthBootstrap::PAYLOAD] = $this->hash($newList);
        $this->requestPayloadFieldPrepared = true;
        return $this;
    }


}