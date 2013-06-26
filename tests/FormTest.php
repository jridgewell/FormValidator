<?php

if(!defined('__ROOT__')) define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__.'/vendor/autoload.php';
require_once 'TestingForm.php';

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
        $_POST = array();
    }

    public function testGetter() {
        $expected = 'test';
        $_POST['test'] = $expected;
        $this->form->addValidation('test', function() {return true;});

        $this->form->validate();

        $this->assertEquals($expected, $this->form->test, 'Should be able to get data from form using getters');
    }

    public function testGetterWithProtectedProperties() {
        $expected = 'test';
        $_POST = array(
            'cssErrorClass' => $expected,
            'errorWrapperTag' => $expected,
            'errors' => $expected,
            'validations' => $expected,
            'data' => $expected,
        );
        foreach ($_POST as $key => $value) {
            $this->form->addValidation($key, function() {return true;});
        }

        $this->form->validate();

        foreach ($_POST as $key => $value) {
            // can't call_user_func with __get defined methods...
            //$ret = call_user_func(array($this->form, $key));
            // so do this:
            $ret = false;
            eval('$ret = $this->form->' . $key . ';');

            $this->assertEquals($expected, $ret, "Should be able to get data from form using getters, even with conflicts with form's protected variables");
        }
    }

    public function testHasPosted() {
        $className = get_class($this->form);
        $hasPosteds = array(
            '',
            '1',
            'true',
            'submit',
            'false',
            '0',
        );

        foreach ($hasPosteds as $hasPosted) {
            $_POST[$className] = $hasPosted;
            $this->assertTrue($this->form->hasPosted(), 'Should have posted if a POST attributes name is the same as the classname of the form');
        }
    }

    public function testHasError() {
        $this->form->addValidation('test', function() {return false;});

        $this->form->validate();

        $this->assertTrue($this->form->hasError());
    }

    public function testHasErrorWithName() {
        $this->form->addValidation('test', function() {return false;});

        $this->form->validate();

        $this->assertTrue($this->form->hasError('test'));
    }

    public function testValidate() {
        $presence = function($val) { return strlen($val) > 0; };
        $this->form->addValidation('test', $presence);
        $_POST['test'] = 'is present';

        $this->form->validate();

        $this->assertFalse($this->form->hasError());
    }

    public function testValidateNested() {
        $presence = function($val) { return strlen($val) > 0; };
        $this->form->addValidation('test', array('nested' => $presence));
        $_POST['test'] = array('nested' => 'is present');

        $this->form->validate();

        $this->assertFalse($this->form->hasError());
    }

    public function testValidateMultipleValidations() {
        $presence = function($val) { return strlen($val) > 0; };
        $numericality = function($val) { return filter_var($val, FILTER_VALIDATE_INT) !== false; };
        $this->form->addValidation('test', array($presence, $numericality));
        $_POST['test'] = 'not a number';
        $this->form->validate();
        $this->assertTrue($this->form->hasError());

        $_POST['test'] = '3';
        $this->form->validate();
        $this->assertFalse($this->form->hasError());
    }

    public function testValidateMultipleNestedValidations() {
        $presence = function($val) { return strlen($val) > 0; };
        $numericality = function($val) { return filter_var($val, FILTER_VALIDATE_INT) !== false; };
        $this->form->addValidation('test', array('nested' => array($presence, $numericality)));
        $_POST['test'] = array('nested' => 'not a number');
        $this->form->validate();
        $this->assertTrue($this->form->hasError());

        $_POST['test'] = array('nested' => '3');
        $this->form->validate();
        $this->assertFalse($this->form->hasError());
    }

    public function testError() {
        $error = 'error';
        $this->form->addValidation('test', function() use ($error) {return $error;});

        $this->form->validate();
        $this->expectOutputRegex("/$error/");

        $this->form->error('test');
    }

    public function testErrorWithMessage() {
        $errorMessage = 'Custom error message';
        $this->form->addValidation('test', function() {return false;});

        $this->form->validate();
        $this->expectOutputRegex("/$errorMessage/");

        $this->form->error('test', $errorMessage);
    }

    public function testErrorWithMultipleErrors() {
        $this->form->addValidation('test', array(
            function() {return 'first';},
            function() {return 'second';}
        ));

        $this->form->validate();
        $this->expectOutputRegex('/first.*second/');

        $this->form->error('test');
    }

    public function testSubmit() {
        $this->expectOutputRegex('/type="submit"/');
        $this->form->submit();
    }

    public function testSubmitHasClassName() {
        $className = get_class($this->form);
        $this->expectOutputRegex("/name=\"$className\"/");
        $this->form->submit();
    }

    public function testSubmitTakesValue() {
        $value = 'testSubmit';
        $this->expectOutputRegex("/value=\"$value\"/");
        $this->form->submit($value);
    }

    public function testInput() {
        $name = 'test';
        $this->expectOutputRegex("/<input.*name=\"$name\"/");
        $this->form->input($name);
    }

    public function testInputTakesType() {
        $type = 'test';
        $this->expectOutputRegex("/type=\"$type\"/");
        $this->form->input('name', array('type' => $type));
    }

    public function testInputTakesValue() {
        $value = 'test';
        $this->expectOutputRegex("/value=\"$value\"/");
        $this->form->input('name', array('value' => $value));
    }

    public function testInputTakesArbitraryAttribute() {
        $attr = 'test';
        $this->expectOutputRegex("/$attr=\"\"/");
        $this->form->input('name', array($attr => ''));
    }

    public function testInputPreservesUserValueAfterSubmit() {
        $value = 'User Entered Value';
        $_POST['test'] = $value;
        $this->form->addValidation('test', function() {return false;});

        $this->form->validate();

        $this->expectOutputRegex("/value=\"$value\"/");
        $this->form->input('test');
    }

    public function testInputDoesntPreservesUserValueAfterPasswordSubmit() {
        $value = 'User Entered Value';
        $_POST['test'] = $value;
        $this->form->addValidation('test', function() {return false;});

        $this->form->validate();

        $this->expectOutputRegex("/value=\"\"/");
        $this->form->input('test', array('type' => 'password'));
    }

    public function testInputFormatsTextareaCorrectly() {
        $this->expectOutputRegex('/<textarea/');
        $this->form->input('test', array('type' => 'textarea'));
    }

    public function testSelect() {
        $name = 'test';
        $this->expectOutputRegex("/<select.*name=\"$name\"/");
        $this->form->select($name);
    }

    public function testSelectTakesValues() {
        $values = array('first', 'second');
        $this->expectOutputRegex('/value="first".*value="second"/');
        $this->form->select('name', $values);
    }

    public function testSelectTakesKeyAndValues() {
        $values = array('f' => 'first', 's' => 'second');
        $this->expectOutputRegex('/value="f".*value="s"/');
        $this->form->select('name', $values);
    }

    public function testSelectPreservesSelected() {
        $value = 'first';
        $values = array($value, 'second');
        $_POST['name'] = $value;
        $this->form->addValidation('name', function() {return false;});

        $this->form->validate();

        $this->expectOutputRegex('/value="first"[^>]*selected="selected"/');
        $this->form->select('name', $values);
    }

    public function testSelectPreservesMultipleSelected() {
        $values = array('first', 'second');
        $_POST['name'] = $values;
        $this->form->addValidation('name', function() {return false;});

        $this->form->validate();

        $this->expectOutputRegex('/selected="selected".*selected="selected"/');
        $this->form->select('name', $values);
    }
}
