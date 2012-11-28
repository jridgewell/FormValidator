<?php
	require_once 'lib/Form.class.php';
	class TestForm extends Form {
		public function __construct() {
			$_POST = array(
				'test' => ''
			);
		}
		
		public function addToValidation($key, $value) {
			$this->validation[$key] = $value;
		}

		public function testCallback($value, $params) {
			return (isset($params['param1']) && strlen($value) > 0) ? true : false;
		}

		public function addListData($fieldName, $array) {
			parent::addListData($fieldName, $array);
		}
	}

	class StackTest extends PHPUnit_Framework_TestCase {

		public function testValidateDoNothing() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', VALIDATE_DO_NOTHING);
			
			// test is empty, shouldn't create an error
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			
			// test isn't empty, shouldn't create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}

		public function testValidateNotEmpty() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', VALIDATE_NOT_EMPTY);
			
			// test is empty, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test isn't empty, shouldn't create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}
		
		public function testValidateNumber() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', VALIDATE_NUMBER);
			
			// test is empty, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test isn't numeric, should create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());

			// test is numeric, shouldn't create an error
			$_POST['test'] = '4';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}
		
		public function testValidateEmail() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', VALIDATE_EMAIL);
			
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
		
		public function testValidateTimeZone() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', VALIDATE_TIMEZONE);
			
			// test is empty, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test isn't timezone, should create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());

			// test is timezone, shouldn't create an error
			$_POST['test'] = 'America/New_York';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}
		
		public function testValidateUrl() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', VALIDATE_URL);
			
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
			$testForm->addToValidation('test', array(VALIDATE_NOT_EMPTY, VALIDATE_NUMBER));
			
			// test is empty, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_NOT_EMPTY));
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_NUMBER));

			// test is not empty but not number, should create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_NOT_EMPTY));
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_NUMBER));

			// test is not empty and is number, shouldn't create an error
			$_POST['test'] = '4';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_NOT_EMPTY));
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_NUMBER));
		}
		
		public function testValidateLength() {
			// Setup
			$testForm = new TestForm();
			
			// Min
			$testForm->addToValidation('test', array(VALIDATE_LENGTH, 'min' => 1));

			// test is less than 1, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is 1, shouldn't create an error
			$_POST['test'] = '1';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is greater than 1, shouldn't create an error
			$_POST['test'] = '12';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));

			// Max
			$testForm->addToValidation('test', array(VALIDATE_LENGTH, 'max' => 5));

			// test is greater than 5, should create an error
			$_POST['test'] = '123456';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_LENGTH));


			// test is 5, shouldn't create an error
			$_POST['test'] = '12345';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is less than 5, shouldn't create an error
			$_POST['test'] = '1';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));
			
			// Min and Max
			$testForm->addToValidation('test', array(VALIDATE_LENGTH, 'max' => 5, 'min' => 1));
			
			// test is less than 1, should create an error
			$_POST['test'] = '';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is greater than 5, should create an error
			$_POST['test'] = '123456';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is 1, shouldn't create an error
			$_POST['test'] = '1';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is 5, shouldn't create an error
			$_POST['test'] = '12345';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is between 1 and 5, shouldn't create an error
			$_POST['test'] = '123';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));
		}
		
		public function testValidateMustMatchField() {
			// Setup
			$testForm = new TestForm();
			$_POST['test2'] = 'testing';
			$testForm->addToValidation('test', array(VALIDATE_MUST_MATCH_FIELD, 'field' => 'test2'));
			
			// test doesn't equal test2, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test equals test2, shouldn't cause an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}

		public function testValidateMustMatchRegex() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', array(VALIDATE_MUST_MATCH_REGEX, 'regex' => '/[a-zA-Z0-9]+/'));
			
			// test doesn't match regex, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test does match regex, shouldn't cause an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}

		public function testValidateCustom() {
			// Setup
			$testForm = new TestForm();
			$testForm->addToValidation('test', array(VALIDATE_CUSTOM, 'callback' => 'testCallback', 'errorCode' => 'test', 'param1' => 'extra params'));
			
			// test won't pass custom, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', 'test'));
			
			// test will pass custom, shouldn't cause an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}

		public function testValidateInList() {
			// Setup
			$testForm = new TestForm();
			
			// list param
			$testForm->addToValidation('test', array(VALIDATE_IN_DATA_LIST, 'list' => array(
				'test1',
				'test2',
				'testing'
			)));
			
			// test isn't in list, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test is in list, shouldn't cause an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());

			// list param && useKeys
			$testForm->addToValidation('test', array(VALIDATE_IN_DATA_LIST, 'useKeys' => true, 'list' => array(
				1 => 'test1',
				2 => 'test2',
				'test' => 'testing'
			)));
			// test isn't in list keys, should create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test is in list keys, shouldn't cause an error
			$_POST['test'] = '1';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());


			// addListData
			$testForm->addListData('test', array(
				'test1',
				'test2',
				'testing'
			));
			$testForm->addToValidation('test', VALIDATE_IN_DATA_LIST);
			
			// test isn't in list, should create an error
			$_POST['test'] = '';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test is in list, shouldn't cause an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());

			// addListData && useKeys
			$testForm->addListData('test', array(
				1 => 'test1',
				2 => 'test2',
				'test' => 'testing'
			));
			$testForm->addToValidation('test', array(VALIDATE_IN_DATA_LIST, 'useKeys' => true));
			// test isn't in list keys, should create an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			
			// test is in list keys, shouldn't cause an error
			$_POST['test'] = '1';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
		}
		
		public function testValidateMultipleAdvanced() {
			$testForm = new TestForm();
			$testForm->addToValidation('test', array(
				array(VALIDATE_IN_DATA_LIST, 'list' => array(
					'1',
					'test2',
					'testing'
				)),
				array(VALIDATE_LENGTH, 'min' => 2)
			));

			// test isn't in list, should create an error
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_IN_DATA_LIST));
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_LENGTH));

			// test is in list but below min, should cause an error
			$_POST['test'] = '1';
			$testForm->validate();
			$this->assertTrue($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_IN_DATA_LIST));
			$this->assertTrue($testForm->elementHasError('test', VALIDATE_LENGTH));
			
			// test is in list and above min, shouldn't cause an error
			$_POST['test'] = 'testing';
			$testForm->validate();
			$this->assertFalse($testForm->hasErrors());
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_IN_DATA_LIST));
			$this->assertFalse($testForm->elementHasError('test', VALIDATE_LENGTH));
		}
	}

?>