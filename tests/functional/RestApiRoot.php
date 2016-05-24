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

    protected function handleAssertionException(\Exception $e) {
        echo PHP_EOL . "Assertion failed" . PHP_EOL;
    }
    
    // TODO classe padre
    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param Exception|Throwable $e
     *
     * @throws Exception|Throwable
     */
    public function onNotSuccessfulTest(\Exception $e) {
        $this->handleAssertionException($e);
        parent::onNotSuccessfulTest($e); // rilancia un eccezione del tipo PHPUnit_Framework_ExpectationFailedException
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