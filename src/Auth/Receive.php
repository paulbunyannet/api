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
        $this->remoteHash = $remoteHash;

    }

    /**
     * @param array $list
     * @return bool
     */
    public function verifyHash(array $list = [])
    {
        $timestamp = array_key_exists(AuthBootstrap::TIMESTAMP, $list) ? $list[AuthBootstrap::TIMESTAMP] : gmdate('U');
        $timeStampRange = $this->getTimestampRange($timestamp);
        foreach($timeStampRange as $stamp) {
            $newList = [];
            $newList = array_merge($list, $newList);
            $newList[AuthBootstrap::TIMESTAMP] = $stamp;
            $hash = $this->generateHash($newList);
            if($hash === $this->getRemoteHash()) {
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
    public function generateHash(array $list = [])
    {
        return $this->hash($list);
    }

    /**
     * @return mixed
     */
    protected function getRemoteHash()
    {
        return $this->remoteHash;
    }

    /**
     * @param mixed $remoteHash
     */
    protected function setRemoteHash($remoteHash)
    {
        $this->remoteHash = $remoteHash;
    }

}
