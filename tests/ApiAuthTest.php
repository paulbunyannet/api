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

    public function __construct()
    {
        parent::__construct();

        $this->faker = Factory::create();

    }

    public function testSend()
    {
        $identity = $this->faker->userName;
        $privateKey = $this->faker->md5;
        $list = ['some-key' => $this->faker->sentence()];
        $sender = new Auth\Send($identity, $privateKey);
        $hash = $sender->generateHash($list);

        // verify that the hash is not empty and returns a string
        $this->assertNotEmpty($hash);
        $this->assertTrue(is_string($hash));
    }


    public function testReceive()
    {
        $identity = $this->faker->userName;
        $privateKey = $this->faker->md5;
        $list = ['some-key' => $this->faker->sentence()];
        $sender = new Auth\Send($identity, $privateKey);
        $hash = $sender->generateHash($list);

        // verify check returns true
        $receiver = new Auth\Receive($hash, $privateKey);
        $this->assertTrue($receiver->verifyHash(array_merge($list, [AuthBootstrap::IDENTITY => $identity, AuthBootstrap::PRIMARYKEY => $privateKey])));

        // verify if any of the params are submitted wrong then it will return false
        $receiver = new Auth\Receive($hash.'x', $privateKey);
        $this->assertFalse($receiver->verifyHash(array_merge($list, [AuthBootstrap::IDENTITY => $identity, AuthBootstrap::PRIMARYKEY => $privateKey])));

        $receiver = new Auth\Receive($hash, $privateKey);
        $this->assertFalse($receiver->verifyHash(array_merge($list, [AuthBootstrap::IDENTITY => $identity.'x', AuthBootstrap::PRIMARYKEY => $privateKey])));

        $receiver = new Auth\Receive($hash, $privateKey);
        $this->assertFalse($receiver->verifyHash(array_merge($list, [AuthBootstrap::IDENTITY => $identity, AuthBootstrap::PRIMARYKEY => $privateKey.'x'])));

    }

}