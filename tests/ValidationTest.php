<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__.'/vendor/autoload.php';
require_once __ROOT__.'/tests/TestForm.php';

use \FormValidator\Validation;

class StackTest extends PHPUnit_Framework_TestCase {

    public function testValidateAnything() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::anything());

        // test is empty, shouldn't create an error
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test isn't empty, shouldn't create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidatePresence() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::presence());

        // test is empty, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't empty, shouldn't create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidatePresenceIgnoresOptional() {
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::presence(array(
            'optional' => true
        )));

        // Have not accepted, should create error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());
    }

    public function testValidateNumericality() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::numericality());

        // test is empty, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't numeric, should create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test starts numeric but isn't, should create an error
        $_POST['test'] = '2 testing';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is numeric, shouldn't create an error
        $_POST['test'] = '4';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateNumericalityEven() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::numericality(array(
            'even' => true
        )));

        // test is odd, should create an error
        $_POST['test'] = '3';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is even, shouldn't create an error
        $_POST['test'] = '2';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateNumericalityOdd() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::numericality(array(
            'odd' => true
        )));

        // test is even, should create an error
        $_POST['test'] = '2';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is odd, shouldn't create an error
        $_POST['test'] = '3';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateEmail() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::email());

        // test is empty, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't email, should create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't email, should create an error
        $_POST['test'] = '"test ."@test.com';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is email, shouldn't create an error
        $_POST['test'] = 'test-+_test@test.com';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is email, shouldn't create an error
        $_POST['test'] = 'test@test.test.com';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is email, shouldn't create an error
        $_POST['test'] = 'test@test.com';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateUrl() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::url());

        // test is empty, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't url, should create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is url, shouldn't create an error
        $_POST['test'] = 'http://www.test.com/testing/_a/url';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateMultipleSimple() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', array(Validation::presence(), Validation::numericality()));

        // test is empty, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is not empty but not number, should create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is not empty and is number, shouldn't create an error
        $_POST['test'] = '4';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateLength() {
        // Setup
        $testForm = new TestForm();

        // Is
        $testForm->addToValidation('test', array(Validation::length(array('is' => 1))));

        // test is less than 1, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is 1, shouldn't create an error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is greater than 1, should create an error
        $_POST['test'] = '12';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // Min
        $testForm->addToValidation('test', array(Validation::length(array('minimum' => 1))));

        // test is less than 1, should create an error
        $_POST['test'] = '';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is 1, shouldn't create an error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is greater than 1, shouldn't create an error
        $_POST['test'] = '12';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // Max
        $testForm->addToValidation('test', array(Validation::length(array('maximum' => 5))));

        // test is greater than 5, should create an error
        $_POST['test'] = '123456';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());


        // test is 5, shouldn't create an error
        $_POST['test'] = '12345';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is less than 5, shouldn't create an error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // Min and Max
        $testForm->addToValidation('test', array(Validation::length(array('maximum' => 5, 'minimum' => 1))));

        // test is less than 1, should create an error
        $_POST['test'] = '';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is greater than 5, should create an error
        $_POST['test'] = '123456';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is 1, shouldn't create an error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is 5, shouldn't create an error
        $_POST['test'] = '12345';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test is between 1 and 5, shouldn't create an error
        $_POST['test'] = '123';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateConfirmation() {
        // Setup
        $testForm = new TestForm();
        $_POST['test2'] = 'testing';
        $testForm->addToValidation('test', array(Validation::confirmation(function() {
            return $_POST['test2'];
        })));

        // test doesn't equal test2, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test equals test2, shouldn't cause an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateFormat() {
        // Setup
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::format('/[a-zA-Z0-9]+/'));

        // test doesn't match regex, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test does match regex, shouldn't cause an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateExclustion() {
        // Setup
        $testForm = new TestForm();

        // list param
        $testForm->addToValidation('test', array(Validation::exclusion(array(
            'test1',
            'test2',
            'testing'
        ))));

        // test is in list, shouldn't cause an error
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());

        // test isn't in list, should create an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());
    }

    public function testValidateInclusion() {
        // Setup
        $testForm = new TestForm();

        // list param
        $testForm->addToValidation('test', array(Validation::inclusion(array(
            'test1',
            'test2',
            'testing'
        ))));

        // test isn't in list, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is in list, shouldn't cause an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateMultipleAdvanced() {
        $testForm = new TestForm();
        $testForm->addToValidation('test', array(
            Validation::inclusion(array(
                '1',
                'test2',
                'testing'
            )),
            Validation::length(array('minimum' => 2))
        ));

        // test isn't in list, should create an error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is in list but below min, should cause an error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is in list and above min, shouldn't cause an error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateValidateWith() {
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::validate_with(function($val) {
            return (strpos($val, 'te') === 0);
        }));

        // test doesn't start with 'te', should create error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test does start with 'te', shouldn't create error
        $_POST['test'] = 'testing';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateAcceptance() {
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::acceptance());

        // Have not accepted, should create error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test is truthy, shouldn't create error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateAcceptanceWithAccept() {
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::acceptance(array(
            'accept' => 'yes'
        )));

        // Have not accepted, should create error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't our custom truthy, should create error
        $_POST['test'] = '1';
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());

        // test isn't our custom truthy, shouldn't create error
        $_POST['test'] = 'yes';
        $testForm->validate();
        $this->assertFalse($testForm->hasErrors());
    }

    public function testValidateAcceptanceIgnoresOptional() {
        $testForm = new TestForm();
        $testForm->addToValidation('test', Validation::acceptance(array(
            'optional' => true
        )));

        // Have not accepted, should create error
        $testForm->validate();
        $this->assertTrue($testForm->hasErrors());
    }

}