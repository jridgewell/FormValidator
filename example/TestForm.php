<?php
	require_once('../vendor/autoload.php');

    use \FormValidator\Form;
    use \FormValidator\Validation;

    class TestForm extends Form {

        public function __construct() {
            $this->validations = array(
                'user' => array(
                    'name' => Validation::presence(),
                    'age' => Validation::numericality(array(
                        'only_integer' => true,
                        'greater_than' => 13
                    )),
                    'homepage' => array(
                        Validation::url(array(
                            'optional' => true
                        )),
                        Validation::length(array(
                            'optional' => true,
                            'maximum' => '256'
                        ))
                    )
                )
            );
        }
    }
?>
