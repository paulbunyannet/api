<?php

namespace Pbc\Api;

/**
 * Tests for Api class
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';
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
        $get = new Get($this->apiEndPoint . 'get?'.$key.'='.$value);
        $retrieve = $get->retrieve();

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);

    }

    /**
     * Run get request with authentication
     */
    public function testGetWithAuthHeader()
    {
        $authKey = 'Apikey';
        $authValue = md5(time());
        $get = new Get($this->apiEndPoint.'get', [$authKey, $authValue]);
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
        $post = new Post($this->apiEndPoint.'post', [$authKey, $authValue]);
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
        $put = new Put($this->apiEndPoint.'put', [$authKey, $authValue]);
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
        $delete = new Delete($this->apiEndPoint.'delete?'.$key.'='.$value, [$authKey, $authValue]);
        $retrieve = $delete->retrieve();

        $this->assertTrue(is_object($retrieve));
        $this->assertObjectHasAttribute('args',$retrieve);
        $this->assertObjectHasAttribute($key, $retrieve->args);
        $this->assertSame($retrieve->args->{$key}, $value);

        $this->assertObjectHasAttribute('headers',$retrieve);
        $this->assertObjectHasAttribute($authKey, $retrieve->headers);
        $this->assertSame($retrieve->headers->{$authKey}, $authValue);

    }
}
