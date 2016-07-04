<?php
namespace Fatturatutto\E2e;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Iubar\Tests\Web_TestCase;

/**
 * Test of www.fatturatutto.it website
 *
 * @author Matteo
 * @global env BROWSER
 * @global env SELENIUM_SERVER
 * @global env SELENIUM_PATH
 * @global env SCREENSHOTS_PATH
 * @global env APP_HOST
 * @global env APP_USERNAME
 * @global env APP_PASSWORD
 */
class FatturatuttoTest extends Web_TestCase {

    const EXAMPLE_FATTURA_URL = 'http://app.fatturatutto.it/public/resources/xml/1.1/examples/IT01234567890_11002.xml';

    const SITE_TITLE = "FatturaTutto.it";

    const APP_SITUAZIONE_TITLE = "Situazione";

    const APP_IMPORTAZIONE_TITLE = "Importazione";

    const APP_ELENCO_TITLE = "Elenco";

    const APP_MODELLI_TITLE = "Modelli";

    const LOGIN_TITLE = "Login";

    const ROUTE_LOGIN = "login";
    
    const ROUTE_LOGOUT = "logout";

    const ROUTE_SITUAZIONE = "situazione";

    const ROUTE_STRUMENTI_IMPORTAZIONE = "strumenti/importazione";

    const ROUTE_ELENCO_FATTURE = "elenco-fatture";

    const ROUTE_MODELLI_FATTURA = "modelli-fattura";

    const DIALOG_WELCOME_MSG = "Benvenuto su FatturaTutto";

    const LOGIN_ERR_MSG = "Email o password errati";
    
    // the title (key) and the id (value) of each elem of the aside navigation bar
    private static $navigation_bar_elem_id = array(
        'Situazione' => 'menu-situazione',
        'Anagrafica' => 'menu-anagrafica',
        'Clienti' => 'menu-clienti',
        'Articoli - servizi' => 'menu-articoli-servizi',
        'Fatture' => 'menu-fatture',
        'Modelli' => 'menu-modelli',
        'Strumenti' => 'menu-strumenti',
        'Impostazioni' => 'menu-impostazioni'
    );

    /**
     * SiteHome and AppHome test
     */
    public function testSiteHome() {
        $wd = $this->getWd();
           
        $wd->get($this->getSiteHome() . '/'); // Navigate to SITE_HOME
        $this->waitSiteHome();
        
        // SITE HOME
        $this->check_webpage($this->getSiteHome() . '/', self::SITE_TITLE);
        
        // select button 'Inizia'
        $inizia_button_path = '//*[@id="slider"]/div/div[1]/div/a/p';
        $this->waitForXpath($inizia_button_path); // Wait until the element is visible
        $start_button = $wd->findElement(WebDriverBy::xpath($inizia_button_path)); // Button "Inizia"
                                                                                   
        // FIXME: probabile bug di marionette nell'identificarte l'elemento precedente con il metodo findElement() (vedi: https://github.com/seleniumhq/selenium/issues/1202)
        $start_button->click();
    }

    /**
     * Login test with wrong and real params
     */
    public function testLogin() {
        $wd = $this->getWd();
        
        // Se ils eguente metodo non funziona:
        // Verificare le opzioni seguenti per singolo browser        
        // Safari : @config[:capabilities]['safari.options'] = { cleanSession: true }
        // IE : @config[:capabilities]['ie.ensureCleanSession'] = true
        // Firefox: @config[:capabilities][:profile] = Selenium::WebDriver::Firefox::Profile.new
        // Edge: @config[:capabilities]['ensureCleanSession'] = true
        
        if (self::$browser != self::SAFARI) {
            $wd->manage()->deleteAllCookies();  // TODFO: da riprovare con SAFARI 
        }else{        
            // $this->deleteAllCookies(); // TODO: testare se con Chrome il metodo deleteAllCookies() funziona
            // oppure
            $url = $this->getAppHome() . '/' . self::ROUTE_LOGOUT;
            $wd->get($url); // Navigate to ROUTE_LOGOUT
            $wd->manage()->timeouts()->implicitlyWait(3);
        }
                
        $url = $this->getAppHome() . '/' . self::ROUTE_LOGIN;
        $wd->get($url); // Navigate to ROUTE_LOGIN
        
        // Poichè ho preventivamente cancellato tutti i cookies sono sicuro che l'url precedente mi indiriizzerà direttamente alla form di login senza alcun redirect
        $this->waitLoginForm(); // oppure $wd->manage()->timeouts()->implicitlyWait(1);
        
        $current_url = $wd->getCurrentURL();
        $this->assertEquals($url, $current_url);
        
        // 1) Wrong login
        $user = 'utente@inesistente';
        $this->login($user, $user);
 
        // Verify the error msg show
        // $login_error_msg = '/html/body/div[1]/div[1]/div/div/div[3]/div[1]';
        // $this->waitForXpath($login_error_msg); // Wait until the element is visible
        // $incorrectData = $wd->findElement(WebDriverBy::xpath($login_error_msg)); // Text "Email o password errati"
        $login_error_class = 'text-danger';
        $this->waitForClassName($login_error_class);
        $incorrectData = $wd->findElement(WebDriverBy::className($login_error_class)); // Find the first element matching the class name argument.
        $this->assertNotNull($incorrectData);
        $this->assertContains(self::LOGIN_ERR_MSG, $incorrectData->getText());
 
        
        // 2) Real login
        
        // checking that we are in the right page
        $this->check_webpage($this->getAppHome() . '/' . self::ROUTE_LOGIN, self::LOGIN_TITLE);
        
        $this->do_login();
        
        // Verify to be enter and that welcome msg is show
        $welcome_msg = '//*[@id="ngdialog1"]/div[2]/div/div[1]'; // dialog compile your data
                                                                 
        // if you have never compile your data this function do it for you
        if (!isset($welcome_msg)) {
            $this->compile_dialog();
        }
    }

    /**
     * Test the aside navigation bar
     */
    public function testAsideNavigationBar() {
        $wd = $this->getWd();
        
        $this->do_login();
        
        // checking that we are in the right page
        $this->check_webpage($this->getAppHome() . '/' . self::ROUTE_SITUAZIONE, self::APP_SITUAZIONE_TITLE);
        
        // checking that all the section of the navigation bar are ok
        foreach (self::$navigation_bar_elem_id as $key => $value) {
            $this->check_nav_bar($value, $key);
        }
    }

    /**
     * Test 'impostazioni' section in the aside navigation bar
     */
    public function testImpostazioni() {
        $wd = $this->getWd();
        
        $this->do_login(); 
        // checking that we are in the right page
        $this->check_webpage($this->getAppHome() . '/' . self::ROUTE_SITUAZIONE, self::APP_SITUAZIONE_TITLE);
        
        $impostazioni_id = self::$navigation_bar_elem_id['Impostazioni'];
        $this->waitForId($impostazioni_id); // Wait until the element is visible
        $impostazioni_button = $wd->findElement(WebDriverBy::id($impostazioni_id)); // aside 'impostazioni' button
        $this->assertNotNull($impostazioni_button);
        
        echo "Waiting to be clickable: " . $impostazioni_id . PHP_EOL;
        $wait = new WebDriverWait($wd, 2);
        $wait->until(WebDriverExpectedCondition::elementToBeClickable($impostazioni_button));
        
        $impostazioni_button->click();
        
        $imp_generali_path = '//*[@id="menu-impostazioni"]/ul/li[1]/a';
        
        $url = $wd->getCurrentURL();
        $title = $wd->getTitle();
        echo "Current url: " . $url . " Page title: " . $title . PHP_EOL;        
        echo "I'm waiting for the xpath: " . $imp_generali_path . PHP_EOL;
        
        // FIXME: probabile bug di phantomjs nell'eseguire il codice seguente (vedi: http://superuser.com/questions/855710/selenium-with-phantomjs-click-not-working)
        // TODO: da riprovare con PHANTOMJS perchè sono state fatte modifiche migliorative al codice
        if (self::$browser != self::PHANTOMJS) {            
            $this->waitForXpath($imp_generali_path); // Wait until the element is visible
            $imp_generali = $wd->findElement(WebDriverBy::xpath($imp_generali_path)); // aside 'impostazioni->generale' button
            $this->assertNotNull($imp_generali);
            
            echo "Waiting to be clickable: " . $imp_generali_path . PHP_EOL;            
            $wait = new WebDriverWait($wd, 2);
            $wait->until(WebDriverExpectedCondition::elementToBeClickable($imp_generali));
            
            $imp_generali->click();
        }
    }

    /**
     * Try to import an invoice in ROUTE_STRUMENTI_IMPORTAZIONE
     */
    public function testImportazioneFattura() {
        $wd = $this->getWd();
        
        $this->do_login();
        $excpected_url = $this->getAppHome() . '/' . self::ROUTE_STRUMENTI_IMPORTAZIONE;
        $wd->get($excpected_url); // Navigate to ROUTE_STRUMENTI_IMPORTAZIONE
        $this->check_webpage($excpected_url);
        $this->waitStrumentiImportazione();        
        
        // checking that we are in the right page
        $this->check_webpage($this->getAppHome() . '/' . self::ROUTE_STRUMENTI_IMPORTAZIONE, self::APP_IMPORTAZIONE_TITLE);
        
        $import_box_path = '//*[@id="import-box"]/div[1]';
        $drop_area = $wd->findElement(WebDriverBy::xpath($import_box_path)); // the 'import-box' area of the invoice
        $this->assertNotNull($drop_area);
        
        $this->clearBrowserConsole(); // clean the browser console log
                                      
        // take an invoice.xml from the webpage EXAMPLE_FATTURA_URL
        $data = file_get_contents(self::EXAMPLE_FATTURA_URL);
        if (!is_string($data)) {
            $this->fail("Can't read the invoice: " . self::EXAMPLE_FATTURA_URL);
        }
        $tmp_file = $this->getTmpDir() . DIRECTORY_SEPARATOR . 'esempio_fattura.xml';
        file_put_contents($tmp_file, $data);
        
        self::$files_to_del[] = $tmp_file;
        
        // execute the js script to upload the invoice
        $this->dragfileToUpload($drop_area, $tmp_file);
        
        // click on 'avanti'
        $avanti_button = '//*[@id="fatture"]/div[2]/button';
        $this->waitForXpath($avanti_button); // Wait until the element is visible
        $button = $wd->findElement(WebDriverBy::xpath($avanti_button)); // button 'avanti'
        $this->assertNotNull($button);
        $button->click();

        // wait for elenco-fatture page is ready
        $this->waitForTagWithText("h2", self::APP_ELENCO_TITLE); // Wait until the element is visible
        $title = $wd->findElement(WebDriverBy::tagName("h2")); // the tag h2 'Elenco fatture'
        $this->assertContains(self::APP_ELENCO_TITLE, $title->getText());
        
        $this->assertNoErrorsOnConsole();
    }

    /**
     * Test the read of the console in ROUTE_MODELLI_FATTURA
     */
    public function testConsole() {
        //  'marionette'
        if (self::$browser != self::MARIONETTE) {  // FIXME: codice non comptibile con 'marionette' (can't read the console)
            $wd = $this->getWd();
            
            $this->do_login();
            $this->clearBrowserConsole(); // clean the browser console log
            
            $wd->get($this->getAppHome() . '/' . self::ROUTE_MODELLI_FATTURA);
            $this->waitModelliFattura();
            
            // checking that we are in the right page
            $this->check_webpage($this->getAppHome() . '/' . self::ROUTE_MODELLI_FATTURA, self::APP_MODELLI_TITLE);
            
            // FIXME: chrome has 1 error, for more info see the cosole.json in logs folder
            if (self::$browser == self::CHROME) {
                $this->assertErrorsOnConsole();
            } else {
                $this->assertNoErrorsOnConsole();
            }
        }
    }

    public function testFinish() {
        self::$climate->info('FINE TEST FATTURATUTTO WEBDRIVER OK!!!!!!!!');
    }

    /**
     * Call the login() function with the global params username and password
     */
    private function do_login($right_account=true) {
        $user = self::$app_username;
        $password = self::$app_password;
        $this->login($user, $password);                
        
        if($right_account){
            $wd = $this->getWd();
            $url = $wd->getCurrentURL();
            $title = $wd->getTitle();            
            $impostazioni_id = 'menu-impostazioni';
            echo "Current url: " . $url . " Page title: " . $title . PHP_EOL;
            echo "I'm waiting for the id: " . $impostazioni_id . PHP_EOL;            
            
            // DOESN'T WORK: $this->waitForId($impostazioni_id); // Wait until the element is visible            
            $wd->manage()->timeouts()->implicitlyWait(3);
        }
    }

    /**
     * Compile login fields and try to enter using the email address
     *
     * @param string $user the email address of the user
     * @param string $password the password of the user
     */
    private function login($user, $password) {
        $wd = $this->getWd();
        $login_url = $this->getAppHome() . '/' . self::ROUTE_LOGIN;
        $wd->get($login_url); // Navigate to ROUTE_LOGIN
                              
        // Implicit waits: I don't know which page it is. If user is already logged-in, the browser is automatically redirected 
        $wd->manage()->timeouts()->implicitlyWait(4);
        
        $current_url = $wd->getCurrentURL();
        
        // if I'm not already log-in do the login
        if ($current_url == $login_url) {
            // select email method to enter
            $email_button_path = '/html/body/div[1]/div[1]/div/div/div[2]/div[2]/button';
            $this->waitForXpath($email_button_path); // Wait until the element is visible
            $email_enter = $wd->findElement(WebDriverBy::xpath($email_button_path)); // Button "Email"
            $email_enter->click();
            
            // Write into email textfield
            $username_field_path = '/html/body/div[1]/div[1]/div/div/form/div[2]/input';
            $this->waitForXpath($username_field_path); // Wait until the element is visible
            $username_text_field = $wd->findElement(WebDriverBy::xpath($username_field_path)); // Field "Username"
            $username_text_field->sendKeys($user);
            
            // Write into password textfield
            $passwor_field_path = '/html/body/div[1]/div[1]/div/div/form/div[3]/input';
            $this->waitForXpath($passwor_field_path); // Wait until the element is visible
            $password_text_field = $wd->findElement(WebDriverBy::xpath($passwor_field_path)); // Field "Password"
            $password_text_field->sendKeys($password);
            
            // Click on 'Accedi' button
            $login_button_path = '/html/body/div[1]/div[1]/div/div/form/div[5]/button';
            $this->waitForXpath($login_button_path); // Wait until the element is visible
            $accedi_button = $wd->findElement(WebDriverBy::xpath($login_button_path)); // Button "Accedi"
            $accedi_button->click();
        }else{
            echo "You're already logged" . PHP_EOL;
        }
    }

    /**
     * Checking that the url and the title of the webpage are what i expected
     *
     * @param string $url the url of the webpage
     * @param string $title the title of the webpage
     */
    private function check_webpage($expected_url, $expected_title=null) {
        $wd = $this->getWd();
        $url = $wd->getCurrentURL();
        $this->assertEquals($expected_url, $url);
        if($expected_title){
            $title = $wd->getTitle();
            $this->assertContains($expected_title, $title);
        }
    }

    /**
     * Checking that every elem of the navigation bar is present
     *
     * @param string $id the id of the elem
     * @param string $expected_title the title of the elem
     */
    private function check_nav_bar($id, $expected_title) {
        $wd = $this->getWd();
        $this->waitForId($id); // Wait until the element is visible
        $elem = $wd->findElement(WebDriverBy::id($id));
        $this->assertNotNull($elem);
        $text = $elem->getText();
        if (self::$browser == self::PHANTOMJS) {
            $text = $elem->getAttribute("innerText");
        }
        $this->assertContains($expected_title, $text);
    }

    /**
     * Compile the dialog 'configurazione iniziale' with random data
     */
    private function compile_dialog() {
        $wd = $this->getWd();
        $avanti_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[1]/div/button';
        $this->waitForXpath($avanti_button_path); // avanti
        $avanti_button = $wd->findElement(WebDriverBy::xpath($avanti_button_path)); // Button "Avanti"
        $avanti_button->click();
        
        $avvocato_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[2]/div[1]/div[2]/div[1]/button'; // Avvocato
        $this->waitForXpath($avvocato_button_path); // Avvocato
        $avvocato_button = $wd->findElement(WebDriverBy::xpath($avvocato_button_path)); // Button "Avvocato"
        $avvocato_button->click();
        
        $avanti_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[2]/div[2]/button';
        $this->waitForXpath($avanti_button_path); // avanti
        $avanti_button = $wd->findElement(WebDriverBy::xpath($avanti_button_path)); // Button "Avanti"
        $avanti_button->click();
        
        $this->check_prova('denominazione', 'aaaaaaaaaaa');
        $this->check_prova('piva', '22222222222');
        $this->check_prova('cf', '1111111111111111');
        $this->check_prova('indirizzo', '11111');
        $this->check_prova('civico', '111');
        $this->check_prova('cap', '11111');
        $this->check_prova('provincia', 'Ancona');
        $this->check_prova('comune', 'Ancona');
        $this->check_prova('telefono', '111111');
        $this->check_prova('fax', '11111111111111');
        $this->check_prova('email', 'ppp@gma.it');
        
        $ordinario_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[3]/form/div[6]/div[2]/select';
        $this->waitForXpath($ordinario_button_path); // Ordinario
        $ordinario_button = $wd->findElement(WebDriverBy::xpath($ordinario_button_path)); // textfield "Ordinario"
        $ordinario_button->sendKeys('Ordinario');
        
        $avanti_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[3]/form/div[7]/button';
        $this->waitForXpath($avanti_button_path); // avanti
        $avanti_button = $wd->findElement(WebDriverBy::xpath($avanti_button_path)); // Button "Avanti"
        $avanti_button->click();
        
        $fine_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[4]/div[2]/button';
        $this->waitForXpath($fine_button_path); // fine
        $fine_button = $wd->findElement(WebDriverBy::xpath($fine_button_path)); // Button "fine"
        $fine_button->click();
    }

    /**
     * Write into the respective field
     *
     * @param string $id the id of the elem
     * @param string $sendKey what do you wanna write in the elem
     */
    private function check_prova($id, $sendKey) {
        $wd = $this->getWd();
        $this->waitForId($id); // Wait until the element is visible
        $elem = $wd->findElement(WebDriverBy::id($id));
        $elem->sendKeys($sendKey);
        $this->assertNotNull($elem);
        $this->assertContains($expected_title, $elem->getText());
    }

    /**
     * Unsed, explain only how to use cookies
     */
    private function playWithCookies() {
        $wd = $this->getWd();
        
        $wd->manage()->deleteAllCookies();
        $wd->manage()->addCookie(array(
            'name' => 'cookie_name',
            'value' => 'cookie_value'
        ));
        $cookies = $wd->manage()->getCookies();
        print_r($cookies);
    }

    /**
     * Return SiteHome (use http protocol)
     */
    private function getSiteHome() {
        return "http://www." . self::$app_host;
    }

    /**
     * Return AppHome (use https protocol)
     */
    private function getAppHome() {
        return "https://app." . self::$app_host;
    }

    /**
     * Take a temporary directory
     *
     * @return string the temporary directory
     */
    private function getTmpDir() {
        $tmp_dir = sys_get_temp_dir();
        if ($this->isTravis()) {
            $tmp_dir = __DIR__;
        }
        if (!is_writable($tmp_dir)) {
            $this->fail("Temp dir not writable: " . $tmp_dir);
        }
        return $tmp_dir;
    }

    /**
     * Wait for an elem in page SiteHome
     */
    private function waitSiteHome() {
        // $inizia_button_path = '//*[@id="slider"]/div/div[1]/div/a/p';
        // $this->waitForXpath($inizia_button_path); // Wait until the element is visible
        $tag = "h1";
        $substr = "fattura elettronica";
        $this->waitForTagWithText($tag, $substr);
    }

    /**
     * Wait for an elem in page ROUTE_LOGIN
     */
    private function waitLoginForm() {
        // $email_button_path = '/html/body/div[1]/div[1]/div/div/div[2]/div[2]/button';
        // $this->waitForXpath($email_button_path); // Wait until the element is visible
        
        // $tag = "button";
        // $substr = "email";
        // $this->waitForTagWithText($tag, $substr);
        $this->waitForClassName("login-box");
    }

    /**
     * Wait for an elem in page ROUTE_STRUMENTI_IMPORTAZIONE
     */
    private function waitStrumentiImportazione() {
        $import_box_id = 'import-box';
        $this->waitForId($import_box_id); // Wait until the element is visible
    }

    /**
     * Wait for an elem in page ROUTE_MODELLI_FATTURA
     */
    private function waitModelliFattura() {
        // $aggiungi_button_path = '/html/body/div[1]/div/section/div/div/div[2]/button'; ?
        // $this->waitForXpath($aggiungi_button_path); // Wait until the element is visible
        $tag = "h2";
        $substr = "Modelli fattura";
        $this->waitForTagWithText($tag, $substr);
    }
}
