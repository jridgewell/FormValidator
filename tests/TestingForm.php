<?php

class TestingForm extends \FormValidator\Form {
    public function __construct() {
        $_POST = array(
            'test' => ''
        );
    }

    public function addValidation($key, $value) {
        $args = array($value, $key, &$this->validations);
        $method = static::getMethod('setDataForName');
        $method->invokeArgs($this, $args);
    }

    public function getValidations() {
        return $this->validations;
    }

    protected static function getMethod($name) {
        $class = new ReflectionClass('TestingForm');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
