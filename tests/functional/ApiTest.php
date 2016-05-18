<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

require_once ('RestApiRoot.php');

/**
 * API Test
 *
 * @author Matteo
 *        
 */
class ApiTest extends RestApiRoot {

    const APP_HOME = "http://www.iubar.it/extranet/api/";

    const ELEM_LIMIT = 3;

    const GET = 'get';
    
    // seconds
    const TIMEOUT = 4;

    const TWITTER = 'twitter';

    const CONTENT_TYPE = 'Content-Type';

    const APP_JSON_CT = 'application/json';

    const OK_STATUS_CODE = 200;

    const LENGTH = 100;

    const RSS = 'rss';

    protected $client = null;

    /**
     * Create a Client
     */
    public function setUp() {
        // Base URI is used with relative requests
        // You can set any number of default request options.
        $this->client = new Client([
            'base_uri' => self::APP_HOME,
            'timeout' => self::TIMEOUT
        ]);
    }

    /**
     * Test Twitter api
     */
    public function testTwitterRequest() {
        $response = null;
        try {
            $request = new Request(self::GET, self::TWITTER);
            $response = $this->client->send($request, [
                'timeout' => self::TIMEOUT,
                'query' => [
                    'limit' => self::ELEM_LIMIT
                ]
            ]);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        
        try {
            // Response
            $this->assertContains(self::APP_JSON_CT, $response->getHeader(self::CONTENT_TYPE)[0]);
            $this->assertEquals(self::OK_STATUS_CODE, $response->getStatusCode());
            
            // Data
            $data = json_decode($response->getBody(), true);
            $this->assertEquals(self::ELEM_LIMIT, count($data));
            $first_obj = $data[0];
            $this->assertArrayHasKey('short_text', $first_obj);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->handleAssertionException($e);
        }
        
        /*
         * foreach ($response->getHeaders() as $name => $values) {
         * echo $name . ': ' . implode(', ', $values) . "\r\n";
         * }
         */
    }

    /**
     * Test rss api
     */
    public function testRssRequest() {
        $response = null;
        try {
            $request = new Request(self::GET, self::APP_HOME . self::RSS);
            $response = $this->client->send($request, [
                'timeout' => self::TIMEOUT,
                'query' => [
                    'limit' => self::ELEM_LIMIT,
                    'length' => self::LENGTH
                ]
            ]);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }
}