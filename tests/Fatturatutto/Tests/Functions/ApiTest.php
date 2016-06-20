<?php
namespace Fatturatutto\Functions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Iubar\RestApi_TestCase;

/**
 * API Test
 *
 * @author Matteo
 *        
 */
class ApiTest extends RestApi_TestCase {

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

    const CONTACT = 'contact';

    const MAILING_LIST = 'mailing-list/';

    const SUBSCRIBE = 'subscribe';

    const EDIT = 'edit';

    const UNSUBSCRIBE = 'unsubscribe';
    
    // momentanea da cancellare e mettere nella classe padre
    const PRIVATE_LOGIN_DATA = "C:/Users/Matteo/Desktop/protected_folder/users.ini";

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
    public function testTwitter() {
        $response = null;
        try {
            /*
             * $request = new Request(self::GET, self::TWITTER);
             * $array = array(
             * 'limit' => self::ELEM_LIMIT
             * );
             * $response = $this->client->send($request, [
             * 'timeout' => self::TIMEOUT,
             * 'query' => $array
             * ]);
             */
            $array = array(
                'limit' => self::ELEM_LIMIT
            );
            $response = $this->sendRequest(self::GET, self::TWITTER, $array, self::TIMEOUT);
            // echo PHP_EOL.$response->getBody();
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        
        /*
         * // Response
         * $this->assertContains(self::APP_JSON_CT, $response->getHeader(self::CONTENT_TYPE)[0]);
         * $this->assertEquals(self::OK_STATUS_CODE, $response->getStatusCode());
         */
        
        $data = $this->checkResponse($response);
        
        /*
         * // Data
         * $data = json_decode($response->getBody(), true);
         */
        $this->assertEquals(self::ELEM_LIMIT, count($data));
        $first_obj = $data[0];
        $this->assertArrayHasKey('short_text', $first_obj);
        
        /*
         * foreach ($response->getHeaders() as $name => $values) {
         * echo $name . ': ' . implode(', ', $values) . "\r\n";
         * }
         */
    }

    /**
     * Test rss api
     */
    public function testRss() {
        $response = null;
        try {
            $array = array(
                'limit' => self::ELEM_LIMIT,
                'length' => self::LENGTH
            );
            $response = $this->sendRequest(self::GET, self::RSS, $array, self::TIMEOUT);
            // echo PHP_EOL.$response->getBody();
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Test contact api
     */
    public function testContact() {
        $response = null;
        try {
            $array = array(
                'from_name' => 'Matteo',
                'from_email' => 'postmaster@fatturatutto.it',
                'from_domain' => 'fatturatutto.it',
                'subject' => 'Prova API',
                'message' => 'Ciao'
            );
            $response = $this->sendRequest(self::GET, self::CONTACT, $array, self::TIMEOUT + 10);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Test subscribe into the mailing list
     */
    public function testMailingListSubscribe() {
        $response = null;
        try {
            $array = array(
                'email' => getenv('FT_USERNAME'),
                'nome' => 'Matteo',
                'cognome' => 'Prova API',
                'idprofessione' => '1',
                'list_id' => '1'
            );
            $response = $this->sendRequest(self::GET, self::MAILING_LIST . self::SUBSCRIBE, $array, self::TIMEOUT);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Test edit from the mailing list
     */
    public function testMailingListEdit() {
        $response = null;
        try {
            $array = array(
                'email' => getenv('FT_USERNAME'),
                'nome' => 'Matteo',
                'cognome' => 'Prova API',
                'idprofessione' => '2',
                'list_id' => '2'
            );
            $response = $this->sendRequest(self::GET, self::MAILING_LIST . self::EDIT, $array, self::TIMEOUT);
            // echo PHP_EOL.$response->getBody();
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Test unsubscribe from the mailing list
     */
    public function testMailingListUnsubscribe() {
        $response = null;
        try {
            
            $array = array(
                'email' => getenv('FT_USERNAME'),
                'nome' => 'Matteo',
                'cognome' => 'Prova API',
                'idprofessione' => '2',
                'list_id' => '2'
            );
            $response = $this->sendRequest(self::GET, self::MAILING_LIST . self::EDIT, $array, self::TIMEOUT);
            // echo PHP_EOL.$response->getBody();
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    public function testFinish() {
        echo PHP_EOL . 'FINE TEST API OK!!!!!!!!' . PHP_EOL;
    }

    /**
     * Send a request
     *
     * @param string $method the method
     * @param string $partial_uri the partial uri
     * @param string $array the query
     * @param int $timeout the timeout
     * @return string the response
     */
    private function sendRequest($method, $partial_uri, $array, $timeout) {
        try {
            $request = new Request($method, $partial_uri);
            $response = $this->client->send($request, [
                'timeout' => $timeout,
                'query' => $array
            ]);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        return $response;
    }

    /**
     * Check the status code and the content type of the response
     *
     * @param string $response the response
     * @return string a PHP variable of the JSON string of the response
     */
    private function checkResponse($response) {
        // Response
        $this->assertContains(self::APP_JSON_CT, $response->getHeader(self::CONTENT_TYPE)[0]);
        $this->assertEquals(self::OK_STATUS_CODE, $response->getStatusCode());
        
        // Data
        $data = json_decode($response->getBody(), true);
        
        return $data;
    }
}