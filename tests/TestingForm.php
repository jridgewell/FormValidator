<?php

class TestingForm extends \FormValidator\Form {
    public function __construct() {
        $_POST = array(
            'test' => ''
        );
    }

    public function addValidation($key, $value) {
        $this->setDataForName($key, $value, $this->validations);
    }
}
