<?php

class TestPhpUnit extends PHPUnit_Framework_TestCase {

    /**
     * This method is called when a test method did not execute successfully
     *
     * @param Exception|Throwable $e the exception
     *       
     * @throws Exception|Throwable throws a PHPUnit_Framework_ExpectationFailedException
     */
    public function onNotSuccessfulTest(\Exception $e) {
        $this->handleAssertionException($e);
        parent::onNotSuccessfulTest($e);
    }
}