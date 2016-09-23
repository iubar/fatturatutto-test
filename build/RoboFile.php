<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
require_once ('../vendor/autoload.php');

use Iubar\Build\Selenium_RoboTask;

class RoboFile extends Selenium_RoboTask {
    
  function __construct() {
       parent::__construct(__DIR__);
   }
   
}