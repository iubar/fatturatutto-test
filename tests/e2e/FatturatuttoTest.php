<?php
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\WebDriverException;

// require_once ('../../vendor/autoload.php');
require_once ('E2eRoot.php');

/**
 * Test of www.fatturatutto.it website
 *
 * @author Matteo
 *        
 */
class FatturatuttoTest extends E2eRoot {

    const SITE_HOME = "http://www.fatturatutto.it/";

    const APP_HOME = "http://app.fatturatutto.it/";

    const LOGIN_URL = "login";

    const APP_SITUAZIONE_URL = "situazione";

    const SITE_TITLE = "FatturaTutto.it";

    const APP_TITLE = "Login";

    const APP_SITUAZIONE_TITLE = "Situazione";

    const ERR_DATI_MSG = "Email o password errati";

    const BENVENUTO_MSG = "Benvenuto su FatturaTutto";

    const PRIVATE_LOGIN_DATA = "C:/Users/Matteo/Desktop/protected_folder/users.ini";

    /**
     * SiteHome and AppHome test and click on button 'Inizia, è gratis'
     */
    public function testSiteHomeTitle() {
        try {
            $wd = $this->getWd();
            $wd->get(self::SITE_HOME); // Navigate to SITE_HOME
                                       
            // SITE HOME
                                       
            // checking that we are in the right page
            $this->check_webpage(self::SITE_HOME, self::SITE_TITLE);
            
            // select button 'Inizia' for php-webdriver
            $inizia_button_path = '//*[@id="slider"]/div/div[1]/div/a/p';
            $this->waitForXpath($inizia_button_path); // Wait until the element is visible
            $start_button = $wd->findElement(WebDriverBy::xpath($inizia_button_path)); // Button "Inizia"
            $start_button->click();
            
            // APP HOME
            
            // checking that we are in the right page
            $this->check_webpage(self::APP_HOME, self::APP_TITLE);
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        }
        
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
        try {
            $wd = $this->getWd();
            $wd->get(self::APP_HOME . self::LOGIN_URL); // Navigate to LOGIN_URL
                                                        
            // 1) Wrong login
            
            $ini_array = parse_ini_file(self::PRIVATE_LOGIN_DATA, true); // Open file 'users.ini' where login data are
            $user = $ini_array['wrongUser']['username'];
            $password = $ini_array['wrongUser']['password'];
            $this->login($user, $password);
            
            // Verify the error msg show
            $login_error_msg = '/html/body/div[1]/div[1]/div/div/div[3]/div[1]';
            $this->waitForXpath($login_error_msg); // Wait until the element is visible
            $incorrectData = $wd->findElement(WebDriverBy::xpath($login_error_msg)); // Text "Email o password errati"
            $this->assertNotNull($incorrectData);
            $this->assertContains(self::ERR_DATI_MSG, $incorrectData->getText());
            
            // checking that we are in the right page
            $this->check_webpage(self::APP_HOME . self::LOGIN_URL, self::APP_TITLE);
            
            // 2) Real login
            
            $user = $ini_array['realUser']['username'];
            $password = $ini_array['realUser']['password'];
            $this->login($user, $password);
            
            // Verify to be enter and that welcome msg is show
            $welcome_msg = '//*[@id="ngdialog1"]/div[2]/div/div[1]/h2';
            if (! isset($welcome_msg)) {
                $this->waitForXpath($welcome_msg); // Wait until the element is visible
                $correctData = $wd->findElement(WebDriverBy::xpath($welcome_msg));
                $this->assertNotNull($correctData);
                $this->assertContains(self::BENVENUTO_MSG, $correctData->getText());
            }
            
            // checking that we are in the right page
            $this->check_webpage(self::APP_HOME . self::APP_SITUAZIONE_URL, self::APP_SITUAZIONE_TITLE);
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        }
    }

    public function testAsideNavigationBar() {
        try {
            $wd = $this->getWd();
            $wd->get(self::APP_HOME . self::APP_SITUAZIONE_URL); // Navigate to APP_SITUAZIONE_URL
                                                                 
            // checking that we are in the right page
            $this->check_webpage(self::APP_HOME . self::APP_SITUAZIONE_URL, self::APP_SITUAZIONE_TITLE);
            
            $navigation_bar_elem_id = array(
                'Situazione' => 'situazione',
                'Anagrafica' => 'anagrafica',
                'Clienti' => 'clienti',
                'Articoli - servizi' => 'articoli-servizi',
                'Fatture' => 'fatture',
                'Modelli' => 'modelli',
                'Strumenti' => 'strumenti',
                'Impostazioni' => 'impostazioni'
            );
            
            // checking that all the section of the navigation bar are ok
            foreach ($navigation_bar_elem_id as $key => $value) {
                $this->check_nav_bar($value, $key);
            }
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        }
    }

    public function testImpostazioni() {
        try {
            $wd = $this->getWd();
            $wd->get(self::APP_HOME . self::APP_SITUAZIONE_URL); // Navigate to APP_SITUAZIONE_URL
                                                                 
            // checking that we are in the right page
            $this->check_webpage(self::APP_HOME . self::APP_SITUAZIONE_URL, self::APP_SITUAZIONE_TITLE);
            
            $impostazioni_id = 'impostazioni';
            $this->waitForId($impostazioni_id); // Wait until the element is visible
            $impostazioni_button = $wd->findElement(WebDriverBy::id($impostazioni_id));
            $this->assertNotNull($impostazioni_button);
            $impostazioni_button->click();
            
            $imp_generali_path = '//*[@id="impostazioni"]/ul/li[1]/a';
            $this->waitForXpath($imp_generali_path); // Wait until the element is visible
            $imp_generali = $wd->findElement(WebDriverBy::xpath($imp_generali_path));
            $this->assertNotNull($imp_generali);
            $imp_generali->click();
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->handleAssertionException($e);
        }
    }

    /**
     * Compile login fields and try to enter using the email address
     *
     * @param string $user the email address of the user
     * @param string $password the password of the user
     */
    private function login($user, $password) {
        try {
            $wd = $this->getWd();
            
            $email_button_path = '/html/body/div[1]/div[1]/div/div/div[2]/div[2]/button';
            $email_enter = $wd->findElement(WebDriverBy::xpath($email_button_path)); // Button "Email"
            $email_enter->click();
            
            // Write into email textfield
            $username_field_path = '/html/body/div[1]/div[1]/div/div/form/div[2]/input';
            $username_text_field = $wd->findElement(WebDriverBy::xpath($username_field_path)); // Field "Username"
            $username_text_field->sendKeys($user);
            
            // Write into password textfield
            $passwor_field_path = '/html/body/div[1]/div[1]/div/div/form/div[3]/input';
            $password_text_field = $wd->findElement(WebDriverBy::xpath($passwor_field_path)); // Field "Password"
            $password_text_field->sendKeys($password);
            
            // Click on 'Accedi' button
            $login_button_path = '/html/body/div[1]/div[1]/div/div/form/div[5]/button';
            $accedi_button = $wd->findElement(WebDriverBy::xpath($login_button_path)); // Button "Accedi"
            $accedi_button->click();
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        }
    }

    /**
     * Checking that the url and the title of the webpage are what i expected
     *
     * @param string $url the url of the webpage
     * @param string $title the title of the webpage
     */
    private function check_webpage($expected_url, $expected_title) {
        try {
            $wd = $this->getWd();
            $title = $wd->getTitle();
            $url = $wd->getCurrentURL();
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        }
        try {
            $this->assertEquals($expected_url, $url);
            $this->assertContains($expected_title, $title);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->handleAssertionException($e);
        }
    }

    /**
     *
     * @param string $id the id of the elem
     * @param string $expected_title the title of the elem
     */
    private function check_nav_bar($id, $expected_title) {
        try {
            $wd = $this->getWd();
            $this->waitForId($id); // Wait until the element is visible
            $elem = $wd->findElement(WebDriverBy::id($id));
            $this->assertNotNull($elem);
            $this->assertContains($expected_title, $elem->getText());
        } catch (WebDriverException $e) {
            $this->handleWebdriverException($e);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->handleAssertionException($e);
        }
    }

    private function check_dialog() {
        $wd = $this->getWd();
        // ngdialog1
        try {
            if ($wd->findElement(WebDriverBy::id('ngdialog1'))) {
                // //*[@id="ngdialog1"]/div[2]/div/p/a
                $elem = $wd->findElement(WebDriverBy::xpath('//*[@id="ngdialog1"]/div[2]/div/p/a'));
                $elem->click();
                // /html/body/div[2]/div[2]
                
                // if(se "Impostazioni" non è cliccabile){
                // 1) verificare la presenza di una finestra di dialogo
                // 2) se la finestra non è presente, allora errore "situazione imprevista"
                // 3) se finestra presente, faccio clic e attesa implicita
                // }
                // clic su "Impostazioni
                
                if (condition) {
                    ;
                }
            }
        } catch (Exception $e) {}
    }
}