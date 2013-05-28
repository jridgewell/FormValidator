<?php

if(!defined('__ROOT__')) define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__.'/vendor/autoload.php';

use \FormValidator\Form;

class FormTest extends PHPUnit_Framework_TestCase {

    protected $form;

    // A helper function
    protected function assertNotTrue($condition, $message = '') {
        $this->assertTrue($condition !== true, $message);
    }

    // Make sure each test has a new form to play with
    protected function setUp() {
        $this->form = new TestingForm();
    }

}
