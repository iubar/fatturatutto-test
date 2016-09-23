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

    const FATTURATUTTO_WEBSITE = "https://www.fatturatutto.it"; // Restituisce: GuzzleHttp\Exception\ConnectException: cURL error 35: gnutls_handshake() failed: A TLS warning alert has been received. 
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
        
        
            // How can I add custom cURL options ? - http://docs.guzzlephp.org/en/latest/faq.html#how-can-i-add-custom-curl-options
            
//             The cURL docs further describe CURLOPT_SSLVERSION:
//         
//             CURL_SSLVERSION_DEFAULT: The default action. This will attempt to figure out the remote SSL protocol version, i.e. either SSLv3 or TLSv1 (but not SSLv2, which became disabled by default with 7.18.1).
//             CURL_SSLVERSION_TLSv1: Force TLSv1.x
//             CURL_SSLVERSION_SSLv2: Force SSLv2
//             CURL_SSLVERSION_SSLv3: Force SSLv3
//             CURL_SSLVERSION_TLSv1_0: Force TLSv1.0 (Added in 7.34.0)
//             CURL_SSLVERSION_TLSv1_1: Force TLSv1.1 (Added in 7.34.0)
//             CURL_SSLVERSION_TLSv1_2: Force TLSv1.2 (Added in 7.34.0)

        // Verificare con comando segbuente:
        // openssl s_client -showcerts -connect www.fatturatutto.it:443
        // openssl s_client -showcerts -cert C:\Users\Daniele\workspace_php\fatturatutto-test\tests\Fatturatutto\Tests\Security\2_fatturatutto.it.crt -connect www.fatturatutto.it:443
        // openssl s_client -connect www.fatturatutto.it:443 -showcerts -CAfile mozilla-root-certs.crt C:\Users\Daniele\workspace_php\fatturatutto-test\tests\Fatturatutto\Tests\Security\1_root_bundle.crt
        // curl -vvI https://app.fatturatutto.it (solo da LINUX)
            
        $curl_options = null;
        
        $cert_file = false;
        if (getenv('TRAVIS')) {
            // PER TRAVIS
            $curl_options = array( // http://php.net/manual/en/function.curl-setopt.php
                //CURLOPT_SSLVERSION => CURL_SSLVERSION_SSLv3
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
                CURLOPT_SSL_VERIFYHOST => 2, // oppure false
                CURLOPT_SSL_VERIFYPEER => 1, // oppure false
                // CURLOPT_CAPATH => realpath(getenv('TRAVIS_BUILD_DIR')),
                CURLOPT_CAINFO =>  realpath(getenv('TRAVIS_BUILD_DIR')) . '/cacert.pem',
                // CURLOPT_CAINFO =>  realpath(getenv('TRAVIS_BUILD_DIR')) . '/1_root_bundle.crt',
                CURLOPT_VERBOSE => 0
                //CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                //CURLOPT_USERPWD => $this->getConfig('application_id') . ':' . $this->getConfig('application_password'),
            );
            
            self::$climate->comment('Travis os: ' . getenv('TRAVIS_OS_NAME')); // https://docs.travis-ci.com/user/ci-environment/
            self::$climate->comment('Travis php version: ' . getenv('TRAVIS_PHP_VERSION')); // https://docs.travis-ci.com/user/environment-variables/
            self::$climate->comment('Travis build dir: ' . getenv('TRAVIS_BUILD_DIR')); // https://docs.travis-ci.com/user/environment-variables/
            $cert_file = getenv('TRAVIS_BUILD_DIR') . DIRECTORY_SEPARATOR . "2_fatturatutto.it.crt";
        
        }else{
            // PER WINDOWS
            $curl_options = array( // http://php.net/manual/en/function.curl-setopt.php
                //CURLOPT_SSLVERSION => 3
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
                CURLOPT_SSL_VERIFYHOST => 2, // oppure false
                CURLOPT_SSL_VERIFYPEER => 1, // oppure false
                // CURLOPT_CAPATH => 'C:/Users/Daniele/PortableApps/MyApps/EasyPHP-DevServer-14.1VC11/data', // Apparently does not work in Windows due to some limitation in openssl !!!
                CURLOPT_CAINFO => 'C:/Users/Daniele/PortableApps/MyApps/EasyPHP-DevServer-14.1VC11/data/cacert.pem',
                CURLOPT_VERBOSE => 1
                //CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                //CURLOPT_USERPWD => $this->getConfig('application_id') . ':' . $this->getConfig('application_password'),
            );
            
            $cert_file = __DIR__ . DIRECTORY_SEPARATOR . "2_fatturatutto.it.crt";
        }
        $cert_file = realpath($cert_file);
        if(!is_file($cert_file)){
            $this->fail('Cert file not found: ' . $cert_file);
        }
        self::$climate->comment('Cert file: ' . $cert_file);
        
        foreach ($urls as $error_code => $urls) {
            $status_code = null;
            foreach ($urls as $value_uri) {
                self::$climate->comment('Url: ' . $value_uri);
                $bOk = false;
                while ($status_code == null || $bOk == false) {

                    // Guzzle 6.x
                    // Per the docs, the exception types you may need to catch are:
                    // GuzzleHttp\Exception\ClientException for 400-level errors
                    // GuzzleHttp\Exception\ServerException for 500-level errors
                    // GuzzleHttp\Exception\BadResponseException for both (it's their superclass)
                    
                    try {
                        $response = null;
                        
                        if(false){
                            $request = new Request(self::GET, $value_uri);
                            $response = self::$client->send($request, [
                               'timeout' => self::TIMEOUT,
                               // 'allow_redirects' => true,  // if status code is MOVED this makes redirects automatically
                                'verify' => $cert_file, // Why am I getting an SSL verification error ?
                                                        // @see: http://docs.guzzlephp.org/en/latest/faq.html#why-am-i-getting-an-ssl-verification-error
                                                        // @see: http://docs.guzzlephp.org/en/latest/request-options.html#verify-option
                                'curl' => $curl_options
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
