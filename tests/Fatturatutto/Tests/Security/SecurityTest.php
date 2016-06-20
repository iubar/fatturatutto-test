<?php
namespace Fatturatutto\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Iubar\RestApi_TestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Test Security Address
 *
 * @author Matteo
 *        
 */
class SecurityTest extends RestApi_TestCase {

    const BASE_URI = "https://www.fatturatutto.it";

    const APP_HOME = "http://app.fatturatutto.it/";

    const DATASLANG = "http://www.dataslang.com";
    // http status code
    const FORBIDDEN = 403;

    const UNAUTHORIZED = 401;

    const MOVED = 301;

    const OK = 200;

    const GET = 'get';
    
    // seconds
    const TIMEOUT = 4;

    protected $client = null;

    /**
     * Create a Client
     */
    public function setUp() {
        // Base URI is used with relative requests
        // You can set any number of default request options.
        $this->client = new Client([
            'base_uri' => self::BASE_URI,
            'timeout' => self::TIMEOUT
        ]);
    }

    /**
     * Test Forbidden and Unauthorized api
     */
    public function testForbidden() {
        $urls = [
            self::FORBIDDEN => array(
                self::BASE_URI . "/app/logs/",
                self::BASE_URI . "/app/vendor",
                self::APP_HOME . "/logs",
                self::APP_HOME . "/vendor"
            ),
            self::UNAUTHORIZED => array(
                self::DATASLANG . "/wp-login.php"
            ),
            self::OK => array(
                'http://www.iubar.it/bugtracker'
            )
        ];
        
        foreach ($urls as $error_code => $url) {
            $status_code = null;
            foreach ($url as $value_uri) {
                while ($status_code == null || $status_code == self::MOVED) {
                    $request = new Request(self::GET, $value_uri);
                    
                    // Guzzle 6.x
                    // Per the docs, the exception types you may need to catch are:
                    // GuzzleHttp\Exception\ClientException for 400-level errors
                    // GuzzleHttp\Exception\ServerException for 500-level errors
                    // GuzzleHttp\Exception\BadResponseException for both (it's their superclass)
                    
                    try {
                        
                        $response = $this->client->send($request, [
                            'timeout' => self::TIMEOUT,
                            'allow_redirects' => true
                        ]);
                        // the execution continues only if there isn't any errors 4xx or 5xx
                        $status_code = $response->getStatusCode();
                        $this->assertEquals($error_code, $status_code);
                    } catch (ClientException $e) { // for 400-level errors
                        $response = $e->getResponse();
                        // $responseBodyAsString = $response->getBody()->getContents();
                        $status_code = $response->getStatusCode();
                        $this->assertEquals($error_code, $status_code);
                    } catch (ServerException $e) { // for 500-level errors
                        $this->fail('500-level errors');
                    }
                }
            }
        }
    }

    public function testFinish() {
        echo PHP_EOL . 'FINE TEST SECURITY OK!!!!!!!!' . PHP_EOL;
    }
}
