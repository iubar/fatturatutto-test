<?php
namespace Fatturatutto\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Iubar\RestApi_TestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use League\CLImate\CLImate;

/**
 * Test Security Address
 *
 * @author Matteo
 */
class SecurityTest extends RestApi_TestCase {

    const FATTURATUTTO_WEBSITE = "https://www.fatturatutto.it";

    const FATTURATUTTO_WEBAPP = "http://app.fatturatutto.it/";

    const DATASLANG_WEBSITE = "http://www.dataslang.com";

    const IUBAR_WEBSITE = "http://www.iubar.it";
    
    // http status code
    const OK = 200;

    const MOVED = 301;

    const UNAUTHORIZED = 401;

    const FORBIDDEN = 403;

    const NOT_FOUND = 404;

    const GET = 'get';
    
    // seconds
    const TIMEOUT = 4;

    protected $client = null;
    
    // easily output colored text and special formatting
    protected static $climate;

    /**
     * Create a Client
     */
    public function setUp() {
        self::$climate = new CLImate();
        // Base URI is used with relative requests
        // You can set any number of default request options.
        $this->client = new Client([
            'base_uri' => self::FATTURATUTTO_WEBSITE,
            'timeout' => self::TIMEOUT
        ]);
    }

    /**
     * Test Forbidden and Unauthorized api
     */
    public function testForbidden() {
        // the status code and the relative address to check
        $urls = [
            self::FORBIDDEN => array(
                self::FATTURATUTTO_WEBSITE . "/app/logs/",
                self::FATTURATUTTO_WEBAPP . "/logs",
                self::FATTURATUTTO_WEBAPP . "/vendor"
            ),
            self::UNAUTHORIZED => array(
                self::DATASLANG_WEBSITE . "/wp-login.php"
            ),
            self::OK => array(
                self::IUBAR_WEBSITE . '/bugtracker'
            ),
            self::NOT_FOUND => array(
                self::FATTURATUTTO_WEBSITE . "/app/vendor"
            )
        ];
        
        foreach ($urls as $error_code => $url) {
            $status_code = null;
            foreach ($url as $value_uri) {
                $bOk = false;
                while ($status_code == null || $bOk == false) {
                    $request = new Request(self::GET, $value_uri);
                    
                    // Guzzle 6.x
                    // Per the docs, the exception types you may need to catch are:
                    // GuzzleHttp\Exception\ClientException for 400-level errors
                    // GuzzleHttp\Exception\ServerException for 500-level errors
                    // GuzzleHttp\Exception\BadResponseException for both (it's their superclass)
                    
                    try {
                        $response = $this->client->send($request, [
                            'timeout' => self::TIMEOUT,
                            // if status code is MOVED this makes redirects automatically
                            'allow_redirects' => true
                        ]);
                        
                        // the execution continues only if there isn't any errors 4xx or 5xx
                        $status_code = $response->getStatusCode();
                        $this->assertEquals($error_code, $status_code);
                        $bOk = true;
                    } catch (ClientException $e) { // for 400-level errors
                        $response = $e->getResponse();
                        $status_code = $response->getStatusCode();
                        $this->assertEquals($error_code, $status_code);
                        $bOk = true;
                    } catch (ServerException $e) { // for 500-level errors
                        $this->fail('500-level errors');
                    }
                }
            }
        }
    }

    public function testFinish() {
        self::$climate->info('FINE TEST SECURITY OK!!!!!!!!');
    }
}
