<?php
	require_once("../lib/Form.class.php");
	require_once("../lib/Validator.class.php");
    class TestForm extends Form {
        public $validation = array( // Contains a hash array of form elements
            "name" => VALIDATE_NOT_EMPTY // name field must contain something
        );
    }
?>