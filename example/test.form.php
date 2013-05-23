<?php
	require_once("vendor/autoload.php");

    use \FormValidator\Form;
    use \FormValidator\Validation;

    class TestForm extends \FormValidator\Form {
        public $validation = array( // Contains a hash array of form elements
            "name" => Validation::presence() // name field must contain something
        );
    }
?>