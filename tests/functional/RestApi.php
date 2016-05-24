<?php
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

require_once ('..\TestPhpUnit.php');

/**
 * PHPUnit_Framework_TestCase Develop
 *
 * @author Matteo
 *        
 */
class RestApi extends TestPhpUnit {

    /**
     * Write that an exception fail
     *
     * @param \Exception $e
     */
    protected function handleAssertionException(\Exception $e) {
        echo PHP_EOL . "Assertion failed" . PHP_EOL;
    }

    /**
     * Handle the RequestException writing his msg
     *
     * @param RequestException $e the exception
     */
    protected function handleException(RequestException $e) {
        echo "REQUEST: " . Psr7\str($e->getRequest());
        echo "ECCEZIONE: " . $e->getMessage() . PHP_EOL;
    }
}