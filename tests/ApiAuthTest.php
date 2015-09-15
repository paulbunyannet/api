<?php
/**
 * ApiAuthTest
 *
 * Created 9/14/15 2:48 PM
 * Tests for the API authentication routine
 *
 * @author Nate Nolting <me@natenolting.com>
 * @package Pbc\Api
 * @subpackage Test
 */

namespace Pbc\Api;

use Faker\Factory;
use Pbc\Api\Auth\AuthBootstrap;

require_once dirname(__DIR__) . '/vendor/autoload.php';

class ApiAuthTest extends \PHPUnit_Framework_TestCase
{

    protected $faker;

    protected $identity = 'user-identity';
    protected $privateKey = 'private-key';
    protected $list = [];

    public function __construct()
    {
        parent::__construct();

        $this->faker = Factory::create();

    }

    protected function setUp()
    {
        $this->identity = $this->faker->userName;
        $this->privateKey = $this->faker->md5;
        $this->list = ['some-key' => $this->faker->sentence()];

    }

    protected function tearDown()
    {
        $this->identity = '';
        $this->privateKey = '';
        $this->list = [];
    }

    public function testSend()
    {
        $sender = new Auth\Send($this->identity, $this->privateKey);
        $hash = $sender->generateHash($this->list);

        // verify that the hash is not empty and returns a string
        $this->assertNotEmpty($hash);
        $this->assertTrue(is_string($hash));
    }

    public function testPrepareRequestFields()
    {
        $sender = new Auth\Send($this->identity, $this->privateKey);
        $sender->prepareRequestFields($this->list);
        $this->assertArrayHasKey(AuthBootstrap::IDENTITY, $this->list);
        $this->assertArrayHasKey(AuthBootstrap::TIMESTAMP, $this->list);
        $this->assertArrayNotHasKey(AuthBootstrap::PAYLOAD, $this->list);
        $this->assertArrayNotHasKey(AuthBootstrap::PRIVATEKEY, $this->list);
    }

    public function testSendGenerateHash()
    {
        $sender = new Auth\Send($this->identity, $this->privateKey);
        $sender->generateHash($this->list);
        $this->assertArrayHasKey(AuthBootstrap::PAYLOAD, $this->list);
        $this->assertArrayHasKey(AuthBootstrap::IDENTITY, $this->list);
        $this->assertArrayHasKey(AuthBootstrap::TIMESTAMP, $this->list);
        $this->assertArrayNotHasKey(AuthBootstrap::PRIVATEKEY, $this->list);
    }

    public function testReceive()
    {
        $sender = new Auth\Send($this->identity, $this->privateKey);
        $hash = $sender->generateHash($this->list);

        // verify check returns true, list will
        // now represent a list of request variables
        $receiver = new Auth\Receive($hash, $this->privateKey);
        $this->assertTrue($receiver->verifyHash($this->list));

        // verify check returns false if bad payload
        $receiver = new Auth\Receive($hash.'x', $this->privateKey);
        $this->assertFalse($receiver->verifyHash($this->list));

        // verify check returns false if bad private key
        $receiver = new Auth\Receive($hash, $this->privateKey . 'x');
        $this->assertFalse($receiver->verifyHash($this->list));

        // verify check returns false if list has data in it that should not be there
        $receiver = new Auth\Receive($hash, $this->privateKey);
        $this->assertFalse($receiver->verifyHash(array_merge($this->list, ['another-thing' => '12345'])));
    }
}