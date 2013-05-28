<?php

if(!defined('__ROOT__')) define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__.'/vendor/autoload.php';

use \FormValidator\Validation;

class ValidationTest extends PHPUnit_Framework_TestCase {

    // A helper function
    protected function assertNotTrue($condition, $message = '') {
        $this->assertTrue($condition !== true, $message);
    }

    public function testValidationsCanBeOptional() {
        $validations = array(
            'email',
            'length',
            'numericality',
            'format',
            'confirmation',
            'inclusion',
            'exclusion',
            'validateWith',
        );
        $optional = array(
            'optional' => true
        );
        $blank_string = '';

        foreach ($validations as $validation) {
            //pass $optional twice, for the validations that take a param
            $validation = call_user_func(array('\FormValidator\Validation', $validation), $optional, $optional);
            $this->assertTrue($validation($blank_string), 'Optional validations should validate a blank string');
        }
    }

    public function testValidationAnything() {
        $validation = Validation::anything();

        $valids = array(
            '',
            '0',
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::anything should validate anything");
        }
    }

    public function testValidationAcceptance() {
        $validation = Validation::acceptance();

        $invalids = array(
            '',
            '0',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::acceptance shouldn't validate things that aren't truthy");
        }

        $valids = array(
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::acceptance should validate things that are truthy");
        }

    }

    public function testValidationAcceptanceIgnoresOptional() {
        $validation = Validation::acceptance(array('optional' => true));

        $blank_string = '';
        $this->assertNotTrue($validation($blank_string), "Validation::acceptance shouldn't validate a blank string, even with optional => true");
    }

    public function testValidationAcceptanceAccept() {
        $validation = Validation::acceptance(array('accept' => 'yes'));

        $invalids = array(
            '',
            '0',
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::acceptance (accept) shouldn't validate things that aren't the accept option");
        }

        $valids = array(
            'yes',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::acceptance should validate the accept option");
        }

    }

    public function testValidationPresence() {
        $validation = Validation::presence();

        $blank_string = '';
        $this->assertNotTrue($validation($blank_string), "Validation::presence shouldn\'t validate a blank string");

        $valids = array(
            '0',
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::presence should validate anything that's not blank");
        }
    }

    public function testValidationPresenceIgnoresOptional() {
        $validation = Validation::presence(array('optional' => true));

        $blank_string = '';
        $this->assertNotTrue($validation($blank_string), "Validation::presence shouldn't validate a blank string, even with optional => true");
    }

    public function testValidationEmail() {
        $validation = Validation::email();

        $invalids = array(
            '0',
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
            '@test.com',
            'test@test'
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::email shouldn't validate things that aren't emails");
        }

        $valids = array(
            'test@test.com',
            'test@sub.test.com',
            'test+t@test.com',
            'test.t@test.com',
            'test123@test.com',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::email should validate emails");
        }
    }

    public function testValidationLengthWithoutOptions() {
        $validation = Validation::length();

        $valids = array(
            '',
            '0',
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::length should validate anything if it's not passed options");
        }
    }

    public function testValidationLengthIs() {
        $validation = Validation::length(array('is' => 3));

        $invalids = array(
            '',
            's',
            'sh',
            'a very long string',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::length (is) shouldn't validate data that doesn't have {is} chars");
        }

        $valids = array(
            '0.1',
            '1.2',
            'tru',
            'fal',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::length (is) should validate data that is {is} chars");
        }
    }

    public function testValidationLengthMaximum() {
        $validation = Validation::length(array('maximum' => 3));

        $invalids = array(
            'shor',
            'a very long string',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::length (maximum) shouldn't validate data that is longer than {maximum}");
        }

        $valids = array(
            '',
            's',
            '01',
            '1.2',
            'tru',
            'fal',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::length (maximum) should validate data that is {maximum} chars or less");
        }
    }

    public function testValidationLengthMinimum() {
        $validation = Validation::length(array('minimum' => 3));

        $invalids = array(
            '',
            's',
            '01',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::length (minimum) shouldn't validate data that is shorter than {minimum}");
        }

        $valids = array(
            '1.2',
            'tru',
            'fal',
            'shor',
            'a very long string',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::length (minimum) should validate data that is {minimum} chars or more");
        }
    }

    public function testValidationNumericality() {
        $validation = Validation::numericality();

        $invalids = array(
            '',
            'true',
            'false',
            's',
            'a very long string',
            '12 string string'
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality shouldn't validate things that aren't numbers");
        }

        $valids = array(
            '0',
            '0.1',
            '1',
            '1.2',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality should validate numbers");
        }
    }

    public function testValidationNumericalityOnlyInteger() {
        $validation = Validation::numericality(array('only_integer' => true));

        $invalids = array(
            '0.1',
            '1.2',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (only_integer) shouldn't validate floats");
        }

        $valids = array(
            '0',
            '1',
            '2',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (only_integers) should validate integers");
        }
    }

    public function testValidationNumericalityEven() {
        $validation = Validation::numericality(array('even' => true));

        $invalids = array('1', '3', '5', '7', '9');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (even) shouldn't validate odd numbers");
        }

        $valids = array('2', '4', '6', '8', '10');
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (even) should validate even numbers");
        }
    }

    public function testValidationNumericalityOdd() {
        $validation = Validation::numericality(array('odd' => true));

        $invalids = array('2', '4', '6', '8', '10');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (odd) shouldn't validate even numbers");
        }

        $valids = array('1', '3', '5', '7', '9');
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (odd) should validate odd numbers");
        }
    }

    public function testValidationNumericalityEqualTo() {
        $validation = Validation::numericality(array('equal_to' => 3));

        $invalids = array('1', '2', '4', '5');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (equal_to) shouldn't validate numbers that aren't {equal_to}");
        }

        $this->assertTrue($validation(3), "Validation::numericality (equal_to) should validate {equal_to}");
    }

    public function testValidationNumericalityLessThan() {
        $validation = Validation::numericality(array('less_than' => 3));

        $invalids = array('3', '4', '5');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (less_than) shouldn't validate numbers greater than or equal to {less_than}");
        }

        $valids = array('0', '1', '2');
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (less_than) should validate numbers less than {less_than}");
        }
    }

    public function testValidationNumericalityLessThanOrEqualTo() {
        $validation = Validation::numericality(array('less_than_or_equal_to' => 3));

        $invalids = array('4', '5', '6');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (less_than_or_equal_to) shouldn't validate numbers greater than {less_than_or_equal_to}");
        }

        $valids = array('1', '2', '3');
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (less_than_or_equal_to) should validate numbers less than or equal to {less_than_or_equal_to}");
        }
    }

    public function testValidationNumericalityGreaterThan() {
        $validation = Validation::numericality(array('greater_than' => 3));

        $invalids = array('1', '2', '3');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (greater_than) shouldn't validate numbers less than or equal to {greater_than}");
        }

        $valids = array('4', '5', '6');
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (greater_than) should validate numbers greater than {greater_than}");
        }
    }

    public function testValidationNumericalityGreaterThanOrEqualTo() {
        $validation = Validation::numericality(array('greater_than_or_equal_to' => 3));

        $invalids = array('0', '1', '2');
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::numericality (greater_than_or_equal_to) shouldn't validate numbers less than {greater_than_or_equal_to}");
        }

        $valids = array('3', '4', '5');
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::numericality (greater_than_or_equal_to) should validate numbers greater than or equal to {greater_than_or_equal_to}");
        }
    }

    public function testValidationFormat() {
        $validation = Validation::format('/\A[\d,.]+\Z/');

        $invalids = array(
            'string',
            '',
            '1 string',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::format should not validate things that don't match the regex");
        }

        $valids = array(
            '1',
            '1,000',
            '1,000.1',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::format should validate things that match the regex");
        }
    }

    public function testValidationConfirmation() {
        $_POST = array('password_confirmation' => '');
        $validation = Validation::confirmation(function() {
            return $_POST['password_confirmation'];
        });

        $invalids = array(
            '',
            'not equals',
        );
        foreach ($invalids as $invalid) {
            $_POST['password_confirmation'] = $invalid;
            $this->assertNotTrue($validation('password'), "Validation::confirmation should not validate when the return value doesn't match the field");
        }

        $_POST['password_confirmation'] = 'password';
        $this->assertTrue($validation('password'), "Validation::confirmation should validate when the return value matches the field");
    }

    public function testValidationInclusion() {
        $validation = Validation::inclusion(array(
            'This is valid',
            'so is this',
            'and, finally, this',
        ));

        $invalids = array(
            'string',
            '',
            '1 string',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::inclusion should not validate things that aren't inside the array");
        }

        $valids = array(
            'This is valid',
            'so is this',
            'and, finally, this',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::inclusion should validate things that are inside the array");
        }
    }

    public function testValidationExclusion() {
        $validation = Validation::exclusion(array(
            'This isnt valid',
            'neither is this',
            'or, finally, this',
        ));

        $invalids = array(
            'This isnt valid',
            'neither is this',
            'or, finally, this',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::exclusion should not validate things that are inside the array");
        }

        $valids = array(
            'This is valid',
            'so is this',
            'and, finally, this',
        );
        foreach ($valids as $valid) {
            $this->assertTrue($validation($valid), "Validation::exclusion should validate things that aren't inside the array");
        }
    }

    public function testValidationValidateWith() {
        $validation = Validation::validateWith(function($val) {
            if ($val == 'letmein') {
                return true;
            }
            return "You can't come in!";
        });

        $invalids = array(
            '',
            '0',
            '0.1',
            '1',
            '1.2',
            'true',
            'false',
            's',
            'a very long string',
        );
        foreach ($invalids as $invalid) {
            $this->assertNotTrue($validation($invalid), "Validation::validateWith should not validate things unless the function `return true;`");
        }

        $valid = 'letmein';
        $this->assertTrue($validation($valid), "Validation::validateWith should validate when the function `return true;`");
    }

    public function testValidationValidateWithErrorReturnValue() {
        $error = "You can't come in!";
        $validation = Validation::validateWith(function($val) use ($error) {
            return $error;
        });

        $invalid = 'letmein';
        $this->assertEquals($error, $validation($invalid), "Validation::validateWith should return the return value of the function");
    }
}
