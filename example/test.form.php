<?php
	require_once("vendor/autoload.php");

    use \FormValidator\Form;
    use \FormValidator\Validation;

    class TestForm extends Form {
        public $validations = array( // Contains a hash array of form elements
            "name" => Validation::presence() // name field must contain something
        );
    }
?>
