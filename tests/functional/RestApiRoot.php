<?php
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * PHPUnit_Framework_TestCase Develop
 *
 * @author Matteo
 *        
 */
class RestApiRoot extends PHPUnit_Framework_TestCase {

    /**
     * Handle the PHPUnit_Framework_ExpectationFailedException by raise the exception
     *
     * @param \PHPUnit_Framework_ExpectationFailedException $e the exception
     * @throws \PHPUnit_Framework_ExpectationFailedException raise the exception
     */
    protected function handleAssertionException(\PHPUnit_Framework_ExpectationFailedException $e) {
        echo PHP_EOL . "Assertion failed" . PHP_EOL;
        throw new \PHPUnit_Framework_ExpectationFailedException($e);
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