<?php

class TestForm extends \FormValidator\Form {
    public function __construct() {
        $_POST = array(
            'test' => ''
        );
    }

    public function addToValidation($key, $value) {
        $this->validation[$key] = $value;
    }
}
