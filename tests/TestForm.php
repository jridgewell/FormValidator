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

    public function testCallback($value, $params) {
        return (isset($params['param1']) && strlen($value) > 0) ? true : false;
    }

    public function addListData($fieldName, $array) {
        parent::addListData($fieldName, $array);
    }
}

