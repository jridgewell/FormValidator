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
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testError() {
        $error = 'error';
        $this->form->addValidation('test', function() use ($error) {return $error;});

        $this->form->validate();
        $this->expectOutputString('<p class="error">Test '. $error .'</p>');

        $this->form->error('test');
    }

    public function testErrorGuessesName() {
        $name = 'test';
        $properName = ucwords($name);
        $this->form->addValidation($name, function() {return false;});

        $this->form->validate();
        $this->expectOutputString('<p class="error">' . $properName . ' </p>');

        $this->form->error($name);
    }

    public function testErrorGuessesNestedName() {
        $name = 'test[name][er]';
        $properName = 'Er';
        $this->form->addValidation($name, function() {return false;});

        $this->form->validate();
        $this->expectOutputString('<p class="error">' . $properName . ' </p>');

        $this->form->error($name);
    }

    public function testErrorWithMessage() {
        $errorMessage = 'Custom error message';
        $this->form->addValidation('test', function() {return false;});

        $this->form->validate();
        $this->expectOutputString('<p class="error">' . $errorMessage . '</p>');

        $this->form->error('test', $errorMessage);
    }

    public function testErrorWithMultipleErrors() {
        $this->form->addValidation('test', array(
            function() {return 'first';},
            function() {return 'second';}
        ));

        $this->form->validate();
        $this->expectOutputString(
            '<p class="error">Test first</p>' . "\n" .
            '<p class="error">Test second</p>'
        );

        $this->form->error('test');
    }

    public function testSubmitButton() {
        $className = get_class($this->form);
        $this->expectOutputString('<input name="' . $className . '" type="submit" class="" value="Submit" />');
        $this->form->submitButton();
    }
}
