<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
require_once ('../../vendor/autoload.php');

class RoboFile extends Iubar\Build\RoboFile {
    
  function __construct() {
       parent::__construct(__DIR__);
   }
   
}