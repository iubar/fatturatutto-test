<?php
namespace Fatturatutto\E2e;

use Facebook\WebDriver\WebDriverBy;
use Iubar\Web_TestCase;

/**
 * Test of www.fatturatutto.it website
 *
 * @author Matteo
 *        
 * @global ....
 *        
 */
class FatturatuttoTest extends Web_TestCase {

    const SITE_HOME = "http://www.fatturatutto.it";

    const APP_HOME = "https://app.fatturatutto.it";

    const LOGIN_URL = "login";

    const APP_SITUAZIONE_URL = "situazione";

    const SITE_TITLE = "FatturaTutto.it";

    const APP_LOGIN = "Login";

    const APP_SITUAZIONE_TITLE = "Situazione";

    const ERR_DATI_MSG = "Email o password errati";

    const BENVENUTO_MSG = "Benvenuto su FatturaTutto";

    /**
     * SiteHome and AppHome test and click on button 'Inizia, � gratis'
     */
    public function testSiteHomeTitle() {
        $wd = $this->getWd();
        
        $this->do_login(); // Make the login
        $wd->get(self::SITE_HOME . '/'); // Navigate to SITE_HOME
                                         
        // SITE HOME
                                         
        // checking that we are in the right page
        $this->check_webpage(self::SITE_HOME . '/', self::SITE_TITLE);
        
        // select button 'Inizia' for php-webdriver
        $inizia_button_path = '//*[@id="slider"]/div/div[1]/div/a/p';
        $this->waitForXpath($inizia_button_path); // Wait until the element is visible
        $start_button = $wd->findElement(WebDriverBy::xpath($inizia_button_path)); // Button "Inizia"
                                                                                   
        // TODO: probabile bug di marionette nell'eseguire i click (vedi: https://github.com/seleniumhq/selenium/issues/1202)
        $start_button->click();
        
        // APP HOME
        
        // checking that we are in the right page
        $this->check_webpage(self::APP_HOME . '/' . self::APP_SITUAZIONE_URL, self::APP_SITUAZIONE_TITLE);
        
        // adding cookie
        /*
         * $wd->manage()->deleteAllCookies();
         * $wd->manage()->addCookie(array(
         * 'name' => 'cookie_name',
         * 'value' => 'cookie_value'
         * ));
         * $cookies = $wd->manage()->getCookies();
         * print_r($cookies);
         */
    }

    /**
     * Login test with wrong and real params
     */
    public function testLogin() {
        $wd = $this->getWd();
        $wd->manage()->deleteAllCookies();
        $wd->get(self::APP_HOME . '/' . self::LOGIN_URL); // Navigate to LOGIN_URL
                                                          
        // 1) Wrong login
        $user = 'utente@inesistente';
        $this->login($user, $user);
        
        // Verify the error msg show
        $login_error_msg = '/html/body/div[1]/div[1]/div/div/div[3]/div[1]';
        $this->waitForXpath($login_error_msg); // Wait until the element is visible
        $incorrectData = $wd->findElement(WebDriverBy::xpath($login_error_msg)); // Text "Email o password errati"
        $this->assertNotNull($incorrectData);
        $this->assertContains(self::ERR_DATI_MSG, $incorrectData->getText());
        
        // checking that we are in the right page
        $this->check_webpage(self::APP_HOME . '/' . self::LOGIN_URL, self::APP_LOGIN);
        
        // 2) Real login
        $this->do_login();
        
        // Verify to be enter and that welcome msg is show
        $welcome_msg = '//*[@id="ngdialog1"]/div[2]/div/div[1]';
        if (! isset($welcome_msg)) { // se esiste compilo i campi
            $this->compile_dialog();
        }
    }

    /**
     * Test the aside navigation bar
     */
    public function testAsideNavigationBar() {
        $wd = $this->getWd();
        
        $this->do_login();
        $wd->get(self::APP_HOME . '/' . self::APP_SITUAZIONE_URL); // Navigate to APP_SITUAZIONE_URL
                                                                   
        // checking that we are in the right page
        $this->check_webpage(self::APP_HOME . '/' . self::APP_SITUAZIONE_URL, self::APP_SITUAZIONE_TITLE);
        
        $navigation_bar_elem_id = array(
            'Situazione' => 'menu-situazione',
            'Anagrafica' => 'menu-anagrafica',
            'Clienti' => 'menu-clienti',
            'Articoli - servizi' => 'menu-articoli-servizi',
            'Fatture' => 'menu-fatture',
            'Modelli' => 'menu-modelli',
            'Strumenti' => 'menu-strumenti',
            'Impostazioni' => 'menu-impostazioni'
        );
        
        // checking that all the section of the navigation bar are ok
        foreach ($navigation_bar_elem_id as $key => $value) {
            $this->check_nav_bar($value, $key);
        }
    }

    /**
     * Test 'impostazioni' section in the aside navigation bar
     */
    public function testImpostazioni() {
        $wd = $this->getWd();
        
        $this->do_login();
        $wd->get(self::APP_HOME . '/' . self::APP_SITUAZIONE_URL); // Navigate to APP_SITUAZIONE_URL
                                                                   
        // checking that we are in the right page
        $this->check_webpage(self::APP_HOME . '/' . self::APP_SITUAZIONE_URL, self::APP_SITUAZIONE_TITLE);
        
        $impostazioni_id = 'menu-impostazioni';
        $this->waitForId($impostazioni_id); // Wait until the element is visible
        $impostazioni_button = $wd->findElement(WebDriverBy::id($impostazioni_id));
        $this->assertNotNull($impostazioni_button);
        $impostazioni_button->click();
        
        if (getEnv('BROWSER') != self::PHANTOMJS) {
            // TODO: probabile bug di phantomjs nell'eseguire il codice seguente (vedi: http://superuser.com/questions/855710/selenium-with-phantomjs-click-not-working)
            // click su Generale
            $imp_generali_path = '//*[@id="menu-impostazioni"]/ul/li[1]/a';
            $this->waitForXpath($imp_generali_path); // Wait until the element is visible
            $imp_generali = $wd->findElement(WebDriverBy::xpath($imp_generali_path));
            
            $this->assertNotNull($imp_generali);
            $imp_generali->click();
        }
    }

    /**
     * Try to import an invoice
     */
    public function testImportazione() {
        $wd = $this->getWd();
        
        $this->do_login(); // Make the login
        $wd->get(self::APP_HOME . '/strumenti/importazione');
        
        $xml = '//*[@id="import-box"]/div[1]/div[2]';
        $xml_id = 'import-box';
        $this->waitForXpath($xml); // Wait until the element is visible
        $drop_area = $wd->findElement(WebDriverBy::id($xml_id));            // TODO: verificare se posso usare questo
        
        $xpath = "//*[@id=\"import-box\"]/div[1]";
        $drop_area = $wd->findElement(WebDriverBy::xpath($xpath));
        
        $input_file = 'C:\Users\Matteo\Desktop\esempio_fattura.xml';
        $this->clickByIdWithJs2($drop_area, $input_file);                
    }

    /**
     * Impossibile leggere da console con browser 'marionette'
     */
    public function testConsole() {
        if (getEnv('BROWSER') != self::MARIONETTE) {
            $wd = $this->getWd();
            
            $this->do_login(); // Make the login
            $wd->get(self::APP_HOME . '/modelli-fattura');
            
            $aggiungi = '/html/body/div[1]/div/section/div/div/div[2]/button';
            $this->waitForXpath($aggiungi); // Wait until the element is visible
            $this->assertNoErrorsOnConsole();
        }
    }

    public function testFinish() {
        self::$climate->info('FINE TEST FATTURATUTTO WEBDRIVER OK!!!!!!!!');
    }

    /**
     * Call the login() function with the set params
     */
    private function do_login() {
        $user = getEnv('FT_USERNAME');
        $password = getEnv('FT_PASSWORD');
        $this->login($user, $password);
    }

    /**
     * Compile login fields and try to enter using the email address
     *
     * @param string $user the email address of the user
     * @param string $password the password of the user
     */
    private function login($user, $password) {
        $wd = $this->getWd();
        $login_url = self::APP_HOME . '/' . self::LOGIN_URL;
        $wd->get($login_url); // Navigate to LOGIN_URL
        $expected_url = $wd->getCurrentURL();
        if ($expected_url == $login_url) {
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
        }
    }

    /**
     * Checking that the url and the title of the webpage are what i expected
     *
     * @param string $url the url of the webpage
     * @param string $title the title of the webpage
     * @param string $wd the webdriver
     */
    private function check_webpage($expected_url, $expected_title) {
        $wd = $this->getWd();
        $url = $wd->getCurrentURL();
        // echo PHP_EOL.'url: '.$url.PHP_EOL;
        switch ($url) {
            case self::SITE_HOME . '/':
                $inizia_button_path = '//*[@id="slider"]/div/div[1]/div/a/p';
                $this->waitForXpath($inizia_button_path); // Wait until the element is visible
                break;
            case self::APP_HOME . '/':
            case self::APP_HOME . '/' . self::LOGIN_URL:
                $email_button_path = '/html/body/div[1]/div[1]/div/div/div[2]/div[2]/button';
                $this->waitForXpath($email_button_path); // Wait until the element is visible
                break;
            case self::APP_HOME . '/' . self::APP_SITUAZIONE_URL:
                $impostazioni_id = 'menu-impostazioni';
                $this->waitForId($impostazioni_id); // Wait until the element is visible
                break;
            default:
                $this->fail("ERROR: (" . $url . "), url non gestita" . PHP_EOL);
        }
        
        $title = $wd->getTitle();
        
        $this->assertEquals($expected_url, $url);
        $this->assertContains($expected_title, $title);
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
        if (getenv('BROWSER') == self::PHANTOMJS) {
            // $text = $elem->getAttribute("textContent");
            $text = $elem->getAttribute("innerText");
        }
        $this->assertContains($expected_title, $text);
    }

    private function check_prova($id, $sendKey) {
        $wd = $this->getWd();
        $this->waitForId($id); // Wait until the element is visible
        $elem = $wd->findElement(WebDriverBy::id($id));
        $elem->sendKeys($sendKey);
        $this->assertNotNull($elem);
        $this->assertContains($expected_title, $elem->getText());
    }

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
        
        // *[@id="ngdialog1"]/div[2]/div/div[2]/div[2]/button
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
        
        // *[@id="ngdialog1"]/div[2]/div/div[3]/form/div[6]/div[2]/select Ordinario
        $ordinario_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[3]/form/div[6]/div[2]/select';
        $this->waitForXpath($ordinario_button_path); // Ordinario
        $ordinario_button = $wd->findElement(WebDriverBy::xpath($ordinario_button_path)); // textfield "Ordinario"
        $ordinario_button->sendKeys('Ordinario');
        
        // avanti
        // *[@id="ngdialog1"]/div[2]/div/div[3]/form/div[7]/button
        $avanti_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[3]/form/div[7]/button';
        $this->waitForXpath($avanti_button_path); // avanti
        $avanti_button = $wd->findElement(WebDriverBy::xpath($avanti_button_path)); // Button "Avanti"
        $avanti_button->click();
        
        // fine
        // *[@id="ngdialog1"]/div[2]/div/div[4]/div[2]/button
        $fine_button_path = '//*[@id="ngdialog1"]/div[2]/div/div[4]/div[2]/button';
        $this->waitForXpath($fine_button_path); // fine
        $fine_button = $wd->findElement(WebDriverBy::xpath($fine_button_path)); // Button "fine"
        $fine_button->click();
    }
}