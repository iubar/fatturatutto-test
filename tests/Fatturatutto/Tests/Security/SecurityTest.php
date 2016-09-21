<?php

namespace Fatturatutto\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Iubar\Tests\RestApi_TestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use League\CLImate\CLImate;

/**
 * Test Security Address
 *
 * @author Matteo
 */

class SecurityTest extends RestApi_TestCase {

    const FATTURATUTTO_WEBSITE = "https://www.fatturatutto.it";   // Restituisce: GuzzleHttp\Exception\ConnectException: cURL error 35: gnutls_handshake() failed: A TLS warning alert has been received. 
                                                                    // @see: http://curl.haxx.se/libcurl/c/libcurl-errors.html
                                                                    // @see: http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
    const FATTURATUTTO_WEBAPP = "http://app.fatturatutto.it";

    const DATASLANG_WEBSITE = "http://www.dataslang.com";

    const IUBAR_WEBSITE = "http://www.iubar.it";
    
    /**
     * Create a Client
     */
    public static function setUpBeforeClass() {        
        self::init();
        // Base URI is used with relative requests
        // You can set any number of default request options.        
        putenv("HTTP_HOST=" . self::FATTURATUTTO_WEBSITE);
        self::$client = self::factoryClient();
    }

    /**
     * Test Forbidden and Unauthorized api
     */
    public function testForbidden() {
        // the status code and the relative address to check
        $urls = [
            self::HTTP_FORBIDDEN => array(
                self::FATTURATUTTO_WEBSITE . "/app/logs/",
                self::FATTURATUTTO_WEBAPP . "/logs",
                self::FATTURATUTTO_WEBAPP . "/vendor"
            ),
            self::HTTP_UNAUTHORIZED => array(
                self::DATASLANG_WEBSITE . "/wp-login.php"
            ),
            self::HTTP_OK => array(
                self::IUBAR_WEBSITE . '/bugtracker'
            ),
            self::HTTP_NOT_FOUND => array(
                self::FATTURATUTTO_WEBSITE . "/app/vendor"
            )
        ];
        
        foreach ($urls as $error_code => $url) {
            $status_code = null;
            foreach ($url as $value_uri) {
                $bOk = false;
                while ($status_code == null || $bOk == false) {
                    //$request = new Request(self::GET, $value_uri);
                    
                    // Guzzle 6.x
                    // Per the docs, the exception types you may need to catch are:
                    // GuzzleHttp\Exception\ClientException for 400-level errors
                    // GuzzleHttp\Exception\ServerException for 500-level errors
                    // GuzzleHttp\Exception\BadResponseException for both (it's their superclass)
                    
                    $curl_options = array();
                    $cert_file = false;
                    if (getenv('TRAVIS')) {
                    
                        // https://docs.travis-ci.com/user/environment-variables/
                        self::$climate->comment('Travis os: ' . getenv('TRAVIS_OS_NAME'));
                        self::$climate->comment('Travis php version: ' . getenv('TRAVIS_PHP_VERSION'));
                        self::$climate->comment('Travis build dir: ' . getenv('TRAVIS_BUILD_DIR'));
                        $cert_file = getenv('TRAVIS_BUILD_DIR') . DIRECTORY_SEPARATOR . "2_fatturatutto.it.crt";
                        if(!is_file($cert_file)){
                            $this->fail('Cert file not found: ' . $cert_file . ' (please see the .travis.yml script)');
                        }else{
                            $cert_file = realpath($cert_file);
                            self::$climate->comment('Cert file: ' . $cert_file);
                        }
                                                
                        // How can I add custom cURL options ? - http://docs.guzzlephp.org/en/latest/faq.html#how-can-i-add-custom-curl-options
                        $curl_options = array(
                            //'CURLOPT_SSLVERSION' => 3
                            // 'CURLOPT_SSLVERSION' => CURL_SSLVERSION_DEFAULT,
                            'CURLOPT_SSL_VERIFYHOST' => false,
                            'CURLOPT_SSL_VERIFYPEER' => false,
                            // 'CURLOPT_HTTPAUTH' => CURLAUTH_BASIC,
                            // 'CURLOPT_USERPWD' =>
                            // $this->getConfig('application_id') . ':' . $this->getConfig('application_password'),
                        );
                    }
                    
                    try {
                        $response = null;
                        if(true){
                        $response = self::$client->send($request, [
                           'timeout' => self::TIMEOUT,
                           // 'allow_redirects' => true,  // if status code is MOVED this makes redirects automatically
                            'verify' => $cert_file, // Why am I getting an SSL verification error ?
                                                    // @see: http://docs.guzzlephp.org/en/latest/faq.html#why-am-i-getting-an-ssl-verification-error
                                                    // @see: http://docs.guzzlephp.org/en/latest/request-options.html#verify-option
                            'curl' => $curl_options,
                            'stream_context' => [
                                'ssl' => [
                                    'allow_self_signed' => true
                                ],
                            ]
                         ]);
                        
                        }else{
                                                        
                            $response = self::$client->request('GET', $value_uri, ['verify' => $cert_file, 'curl' => $curl_options]);                                           
                        }
                        
                        // the execution continues only if there isn't any errors 4xx or 5xx
                        $status_code = $response->getStatusCode();
                        $this->assertEquals($error_code, $status_code);
                        $bOk = true;
                    } catch (ConnectException $e) { // Is thrown in the event of a networking error. (This exception extends from GuzzleHttp\Exception\RequestException.)
                        $this->handleException($e);
                    } catch (ClientException $e) { // Is thrown for 400 level errors if the http_errors request option is set to true.
                        $response = $e->getResponse();
                        $status_code = $response->getStatusCode();
                        $this->assertEquals($error_code, $status_code);
                        $bOk = true;
                    } catch (RequestException $e) { // In the event of a networking error (connection timeout, DNS errors, etc.), a GuzzleHttp\Exception\RequestException is thrown.
                        $this->handleException($e);
                    } catch (ServerException $e) { // Is thrown for 500 level errors if the http_errors request option is set to true.
                        $this->handleException($e);
                    }
                    
                    
                }
            }
        }
    }
}
