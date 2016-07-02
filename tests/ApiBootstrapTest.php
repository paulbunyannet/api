<?php
namespace Pbc\Api;

/**
 * ApiBootstrapTest
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\Api
 */
class ApiBootstrapTest extends \PHPUnit_Framework_TestCase
{

    protected static $tempDir = 'tmp';

    /**
     * @return string
     */
    public static function tempDir()
    {
        return __DIR__ . '/' . self::$tempDir;
    }

    public function setUp()
    {
        parent::setUp();
        // if the temp dir exist and is a directory, wipe it, otherwise make the dir
        if (file_exists(self::tempDir()) && is_dir(self::tempDir())) {
            // remove any old files in the temp directory
            $scanDir = scandir(self::tempDir());
            if (count($scanDir) > 0) {
                foreach ($scanDir as $file) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    exec('rm -f ' . self::tempDir() . '/' . $file);
                }
            }
        } else {
            exec('mkdir ' . self::tempDir());
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        if (file_exists(self::tempDir())) {
            exec('rm -rf ' . self::tempDir());
        }

    }

    /**
     * Check that the responseContent method returns a string if invalid json string is passed
     * @test
     */
    public function responseContent_returns_a_string_if_invalid_json_response_is_passed()
    {

        $faker = \Faker\Factory::create();
        $filePath = self::tempDir() . '/' . __FUNCTION__ . '.txt';
        $logFile = self::tempDir() . '/' . __FUNCTION__ . '_log.txt';
        $string = $faker->sentence();
        file_put_contents($filePath, $string);
        $boot = new ApiBootstrap('file://' . $filePath, [], true);
        $boot->setLogFile($logFile);
        $handler = $boot->curlBootstrap();

        $response = $boot->curlResponse($handler);

        $responseContent = $boot->responseContent($response, $handler);

        $this->assertSame($responseContent, $string);

    }

    /**
     * Test that when a json response with a numerical
     * array, the first object will have a key
     * response with the json array returned
     * when debugging is turned on.
     *
     * @test
     * @covers \Pbc\Api\ApiBootstrap::responseContent
     */
    public function responseContent_returns_with_response_if_content_is_a_numerical_array()
    {
        $faker = \Faker\Factory::create();
        $filePath = self::tempDir() . '/' . __FUNCTION__ . '.txt';
        $logFile = self::tempDir() . '/' . __FUNCTION__ . '_log.txt';
        $key  =$faker->word;
        $val = $faker->sentence();
        $data = [[$key => $val]];
        file_put_contents($filePath, json_encode($data));
        $boot = new ApiBootstrap('file://' . $filePath, [], true);
        $boot->setLogFile($logFile);
        $handler = $boot->curlBootstrap();
        $response = $boot->curlResponse($handler);

        $responseContent = $boot->responseContent($response, $handler);

        $this->assertTrue(is_array($responseContent));
        $this->assertTrue(isset($responseContent[0]));
        $this->assertArrayHasKey(0, $responseContent);

        $this->assertObjectHasAttribute('response', $responseContent[0]);
        $this->assertSame($responseContent[0]->response, $response);

    }

    /**
     * Test that when a json response with a numerical
     * array, the first object will have a key
     * curl with the output of curl_getinfo
     * will be returned.
     *
     * @test
     * @covers \Pbc\Api\ApiBootstrap::responseContent
     */
    public function responseContent_returns_with_curl_info_if_content_is_a_numerical_array()
    {
        $faker = \Faker\Factory::create();
        $filePath = self::tempDir() . '/' . __FUNCTION__ . '.txt';
        $logFile = self::tempDir() . '/' . __FUNCTION__ . '_log.txt';
        $key  =$faker->word;
        $val = $faker->sentence();
        $data = [[$key => $val]];
        file_put_contents($filePath, json_encode($data));
        $boot = new ApiBootstrap('file://' . $filePath, [], true);
        $boot->setLogFile($logFile);
        $handler = $boot->curlBootstrap();
        $response = $boot->curlResponse($handler);
        $responseContent = $boot->responseContent($response, $handler);

        $this->assertTrue(is_array($responseContent));
        $this->assertTrue(isset($responseContent[0]));
        $this->assertArrayHasKey(0, $responseContent);
        $this->assertObjectHasAttribute('curl', $responseContent[0]);
        $this->assertArrayHasKey('url', $responseContent[0]->curl);
        $this->assertsame('file://' . $filePath, $responseContent[0]->curl['url']);
        $this->assertArrayHasKey('content_type', $responseContent[0]->curl);
        $this->assertArrayHasKey('http_code', $responseContent[0]->curl);
        $this->assertArrayHasKey('total_time', $responseContent[0]->curl);

    }

    /**
     * Test that when a json response with object,
     * the object will have a property curl with
     * the output of curl_getinfo returned.
     *
     * @test
     * @covers \Pbc\Api\ApiBootstrap::responseContent
     */
    public function responseContent_returns_with_curl_info_if_content_is_object()
    {
        $faker = \Faker\Factory::create();
        $filePath = self::tempDir() . '/' . __FUNCTION__ . '.txt';
        $logFile = self::tempDir() . '/' . __FUNCTION__ . '_log.txt';
        $key  =$faker->word;
        $val = $faker->sentence();
        $data = [$key => $val];
        file_put_contents($filePath, json_encode($data));
        $boot = new ApiBootstrap('file://' . $filePath, [], true);
        $boot->setLogFile($logFile);
        $handler = $boot->curlBootstrap();
        $response = $boot->curlResponse($handler);
        $responseContent = $boot->responseContent($response, $handler);

        $this->assertTrue(is_object($responseContent));

        $this->assertObjectHasAttribute('curl', $responseContent);
        $this->assertArrayHasKey('url', $responseContent->curl);
        $this->assertsame('file://' . $filePath, $responseContent->curl['url']);
        $this->assertArrayHasKey('content_type', $responseContent->curl);
        $this->assertArrayHasKey('http_code', $responseContent->curl);
        $this->assertArrayHasKey('total_time', $responseContent->curl);

    }

    /**
     * Test that when a json response with a single
     * object the object will have a key
     * response with the json output
     * will be returned.
     *
     * @test
     * @covers \Pbc\Api\ApiBootstrap::responseContent
     */
    public function responseContent_returns_an_object_with_the_property_response_if_response_is_a_object()
    {
        $faker = \Faker\Factory::create();
        $filePath = self::tempDir() . '/' . __FUNCTION__ . '.txt';
        $logFile = self::tempDir() . '/' . __FUNCTION__ . '_log.txt';
        $key  =$faker->word;
        $val = $faker->sentence();
        $data = [$key => $val];
        file_put_contents($filePath, json_encode($data));
        $boot = new ApiBootstrap('file://' . $filePath, [], true);
        $boot->setLogFile($logFile);
        $handler = $boot->curlBootstrap();
        $response = $boot->curlResponse($handler);

        $responseContent = $boot->responseContent($response, $handler);
        $this->assertTrue(is_object($responseContent));

        $this->assertObjectHasAttribute('response', $responseContent);
        $this->assertSame($responseContent->response, $response);
    }

    /**
     * @test
     * @covers \Pbc\Api\ApiSetup::getDebugData
     */
    public function debugData_return_output_of_debug()
    {
        $faker = \Faker\Factory::create();
        $filePath = self::tempDir() . '/' . __FUNCTION__ . '.txt';
        $logFile = self::tempDir() . '/' . __FUNCTION__ . '_log.txt';
        $key  =$faker->word;
        $val = $faker->sentence();
        $data = [$key => $val];
        file_put_contents($filePath, json_encode($data));
        $apiPath = $filePath . '12345';
        $boot = new ApiBootstrap('file://' . $apiPath, [], true);
        $boot->setLogFile($logFile);
        $handler = $boot->curlBootstrap();
        $boot->curlResponse($handler);
        $this->assertContains("Couldn't open file {$apiPath}", $boot->getDebugData());
    }

}
