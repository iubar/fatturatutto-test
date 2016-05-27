<?php
namespace Iubar;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use League\CLImate\CLImate;

/**
 * PHPUnit_Framework_TestCase Develop
 *
 * @author Matteo
 *        
 * @see : https://gist.github.com/huangzhichong/3284966 Cheat sheet for using php webdriver
 * @see : https://gist.github.com/aczietlow/7c4834f79a7afd920d8f Cheat sheet for using php webdriver
 *     
 */
class E2e extends TestPhpUnit {

    const TAKE_A_SCREENSHOT = true;
    
    // seconds
    const DEFAULT_WAIT_TIMEOUT = 15;
    
    // milliseconds
    const DEFAULT_WAIT_INTERVAL = 1000;
    
    // Browser
    const PHANTOMJS = 'phantomjs';

    const CHROME = 'chrome';

    const FIREFOX = 'firefox';

    const MARIONETTE = 'marionette';

    const START = 'start';

    protected static $screenshots = array();

    protected static $webDriver;

    protected static $selenium_shutdown;

    /**
     * Start the WebDriver
     *
     * @global string BROWSER
     * @global string TRAVIS
     *        
     */
    public static function setUpBeforeClass() {
        
        // Usage with SauceLabs:
        // set on Travis: SAUCE_USERNAME and SAUCE_ACCESS_KEY
        // set on .tavis.yml and env.bat: SERVER (hostname + port, without protocol);
        
        // check if you can take screenshots and path exist
        if (self::TAKE_A_SCREENSHOT) {
            $screenshots_path = getEnv('SCREENSHOTS_PATH');
            if ($screenshots_path && ! is_writable($screenshots_path)) {
                die("ERRORE percorso non scrivibile: " . $screenshots_path . PHP_EOL);
            }
        }
        
        $capabilities = null;
        
        // set capabilities according to the browers
        switch (getEnv('BROWSER')) {
            case self::PHANTOMJS:
                echo "Inizializing PhantomJs browser" . PHP_EOL;
                $capabilities = DesiredCapabilities::phantomjs();
                break;
            case self::CHROME:
                echo "Inizializing Chrome browser" . PHP_EOL;
                $capabilities = DesiredCapabilities::chrome();
                break;
            case self::FIREFOX:
                echo "Inizializing Firefox browser" . PHP_EOL;
                $capabilities = DesiredCapabilities::firefox();
                break;
            case self::MARIONETTE:
                echo "Inizializing Marionette browser" . PHP_EOL;
                $capabilities = DesiredCapabilities::firefox();
                $capabilities->setCapability(self::MARIONETTE, true);
                break;
            default:
                $error = "Browser '" . getEnv('BROWSER') . "' not supported.";
                die("ERROR: " . $error . PHP_EOL);
        }
        
        // create the WebDriver
        $connection_timeout_in_ms = 10 * 1000; // Set the maximum time of a request
        $request_timeout_in_ms = 20 * 1000; // Set the maximum time of a request
        
        $server_root = null;
        if (getEnv('TRAVIS')) {
            echo "Travis detected..." . PHP_EOL;
            $capabilities->setCapability('tunnel-identifier', getEnv('TRAVIS_JOB_NUMBER'));
            $username = getEnv('SAUCE_USERNAME');
            $access_key = getEnv('SAUCE_ACCESS_KEY');
            $server_root = "http://" . $username . ":" . $access_key . "@" . getEnv('SERVER');
        } else {
            $server_root = "http://" . getEnv('SERVER');
        }
        self::$selenium_shutdown = $server_root . '/selenium-server/driver/?cmd=shutDownSeleniumServer';
        $server = $server_root . "/wd/hub";
        echo "Server: " . $server . PHP_EOL;
        
        try {
            self::$webDriver = RemoteWebDriver::create($server, $capabilities, $connection_timeout_in_ms, $request_timeout_in_ms); // This is the default
        } catch (\Exception $e) {
            $error = "Exception: " . $e->getMessage();
            die($error . PHP_EOL);
        }
        
        // set some timeouts
        self::$webDriver->manage()
            ->timeouts()
            ->pageLoadTimeout(60); // Set the amount of time (in seconds) to wait for a page load to complete before throwing an error
        self::$webDriver->manage()
            ->timeouts()
            ->setScriptTimeout(240); // Set the amount of time (in seconds) to wait for an asynchronous script to finish execution before throwing an error.
                                         
        // Window size
                                         // self::$webDriver->manage()->window()->maximize();
                                         // $window = new WebDriverDimension(1024, 768);
                                         // $this->webDriver->manage()->window()->setSize($window)
    }

    /**
     * Close the WebDriver and show the screenshot in the browser if there is
     */
    public static function tearDownAfterClass() {
        self::closeAllWindows();
        self::$webDriver->quit();
        
        // if there is at least a screenshot show it in the browser
        if (count(self::$screenshots) > 0) {
            echo "Taken " . count(self::$screenshots) . " screenshots" . PHP_EOL;
            $first_screenshot = self::$screenshots[0];
            self::startShell(self::START . " " . self::CHROME . " " . $first_screenshot);
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
        $screenshots_path = getEnv('SCREENSHOTS_PATH');
        if ($screenshots_path) {
            // The path where save the screenshot
            $screenshot = $screenshots_path . time() . ".png";
            
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
            
            self::$screenshots[] = $screenshot;
        }
    }

    /**
     * This method is called when a test method did not execute successfully
     *
     * @param Exception|Throwable $e the exception
     *       
     * @throws Exception|Throwable throws a PHPUnit_Framework_ExpectationFailedException
     */
    public function onNotSuccessfulTest(\Exception $e) {
        $msg = $this->formatErrorMsg($e);
        echo PHP_EOL;
        $climate = new CLImate();
        $climate->to('out')->red("EXCEPTION: " . $msg);
        
        if (self::TAKE_A_SCREENSHOT) {
            echo "Taking a screenshot..." . PHP_EOL;
            $this->takeScreenshot();
        }
        parent::onNotSuccessfulTest($e);
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

    protected function waitForTag($tag, $timeout = self::DEFAULT_WAIT_TIMEOUT, $interval = self::DEFAULT_WAIT_INTERVAL) {
        $this->getWd()
            ->wait($timeout, $interval)
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName($tag)));
    }

    protected function waitForCss($css, $timeout = self::DEFAULT_WAIT_TIMEOUT, $interval = self::DEFAULT_WAIT_INTERVAL) {
        $this->getWd()
            ->wait($timeout, $interval)
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector($css)));
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
     * waitForAjax : wait for all ajax request to close
     *
     * @param integer $timeout timeout in seconds
     * @param integer $interval interval in miliseconds
     * @return void
     */
    protected function waitForAjax($timeout = self::DEFAULT_WAIT_TIMEOUT, $interval = self::DEFAULT_WAIT_INTERVAL) {
        $this->getWd()
            ->wait($timeout, $interval)
            ->until(function () {
            // jQuery: "jQuery.active" or $.active
            // Prototype: "Ajax.activeRequestCount"
            // Dojo: "dojo.io.XMLHTTPTransport.inFlight.length"
            $condition = 'return ($.active == 0);';
            return $this->getWd()
                ->executeScript($condition);
        });
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
        if (trim(fgets(fopen("php://stdin", "r"))) != chr(13)) // chr(13) == "\n"
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
     * Shutdown Selenium Server
     *
     * Metodo non utilizzato. L'azione è delegata allo script che avvia il test.
     */
    protected function quitSelenium() {
        $this->startShell(self::START . " " . self::CHROME . " " . self::$selenium_shutdown);
    }

    /**
     * Start a shell and execute the command
     *
     * @param string $cmd the command to execute
     */
    protected static function startShell($cmd) {
        shell_exec($cmd);
    }

    /**
     * Colored the error msg in red
     *
     * @param string $e the error message
     * @return string the error message colored
     */
    private function formatErrorMsg($e) {
        $msg = $e->getMessage();
        $array = explode("\n", $msg);
        $msg = $array[0] . "...";
        return $msg;
    }
}
