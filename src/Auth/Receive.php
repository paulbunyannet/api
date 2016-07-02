<?php
/**
 * Receive
 *
 * Created 9/14/15 2:07 PM
 * Receive request and check hash
 *
 * @author natenolting <natenolting@email.com>
 * @package Pbc\Api\Auth
 * @subpackage Subpackage
 */

namespace Pbc\Api\Auth;

/**
 * Class Receive
 * @package Pbc\Api\Auth
 */
class Receive extends AuthBootstrap implements AuthInterface
{
    /**
     * @var int $timeStampLimit number of minutes that a timestamp should be valid
     */
    protected $timeStampLimit = 5;
    protected $remoteHash;

    /**
     * @param $remoteHash
     * @param $privateKey
     */
    public function __construct($remoteHash, $privateKey)
    {
        parent::__construct('', $privateKey);
        $this->setRemoteHash($remoteHash);

    }

    /**
     * @param array $list
     * @return bool
     */
    public function verifyHash(array $list = [])
    {
        $timestamp = array_key_exists(AuthBootstrap::TIMESTAMP, $list) ? $list[AuthBootstrap::TIMESTAMP] : gmdate('U');
        $timeStampRange = $this->getTimestampRange($timestamp);
        foreach ($timeStampRange as $stamp) {
            $newList = [AuthBootstrap::PRIVATEKEY => $this->getPrivateKey()];
            $newList = array_merge($list, $newList);
            $newList[AuthBootstrap::TIMESTAMP] = $stamp;
            $this->generateHash($newList);
            if ($newList[AuthBootstrap::PAYLOAD] === $this->getRemoteHash()) {
                return true;
            }
        }

        return false;

    }

    /**
     * @param $timestamp
     * @return array
     */
    private function getTimestampRange($timestamp)
    {
        $timeStampLimit = floor(60 * 60 * $this->timeStampLimit);
        return range(intval($timestamp - $timeStampLimit), intval($timestamp + $timeStampLimit));
    }

    /**
     * @param array $list
     * @return string
     */
    public function generateHash(array &$list = [])
    {
        $newList = array_merge($list, []);
        // make sure payload key is not in the list prior to generating hash
        unset($newList[AuthBootstrap::PAYLOAD]);
        $list[AuthBootstrap::PAYLOAD] = $this->hash($newList);
        return $list[AuthBootstrap::PAYLOAD];
    }

    /**
     * @return mixed
     */
    public function getRemoteHash()
    {
        return $this->remoteHash;
    }

    /**
     * @param mixed $remoteHash
     */
    public function setRemoteHash($remoteHash)
    {
        $this->remoteHash = $remoteHash;
    }
}
