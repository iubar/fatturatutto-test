<?php
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\WebDriverException;

/**
 * PHPUnit_Framework_TestCase Develop
 *
 * @author Matteo
 *        
 */
class E2eRoot extends PHPUnit_Framework_TestCase {

    const TAKE_A_SCREENSHOT = true;

    const DEFAULT_WAIT_TIMEOUT = 15;

    const DEFAULT_WAIT_INTERVAL = 1000;

    const SCREENS_PATH = './logs/screenshots/';

    const BROWSER = 'chrome';

    /**
     *
     * @var \RemoteWebDriver
     */
    protected static $webDriver;

    /**
     * Start WebDriver
     */
    public static function setUpBeforeClass() {
        if (self::TAKE_A_SCREENSHOT) {
            if (! is_writable(self::SCREENS_PATH)) {
                die("ERRORE. percorso non scrivibile: " . self::SCREENS_PATH . PHP_EOL);
            }
        }
        $capabilities = array(
            WebDriverCapabilityType::BROWSER_NAME => self::BROWSER
        );
        self::$webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities); // This is the default
    }

    /**
     * Close WebDriver
     */
    public static function tearDownAfterClass() {
        self::closeAllWindows();
        self::$webDriver->quit();
    }

    /**
     * Take a screenshot of the webpage
     *
     * @param string $element the element to capture
     * @throws Exception if the folder where to save doesn't exist
     * @return string the screenshot
     */
    public function TakeScreenshot($element = null) {
        // The path where save the screenshot
        $screenshot = self::SCREENS_PATH . time() . ".png";
        
        $this->getWd()->takeScreenshot($screenshot);
        if (! file_exists($screenshot)) {
            throw new Exception('Could not save screenshot');
        }
        
        // To capture the full screen
        if (! (bool) $element) {
            return $screenshot;
        }
        
        // To capture an element
        $element_screenshot = self::SCREENS_PATH . time() . ".png";
        
        $element_width = $element->getSize()->getWidth();
        $element_height = $element->getSize()->getHeight();
        
        $element_src_x = $element->getLocation()->getX();
        $element_src_y = $element->getLocation()->getY();
        
        // Create image instances
        $src = imagecreatefrompng($screenshot);
        $dest = imagecreatetruecolor($element_width, $element_height);
        
        // Copy
        imagecopy($dest, $src, 0, 0, $element_src_x, $element_src_y, $element_width, $element_height);
        
        imagepng($dest, $element_screenshot);
        
        // unlink($screenshot); // unlink function might be restricted in mac os x.
        
        if (! file_exists($element_screenshot)) {
            throw new Exception('Could not save element screenshot');
        }
        
        return $element_screenshot;
    }

    /**
     * Return the WebDriver
     *
     * @return RemoteWebDriver
     */
    protected function getWd() {
        return self::$webDriver;
    }

    /**
     * Click on a element that has the corresponding xpath and wait some time if the element is not immediately present
     *
     * @param string $xpath the xpath of the element
     * @param real $wait the time to wait
     */
    protected function click($xpath, $wait = 0.50) {
        $this->getWd()
            ->findElement(WebDriverBy::xpath($xpath))
            ->click();
        
        $this->getWd()
            ->manage()
            ->timeouts()
            ->implicitlyWait($wait);
    }

    /**
     * Wait at most $timeout seconds until at least one result is shown
     *
     * @param string $id the id of the element
     * @param int $timeout the timeout
     */
    protected function waitForId($id, $timeout = self::DEFAULT_WAIT_TIMEOUT) {
        $this->getWd()
            ->wait($timeout, self::DEFAULT_WAIT_INTERVAL)
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id($id)));
    }

    /**
     * Wait at most $timeout seconds until at least one result is shown
     *
     * @param string $xpath the xpath of the element
     * @param int $timeout the timeout
     */
    protected function waitForXpath($xpath, $timeout = self::DEFAULT_WAIT_TIMEOUT) {
        $this->getWd()
            ->wait($timeout, self::DEFAULT_WAIT_INTERVAL)
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath($xpath)));
    }

    /**
     * Wait at most $timeout seconds until at least one result is shown
     *
     * @param int $timeout the timeout
     */
    protected function waitForEmailLink($timeout = self::DEFAULT_WAIT_TIMEOUT) {
        $partial_link_text = "mailto:";
        $this->getWd()
            ->wait($timeout, self::DEFAULT_WAIT_INTERVAL)
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::partialLinkText($partial_link_text)));
    }

    /**
     * Assert that an element was not found
     *
     * @param string $by the element
     */
    protected function assertElementNotFound($by) {
        $els = $this->getWd()->findElements($by);
        if (count($els)) {
            $this->fail("Unexpectedly element was found");
        }
        $this->assertTrue(true);
    }

    /**
     * Wait for user input
     */
    protected function waitForUserInput() {
        if (trim(fgets(fopen("php://stdin", "r"))) != chr(13))
            return;
    }

    /**
     * Close all windows
     */
    protected static function closeAllWindows() {
        $wd = self::$webDriver;
        $handlers = $wd->getWindowHandles();
        foreach ($handlers as $handler) {
            $wd->switchTo()->window($handler);
            $wd->close();
        }
    }

    /**
     * Handle the PHPUnit_Framework_ExpectationFailedException taking a screenshot
     *
     * @param \PHPUnit_Framework_ExpectationFailedException $e the exception
     * @throws \PHPUnit_Framework_ExpectationFailedException raise the exception
     */
    protected function handleAssertionException(\PHPUnit_Framework_ExpectationFailedException $e) {
        echo PHP_EOL;
        if (self::TAKE_A_SCREENSHOT) {
            echo "Assertion failed: taking a screen shot..." . PHP_EOL;
            $this->TakeScreenshot();
        }
        throw new \PHPUnit_Framework_ExpectationFailedException($e);
    }

    /**
     * Handle the WebDriverException taking a screenshot
     *
     * @param WebDriverException $e the exception
     */
    protected function handleWebdriverException(WebDriverException $e) {
        if (self::TAKE_A_SCREENSHOT) {
            echo "Assertion failed: taking a screen shot..." . PHP_EOL;
            $this->TakeScreenshot();
        }
    }
}