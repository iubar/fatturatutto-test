<?php
namespace Fatturatutto\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Iubar\RestApi_TestCase;

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
            )
        ];
        
        foreach ($urls as $error_code => $url) {
            $status_code = null;
            foreach ($url as $value) {
                while ($status_code == null || $status_code == self::MOVED) {
                    $request = new Request(self::GET, $value);
                    $response = $this->client->send($request, [
                        'timeout' => self::TIMEOUT
                    ]);
                    $status_code = $response->getStatusCode();
                    if ($status_code == self::MOVED) {
                        $url = $response->getUri();
                    }
                }
                assertEqual($error_code, $status_code);
            }
        }
    }
}
