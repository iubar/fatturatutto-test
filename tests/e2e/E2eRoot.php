<?php
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 * PHPUnit_Framework_TestCase Develop
 *
 * @author Matteo
 *        
 */
class E2eRoot extends TestPhpUnit {

    const TAKE_A_SCREENSHOT = true;
    
    // seconds
    const DEFAULT_WAIT_TIMEOUT = 15;
    
    // milliseconds
    const DEFAULT_WAIT_INTERVAL = 1000;

    const PHANTOMJS = 'phantomjs';

    const CHROME = 'chrome';

    const MARIONETTE = 'marionette';

    const SELENIUM_SHUTDOWN_URL = 'http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer';

    protected static $screenshots = array();

    protected static $webDriver;

    /**
     * Start the WebDriver
     *
     * @throws \InvalidArgumentException if a wrong browser is given
     */
    public static function setUpBeforeClass() {
        // check if you can take screenshots and path exist
        if (self::TAKE_A_SCREENSHOT) {
            if (! is_writable(SCREENSHOTS_PATH)) {
                die("ERRORE. percorso non scrivibile: " . SCREENSHOTS_PATH . PHP_EOL);
            }
        }
        
        // set capabilities according to the browers
        switch (BROWSER) {
            case self::PHANTOMJS:
                $capabilities = DesiredCapabilities::phantomjs();
                break;
            case self::CHROME:
                $capabilities = DesiredCapabilities::chrome();
                break;
            case self::MARIONETTE:
                $capabilities = DesiredCapabilities::firefox();
                $capabilities->setCapability(self::MARIONETTE, true);
                break;
            default:
                throw new \InvalidArgumentException("parametro " . BROWSER . " non previsto");
        }
        
        // create the WebDriver
        self::$webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities); // This is the default
    }

    /**
     * Close the WebDriver and show the screenshot in the browser if there is
     */
    public static function tearDownAfterClass() {
        self::closeAllWindows();
        self::$webDriver->quit();
        
        // if there is at least a screenshot show it in the browser
        if (count(self::$screenshots) > 0) {
            echo "Screnshots taken: " . PHP_EOL;
            foreach (self::$screenshots as $screenshot) {
                echo "\t" . $screenshot . PHP_EOL;
            }
            $first_screenshot = self::$screenshots[0];
            $this->startShell("start chrome " . $first_screenshot);
        }
    }

    /**
     * Take a screenshot of the webpage
     *
     * @param string $element the element to capture
     * @throws Exception if the screenshot doesn't exist
     * @return string the screenshot
     */
    public function takeScreenshot($element = null) {
        // The path where save the screenshot
        $screenshot = SCREENSHOTS_PATH . time() . ".png";
        
        $this->getWd()->takeScreenshot($screenshot);
        
        if (! file_exists($screenshot)) {
            throw new Exception('Could not save screenshot: ' . $screenshot);
        }
        
        if ($element) {
            
            $element_width = $element->getSize()->getWidth();
            $element_height = $element->getSize()->getHeight();
            
            $element_src_x = $element->getLocation()->getX();
            $element_src_y = $element->getLocation()->getY();
            
            // Create image instances
            $src = imagecreatefrompng($screenshot);
            $dest = imagecreatetruecolor($element_width, $element_height);
            
            // Copy
            imagecopy($dest, $src, 0, 0, $element_src_x, $element_src_y, $element_width, $element_height);
            
            imagepng($dest, $screenshot); // overwrite the full screenshot
            
            if (! file_exists($screenshot)) {
                throw new Exception('Could not save the cropped screenshot' . $screenshot);
            }
        }
        
        $screenshots[] = $screenshot;
        
        return $screenshot;
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
     * Make a screenshot if the assertion fail
     *
     * @param \Exception $e the exception
     */
    protected function handleAssertionException(\Exception $e) {
        echo PHP_EOL;
        if (self::TAKE_A_SCREENSHOT) {
            echo "Assertion failed: taking a screen shot..." . PHP_EOL;
            $this->takeScreenshot();
        }
    }

    /**
     * Handle the WebDriverException taking a screenshot
     *
     * @param WebDriverException $e the exception
     */
    protected function handleWebdriverException(WebDriverException $e) {
        if (self::TAKE_A_SCREENSHOT) {
            echo "Assertion failed: taking a screen shot..." . PHP_EOL;
            $this->takeScreenshot();
        }
    }

    /**
     * Shutdown Selenium Server
     */
    protected function quitselenium() {
        $this->startShell("start " . self::CHROME . self::SELENIUM_SHUTDOWN_URL);
    }

    /**
     * Start a shell and execute the command
     *
     * @param string $cmd the command to execute
     */
    private function startShell($cmd) {
        $this->shell_exec($cmd);
    }
}