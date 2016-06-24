<?php
namespace Fatturatutto\Functions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Iubar\RestApi_TestCase;
use League\CLImate\CLImate;

/**
 * API Test
 *
 * @author Matteo
 *        
 * @global ft_username
 *        
 */
class ApiTest extends RestApi_TestCase {

    const IUBAR_EXTRANET_API = "http://www.iubar.it/extranet/api/";
    
    // seconds
    const TIMEOUT = 4;

    const ELEM_LIMIT = 3;

    const PARTIAL_EMAIL = "postmaster@";

    const EMAIL_DOMAIN = "fatturatutto.it";

    const GET = 'get';

    const TWITTER = 'twitter';

    const LENGTH = 100;

    const CONTENT_TYPE = 'Content-Type';

    const APP_JSON_CT = 'application/json';
    
    // http status code
    const OK = 200;

    const RSS = 'rss';

    const CONTACT = 'contact';

    const MAILING_LIST = 'mailing-list/';

    const SUBSCRIBE = 'subscribe';

    const EDIT = 'edit';

    const UNSUBSCRIBE = 'unsubscribe';

    const ID_SUBSCRIBE = 1;

    const ID_EDIT_UNSUBSCRIBE = 2;

    const NOME = "NomeTest";

    const COGNOME = "CognomeTest";

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
            'base_uri' => self::IUBAR_EXTRANET_API,
            'timeout' => self::TIMEOUT
        ]);
    }

    /**
     * Test Twitter
     */
    public function testTwitter() {
        $response = null;
        try {
            $array = array(
                'limit' => self::ELEM_LIMIT
            );
            $response = $this->sendRequest(self::GET, self::TWITTER, $array, self::TIMEOUT);
            // echo PHP_EOL . $response->getBody();
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
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
     * Test Rss
     */
    public function testRss() {
        $response = null;
        try {
            $array = array(
                'limit' => self::ELEM_LIMIT,
                'length' => self::LENGTH
            );
            $response = $this->sendRequest(self::GET, self::RSS, $array, self::TIMEOUT);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
        $this->assertEquals(self::ELEM_LIMIT, count($data));
    }

    /**
     * Test contact
     */
    public function testContact() {
        $response = null;
        try {
            $array = array(
                'from_name' => getEnv('FT_USERNAME'),
                'from_email' => self::PARTIAL_EMAIL . self::EMAIL_DOMAIN,
                'from_domain' => self::EMAIL_DOMAIN,
                'subject' => 'Prova API',
                'message' => 'This is an api test'
            );
            $response = $this->sendRequest(self::GET, self::CONTACT, $array, self::TIMEOUT + 10);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Subscribe into the mailing list
     */
    public function testMailingListSubscribe() {
        $response = null;
        try {
            $array = array(
                'email' => getenv('FT_USERNAME'),
                'nome' => self::NOME,
                'cognome' => self::COGNOME,
                'idprofessione' => self::ID_SUBSCRIBE,
                'list_id' => self::ID_SUBSCRIBE
            );
            $response = $this->sendRequest(self::GET, self::MAILING_LIST . self::SUBSCRIBE, $array, self::TIMEOUT);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Edit from the mailing list
     */
    public function testMailingListEdit() {
        $response = null;
        try {
            $array = array(
                'email' => getenv('FT_USERNAME'),
                'nome' => self::NOME,
                'cognome' => self::COGNOME,
                'idprofessione' => self::ID_EDIT_UNSUBSCRIBE,
                'list_id' => self::ID_EDIT_UNSUBSCRIBE
            );
            $response = $this->sendRequest(self::GET, self::MAILING_LIST . self::EDIT, $array, self::TIMEOUT);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    /**
     * Unsubscribe from the mailing list
     */
    public function testMailingListUnsubscribe() {
        $response = null;
        try {
            $array = array(
                'email' => getenv('FT_USERNAME'),
                'nome' => self::NOME,
                'cognome' => self::COGNOME,
                'idprofessione' => self::ID_EDIT_UNSUBSCRIBE,
                'list_id' => self::ID_EDIT_UNSUBSCRIBE
            );
            $response = $this->sendRequest(self::GET, self::MAILING_LIST . self::EDIT, $array, self::TIMEOUT);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
        $data = $this->checkResponse($response);
    }

    public function testFinish() {
        self::$climate->info('FINE TEST API OK!!!!!!!!');
    }

    /**
     * Send an http request and return the response
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
     * Check the OK status code and the APP_JSON_CT content type of the response
     *
     * @param string $response the response
     * @return string the body of the decode response
     */
    private function checkResponse($response) {
        // Response
        $this->assertContains(self::APP_JSON_CT, $response->getHeader(self::CONTENT_TYPE)[0]);
        $this->assertEquals(self::OK, $response->getStatusCode());
        
        // Data
        $data = json_decode($response->getBody(), true);
        
        return $data;
    }
}