<?php

namespace Pbc\Api;

/**
 * Tests for Api class
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Pbc\Api\Auth;
/**
 * Class ApiTest
 * @package Pbc\Api
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected $apiEndPoint = "http://httpbin.org/";

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @var string $tempFolder Temporary older folder for tests
     */
    protected static $tempFolder;
    /**
     * @var
     */
    protected static $tempFolderName;

    /**
     * Set Up
     */
    public static function setUpBeforeClass()
    {
        self::$tempFolderName = 'tmp';
        self::$tempFolder = __DIR__ . '/'.self::$tempFolderName;
        if(!file_exists(self::$tempFolder)) {
            mkdir(self::$tempFolder);
        }
    }

    /**
     * Delete directory recursively
     * http://stackoverflow.com/a/7288067/405758
     * @param $dir
     */
    protected static function rmdir_recursive($dir) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$dir/$file")) self::rmdir_recursive("$dir/$file");
            else unlink("$dir/$file");
        }
        rmdir($dir);
    }

    /**
     * Tear Down
     */
    public static function tearDownAfterClass()
    {
        self::rmdir_recursive(self::$tempFolder);

        // cleanup any left over log files
        foreach(['Get','Post','Put','Delete'] as $method)
        {
            $className = "Pbc\\Api\\".$method;
            if(class_exists($className)) {
                $m = new $className('http://httpbin.org/' . strtolower($method), ['keyname', 'keyvalue']);
                if (file_exists(dirname(__DIR__) . '/src/' . $m->getLogFile())) {
                    unlink(dirname(__DIR__) . '/src/' . $m->getLogFile());
                }
            }
        }
    }

    /**
     * Run get request
     */
    public function testGet()
    {
        $key = 'something';
        $value = md5(time());
        $get = new Get($this->apiEndPoint . 'get');
        $retrieve = $get->retrieve([$key => $value]);
        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);

    }

    /**
     * Run get request with payload included with posts
     */
    public function testGetWithAuthPayload()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';
        $sender = new Auth\Send($identity, $secretKey);
        $sender->generateHash($posts);
        $get = new Get($this->apiEndPoint . 'get');
        $retrieve = $get->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->args);
        $this->assertSame($retrieve->args->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->args);
        $this->assertSame($retrieve->args->{Auth\AuthBootstrap::PAYLOAD}, $posts[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->args);
        $this->assertSame($retrieve->args->{Auth\AuthBootstrap::TIMESTAMP}, strval($posts[Auth\AuthBootstrap::TIMESTAMP]));

    }   /**
     * Run get request with payload included with posts
     */
    public function testGetWithAuthPayloadInjected()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';

        $get = new Get($this->apiEndPoint . 'get');
        $get->setPayload([Auth\AuthBootstrap::IDENTITY => $identity, Auth\AuthBootstrap::PRIVATEKEY => $secretKey]);
        $retrieve = $get->retrieve($posts);
        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->args);
        $this->assertSame($retrieve->args->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->args);
        $this->assertSame($retrieve->args->{Auth\AuthBootstrap::PAYLOAD}, $get->getPayload()[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->args);
        $this->assertSame($retrieve->args->{Auth\AuthBootstrap::TIMESTAMP}, strval($get->getPayload()[Auth\AuthBootstrap::TIMESTAMP]));

    }

    /**
     * Run get request with authentication
     */
    public function testGetWithAuthHeader()
    {
        $authKey = 'Apikey';
        $authValue = md5(time());
        $get = new Get($this->apiEndPoint.'get');
        $get->setHeaders([$authKey => $authValue]);
        $retrieve = $get->retrieve();

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('headers',$retrieve);
        $this->assertObjectHasAttribute($authKey, $retrieve->headers);
        $this->assertSame($retrieve->headers->{$authKey}, $authValue);

    }

    /**
     * run post request
     */
    public function testPost()
    {
        $key = 'postkey';
        $value = md5(time());
        $post = new Post($this->apiEndPoint . 'post');
        $retrieve = $post->retrieve([$key => $value]);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);
    }

    /**
     * Run post request with authentication
     */
    public function testPostWithAuthHeader()
    {
        $authKey = 'Apikey';
        $authValue = md5(time());
        $key = 'postkey';
        $value = md5(time());
        $post = new Post($this->apiEndPoint.'post');
        $post->setHeaders([$authKey => $authValue]);
        $retrieve = $post->retrieve([$key => $value]);

        $this->assertTrue(is_object($retrieve));

        $this->assertObjectHasAttribute('form',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute('headers',$retrieve);
        $this->assertObjectHasAttribute($authKey, $retrieve->headers);
        $this->assertSame($retrieve->headers->{$authKey}, $authValue);

    }

    /**
     * Run Post request with payload
     */
    public function testPostWithAuthPayload()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';
        $sender = new Auth\Send($identity, $secretKey);
        $sender->generateHash($posts);
        $post = new Post($this->apiEndPoint . 'post');
        $retrieve = $post->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::PAYLOAD}, $posts[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::TIMESTAMP}, strval($posts[Auth\AuthBootstrap::TIMESTAMP]));

    }    /**
     * Run Post request with payload
     */
    public function testPostWithAuthPayloadInjected()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';
        $post = new Post($this->apiEndPoint . 'post');
        $post->setPayload([Auth\AuthBootstrap::IDENTITY => $identity, Auth\AuthBootstrap::PRIVATEKEY => $secretKey]);
        $retrieve = $post->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::PAYLOAD}, $post->getPayload()[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::TIMESTAMP}, strval($post->getPayload()[Auth\AuthBootstrap::TIMESTAMP]));

    }


    /**
     * run put request
     */
    public function testPut()
    {
        $key = 'putkey';
        $value = md5(time());
        $put = new Put($this->apiEndPoint . 'put');
        $retrieve = $put->retrieve([$key => $value]);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);
    }

    /**
     * Run put request with authentication
     */
    public function testPutWithAuthHeader()
    {
        $authKey = 'Apikey';
        $authValue = md5(time());
        $key = 'putkey';
        $value = md5(time());
        $put = new Put($this->apiEndPoint.'put');
        $put->setHeaders([$authKey => $authValue]);
        $retrieve = $put->retrieve([$key => $value]);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute('headers',$retrieve);
        $this->assertObjectHasAttribute($authKey, $retrieve->headers);
        $this->assertSame($retrieve->headers->{$authKey}, $authValue);

    }



    /**
     * Run Put request with payload
     */
    public function testPutWithAuthPayload()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';
        $sender = new Auth\Send($identity, $secretKey);
        $sender->generateHash($posts);
        $put = new Put($this->apiEndPoint . 'put');
        $retrieve = $put->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::PAYLOAD}, $posts[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::TIMESTAMP}, strval($posts[Auth\AuthBootstrap::TIMESTAMP]));

    }
    /**
     * Run Put request with payload
     */
    public function testPutWithAuthPayloadInjected()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';
        $put = new Put($this->apiEndPoint . 'put');
        $put->setPayload([Auth\AuthBootstrap::IDENTITY => $identity, Auth\AuthBootstrap::PRIVATEKEY => $secretKey]);
        $retrieve = $put->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::PAYLOAD}, $put->getPayload()[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::TIMESTAMP}, strval($put->getPayload()[Auth\AuthBootstrap::TIMESTAMP]));

    }

    /**
     * Run delete request
     */
    public function testDelete()
    {
        $value = md5(time());
        $key = 'deletekey';
        $delete = new Delete($this->apiEndPoint . 'delete?'.$key.'='.$value);
        $retrieve = $delete->retrieve();

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);
    }

    /**
     * Run delete request with authentication
     */
    public function testDeleteWithAuthHeader()
    {
        $authKey = 'Apikey';
        $authValue = md5(time());
        $key = 'deletekey';
        $value = md5(time());
        $delete = new Delete($this->apiEndPoint.'delete?'.$key.'='.$value);
        $delete->setHeaders([$authKey => $authValue]);
        $retrieve = $delete->retrieve();

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);

        $this->assertObjectHasAttribute('headers',$retrieve);
        $this->assertObjectHasAttribute($authKey, $retrieve->headers);
        $this->assertSame($retrieve->headers->{$authKey}, $authValue);

    }



    /**
     * Run Delete request with payload
     */
    public function testDeleteWithAuthPayload()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';
        $sender = new Auth\Send($identity, $secretKey);
        $sender->generateHash($posts);
        $delete = new Delete($this->apiEndPoint . 'delete');
        $retrieve = $delete->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::PAYLOAD}, $posts[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::TIMESTAMP}, strval($posts[Auth\AuthBootstrap::TIMESTAMP]));

    }/**
     * Run Delete request with payload
     */
    public function testDeleteWithAuthPayloadInjected()
    {
        $key = 'something';
        $value = md5(time());
        $posts = [$key => $value];
        $identity = md5(time());
        $secretKey = 'my-super-secret-key';

        $delete = new Delete($this->apiEndPoint . 'delete');
        $delete->setPayload([Auth\AuthBootstrap::IDENTITY => $identity, Auth\AuthBootstrap::PRIVATEKEY => $secretKey]);
        $retrieve = $delete->retrieve($posts);

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('form',$retrieve);

        $this->assertObjectHasAttribute($key, $retrieve->form);
        $this->assertSame($retrieve->form->{$key}, $value);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::IDENTITY, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::IDENTITY}, $identity);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::PAYLOAD, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::PAYLOAD}, $delete->getPayload()[Auth\AuthBootstrap::PAYLOAD]);

        $this->assertObjectHasAttribute(Auth\AuthBootstrap::TIMESTAMP, $retrieve->form);
        $this->assertSame($retrieve->form->{Auth\AuthBootstrap::TIMESTAMP}, strval($delete->getPayload()[Auth\AuthBootstrap::TIMESTAMP]));

    }
}
