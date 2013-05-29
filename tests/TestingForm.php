<?php

class TestingForm extends \FormValidator\Form {
    public function __construct() {
        $_POST = array(
            'test' => ''
        );
    }

    public function addValidation($key, $value) {
        $method = static::getMethod('setDataForName');
        $method->invoke($this, $value, $key, &$this->validations);
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
