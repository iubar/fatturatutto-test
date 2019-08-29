<?php

namespace Fatturatutto\Tests\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use League\CLImate\CLImate;
use Iubar\Tests\RestApi_TestCase;

/**
 * Test Security Address
 *
 * @author Matteo
 */

class SecurityTest extends RestApi_TestCase {

    const FATTURATUTTO_WEBSITE = "http://www.fatturatutto.it"; // Restituisce: GuzzleHttp\Exception\ConnectException: cURL error 35: gnutls_handshake() failed: A TLS warning alert has been received.
                                                                // @see: http://curl.haxx.se/libcurl/c/libcurl-errors.html
                                                                // @see: http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
    const FATTURATUTTO_WEBAPP = "http://app.fatturatutto.it";

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
            self::HTTP_OK => array(
				  self::FATTURATUTTO_WEBAPP
            ),
            self::HTTP_FORBIDDEN => array(

            ),
            self::HTTP_UNAUTHORIZED => array(

            ),
            self::HTTP_NOT_FOUND => array(
                self::FATTURATUTTO_WEBAPP . "/logs"
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

        // E' possibile effettuare il debug di curl e dei certificati installati sul server con i comandi segbuenti:
		// openssl s_client -connect fatturatutto.it:443 -showcerts -servername app.fatturatutto.it -CAfile C:/Users/Daniele/PortableApps/MyApps/EasyPHP-Devserver-16.1/cacert.pem // OK !
        // curl -vvI https://app.fatturatutto.it (solo da LINUX)
		// To be able to use SNI, three conditions are required:
		// 1) Using a version of Curl that supports it, at least 7.18.1, according to the change logs.
		// 2) Using a version of Curl compiled against a library that supports SNI, e.g. OpenSSL 0.9.8j (depending on the compilation options some older versions).
		// 3) Using TLS 1.0 at least (not SSLv3).
		// More details: SNI sends the hostname inside the TLS handshake (ClientHello). The server then chooses the correct certificate based on this information. Only after the TLS connection is successfully established it will send the HTTP-Request, which contains the Host header you specified.

        $curl_options = null;
		$cacert = null;
        $cert_file = false;
        if (getenv('TRAVIS')) {
            // PER TRAVIS
			$cert_file = realpath(getenv('TRAVIS_BUILD_DIR') . DIRECTORY_SEPARATOR . "2_fatturatutto.it.crt");
			$cacert = realpath(getenv('TRAVIS_BUILD_DIR') . '/cacert.pem');
            $curl_options = array( // http://php.net/manual/en/function.curl-setopt.php
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
                CURLOPT_SSL_VERIFYHOST => 2,	// 1 to check the existence of a common name in the SSL peer certificate.
												// 2 to check the existence of a common name and also verify that it matches the hostname provided.
												// 0 to not check the names.
												// In production environments the value of this option should be kept at 2 (default value).

                CURLOPT_SSL_VERIFYPEER => true,
                // CURLOPT_CAPATH => realpath(getenv('TRAVIS_BUILD_DIR')),
                CURLOPT_CAINFO =>  $cacert,
                CURLOPT_VERBOSE => 0
                //CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                //CURLOPT_USERPWD => $this->getConfig('application_id') . ':' . $this->getConfig('application_password'),
            );

            self::$climate->comment('Travis os: ' . getenv('TRAVIS_OS_NAME')); // https://docs.travis-ci.com/user/ci-environment/
            self::$climate->comment('Travis php version: ' . getenv('TRAVIS_PHP_VERSION')); // https://docs.travis-ci.com/user/environment-variables/
            self::$climate->comment('Travis build dir: ' . getenv('TRAVIS_BUILD_DIR')); // https://docs.travis-ci.com/user/environment-variables/

        }else{
            // PER WINDOWS
			$user_home = getenv('userprofile');
			$project_folder = $user_home . "/workspace_php/fatturatutto-site/public";
            $cert_file = $project_folder . DIRECTORY_SEPARATOR . "2_fatturatutto.it.crt";
			$cacert = $project_folder . DIRECTORY_SEPARATOR . 'cacert.pem';
            $curl_options = array( // http://php.net/manual/en/function.curl-setopt.php
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1, // nota che CURL_SSLVERSION_SSLv3 non supporta SNI
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
				//CURLOPT_CAPATH => $project_folder,
                CURLOPT_CAINFO => $cacert,
                CURLOPT_VERBOSE => 1
                //CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                //CURLOPT_USERPWD => $this->getConfig('application_id') . ':' . $this->getConfig('application_password'),
            );
        }

        if(!is_file($cacert)){
            $this->fail('cacert file not found: ' . $cacert);
        }
        self::$climate->comment('cacert file: ' . $cacert);

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
                while ($status_code == null || $bOk == false) { // FIXME: verificare se il while è inutile. In alternativa potrebbe essere sufficiente usare successivamente "'allow_redirects' => true"

                    // Guzzle 6.x
                    // Per the docs, the exception types you may need to catch are:
                    // GuzzleHttp\Exception\ClientException for 400-level errors
                    // GuzzleHttp\Exception\ServerException for 500-level errors
                    // GuzzleHttp\Exception\BadResponseException for both (it's their superclass)

                    try {
                        $response = null;

                        if(true){
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
