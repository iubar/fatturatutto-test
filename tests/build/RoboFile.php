<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
require_once ('../../vendor/autoload.php');

class RoboFile extends \Robo\Tasks {

    private $climate = null;

    private $browser = null;

    private $selenium_server = null;

    private $selenium_path = null;
	
	private $start_selenium = true;

    public function start() {
        $this->climate = new League\CLImate\CLImate();
        echo "Iinitializing..." . PHP_EOL;
        $this->init();
        echo PHP_EOL;
        if($this->start_selenium){
            echo "Starting Selenium..." . PHP_EOL;
            $this->startSelenium();
            echo PHP_EOL;
        }
        // TODO: check if selenium is running, or fail
        echo "Running php unit tests..." . PHP_EOL;
        $this->runPhpunit();
        echo PHP_EOL;
        if($this->start_selenium){
            echo "Shutting down Selenium..." . PHP_EOL;
            $this->stopSelenium(); // TODO: verificare se necessario o se ci pensa Robo
            echo PHP_EOL;
        }
        if ($this->browser != 'phantomjs') {
            echo "Running slideshow..." . PHP_EOL;
            $this->startHttpServer();
            $url = 'http://localhost:8000/slideshow/index.php';
            $this->browser($url);
            echo PHP_EOL;
            $input = $this->climate->password('Press Enter to stop the slideshow:');
            $dummy = $input->prompt();
        }
        echo "Done." . PHP_EOL;
    }

    private function stopSelenium() {
        $url = 'http://' . $this->selenium_server . '/selenium-server/driver/?cmd=shutDownSeleniumServer';
        $this->browser($url);
    }

    private function closeSeleniumSession() {
        $url = 'http://' . $this->selenium_server . '/selenium-server/driver/?cmd=shutDown';
        $this->browser($url);
    }

    private function isRelativePath($path) {
        $tmp = realpath($path);
        if (! $tmp) { // then it's a relative path
            return true;
        }
        return false;
    }

    private function init() {
        $ini_file = "config.ini";
        if (! is_file($ini_file)) {
            die("File not found: " . $ini_file . PHP_EOL);
        }
        $ini_array = parse_ini_file($ini_file);
        
        $this->start_selenium = $ini_array['start_selenium'];         
        
        $screenshots_path = $ini_array['screenshots_path'];
        $this->browser = $ini_array['browser'];
        $this->selenium_server = $ini_array['selenium_server'];
        $this->selenium_path = $ini_array['selenium_path'];
        
        putenv('BROWSER=' . $this->browser);
        putenv('SELENIUM_SERVER=' . $this->selenium_server);
        
        $ft_username = getenv('FT_USERNAME');
        if (! $ft_username) {
            $ft_username = $ini_array['ft_username'];
            putenv('FT_USERNAME=' . $ft_username);
        }
        
        $ft_password = null;
        if (! getenv('FT_PASSWORD')) {
            if (isset($ini_array['ft_password'])) {
                $ft_password = $ini_array['ft_password'];
            }
            if (! $ft_password) {
                $input = $this->climate->password('Please enter password for ' . $ft_username . ':');
                $ft_password = $input->prompt();
            }
            putenv('FT_PASSWORD=' . $ft_password);
        }
        
        if ($this->isRelativePath($this->selenium_path)) {
            $this->selenium_path = __DIR__ . $this->selenium_path;
        }
        if (! is_dir($this->selenium_path)) {
            die("Path not found: " . $this->selenium_path . PHP_EOL);
        }
        
        if ($this->isRelativePath($screenshots_path)) {
            $screenshots_path = __DIR__ . $screenshots_path;
        }
        if (! is_dir($screenshots_path)) {
            die("Path not found: " . $screenshots_path . PHP_EOL);
        }
        putenv('SCREENSHOTS_PATH=' . $screenshots_path);
        
        echo "SELENIUM SERVER: " . $this->formatEnv(getenv("SELENIUM_SERVER")) . PHP_EOL;
        echo "SELENIUM PATH: " . $this->selenium_path . PHP_EOL;
        
        echo "BROWSER:  " . $this->formatEnv(getenv("BROWSER")) . PHP_EOL;
        echo "SCREENSHOTS_PATH: " . $this->formatEnv(getenv("SCREENSHOTS_PATH")) . PHP_EOL;
        echo "FT_USERNAME: " . $this->formatEnv(getenv("FT_USERNAME")) . PHP_EOL;
        echo "FT_PASSWORD: " . $this->formatPassword(getenv("FT_PASSWORD")) . PHP_EOL;
        
        echo "TRAVIS_JOB_NUMBER: " . $this->formatEnv(getenv("TRAVIS_JOB_NUMBER")) . PHP_EOL;
        echo "TRAVIS: " . $this->formatEnv(getenv("TRAVIS")) . PHP_EOL;
        echo "SAUCE_USERNAME: " . $this->formatEnv(getenv("SAUCE_USERNAME")) . PHP_EOL;
        echo "SAUCE_ACCESS_KEY: " . $this->formatPassword(getenv("SAUCE_ACCESS_KEY")) . PHP_EOL;
        
        echo PHP_EOL . PHP_EOL;
    }

    private function formatPassword($env) {
        $str = '<not set>';
        if ($env) {
            $str = '**********';
        }
        return $str;
    }

    private function formatEnv($env) {
        return $env;
    }

    private function startSelenium() {
        $cmd = null;
        
        $selenium_path = $this->selenium_path;   
        
        // TODO: se non trovo selenium-server.jar o i drivers (!!!), allora il test deve fallire
        
        $cmmd_prefix = "java -jar $selenium_path/selenium-server\selenium-server-standalone.jar";
        switch ($this->browser) {
            case 'chrome':
                $cmd = $cmmd_prefix . " -Dwebdriver.chrome.driver=" . "$selenium_path/drivers/chrome/chromedriver.exe";
                break;
            case 'marionette':
                //$cmd = $cmmd_prefix . " -Dwebdriver.gecko.driver=" . "$selenium_path/drivers/marionette/wires-0.6.2.exe" . " -Dwebdriver.firefox.bin=" . "\"C:/Program Files (x86)/Firefox Developer Edition/firefox.exe\"";
                $cmd = $cmmd_prefix . " -Dwebdriver.gecko.driver=" . "$selenium_path/drivers/marionette/wires-0.6.2.exe";
                break;
            case 'firefox':
                $cmd = $cmmd_prefix . "";
                break;
            case 'phantomjs':
                $cmd = $cmmd_prefix . " -Dphantomjs.ghostdriver.cli.args=[\"--loglevel=DEBUG\"] -Dphantomjs.binary.path=" . "$selenium_path/phantomjs-2.1.1-windows\bin\phantomjs.exe";
                break;
            case 'all':
                $cmd = $cmmd_prefix . " -Dwebdriver.chrome.driver=" . "$selenium_path/drivers/chrome/chromedriver.exe" . " -Dwebdriver.gecko.driver=" . "$selenium_path/drivers/wires-0.6.2.exe" . " -Dphantomjs.binary.path=" . "$selenium_path/phantomjs-2.1.1-windows\bin\phantomjs.exe";
                break;
            default:
                die("Browser '" . $this->browser . "' not supported" . PHP_EOL);
        }
        
        if ($cmd) {
            // launches Selenium server
            $this->taskExec($cmd)
                ->background()
                ->run();
		
		}
        
    }
    
    protected function isFile(){
        
    }

    private function startHttpServer() {
        $dir = __DIR__ . "/../../logs/screenshots";
        if (! is_dir($dir)) {
            die("Path not found: " . $dir . PHP_EOL);
        }
        // starts PHP server
        $this->taskServer(8000)
        ->dir($dir)    
        // ->host('0.0.0.0')                
        ->background()  // execute server in background
        ->run();
    }

    private function browser($url) {
        // TODO: valutare se è meglio avviare il browser $this->browser pittuosto che quello di default di sistema
        $this->taskOpenBrowser([$url])->run();
    }

    private function runPhpunit() {
        $cfg_file = __DIR__ . "\..\..\phpunit.xml";
        if (! is_file($cfg_file)) {
            die("File not found: " . $cfg_file . PHP_EOL);
        }
        // runs PHPUnit tests
        $this->taskPHPUnit('phpunit')
            ->configFile($cfg_file)           
            // ->bootstrap('test/bootstrap.php')
            ->run();
    }
}