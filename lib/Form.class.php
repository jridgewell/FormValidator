<?php

/**
 * @author Matt Labrum <matt@labrum.me>
 * @license Beerware
 * @link url
 */

class Form {

    /**
     * Stores the path of the Forms directory
     * @var String
     */
    static public $formDirectory = './Forms/';

    /**
     * Stores the name of the css error to be included on elements that don't pass validation
     * @var String
     */
    static public $cssErrorClass = 'Error';

    /**
     * Stores any errors the form has after validation
     * @var Map
     */
    public $errors = array();

    /**
     * Stores the validation array, this is overridden by the child class
     * @var Map
     */
    public $validation = array();

    /**
     * Stores the Form data
     * @var Map
     */
    private $data = array();


    /**
     * Stores the list data for any elements that use lists, eg Select
     * @var Map
     */
    private $listData = array();

    /**
     * Loads the specified form file and initializes it
     * @param String $name
     * @return Form
     */
    static public function loadForm($name) {
        $name = ucfirst($name);
        $file = self::$formDirectory . $name . '.form.php';

        if (file_exists($file) && is_readable($file)) {
            require_once($file);
            $class = $name . 'Form';
            return new $class;
        } else {
            throw new FileDoesntExistException();
        }
    }

    /**
     * Adds list data to the form for validation
     * @param String $name
     * @param array $data
     */
    public function addListData($name, $data) {
        $this->setDataForName($data, $name, $this->listData);
    }

    /**
     * retrieves stored list data
     * @param String $name
     * @return array
     */
    public function getListData($name) {
        $data = $this->getDataForName($name, $this->listData);
        return isset($data) ? $data : null;
    }


    /**
     * validates the form against the current validation rules
     * @param String $name
     * @param Int $errorCode
     * @return array
     */
    public function validate() {
        array_walk(
            $this->validation,
            array($this, 'validateRule'),
            array('', $_POST, &$this->data)
        );
        // Call the subclass when the verify is finished, so they don't have to override the validation method
        if (method_exists($this, 'verify')) {
            $this->verify($this->data);
        }
        // Return the data if there isn't any errors
        return !$this->hasErrors() ? $this->data : null;
    }


    /**
     * Validates posted data against a particular rule
     * @param String $name
     * @param int $rule
     */
    function validateRule($value, $key, $args) {
        $name = $args[0];
        $from = $args[1];
        $to = &$args[2];
        if (!is_array($value)) {
            $to = $from;
            $ret = Validator::isElementValid($value, $from, null, $this);
            if ($ret !== true) {
                $this->invalidateElement($name, $ret);
            }
        //TODO: Validate the Validators that take arguments
        //} elseif ($value[0] === VALIDATE_LENGTH) {
        } else {
            if ($key === '[]') {
                foreach($from as $k => $p) {
                    $name .= '[' . $k . ']';
                    $to[$k] = array();
                    $args = array (
                        $name,
                        $p,
                        &$to[$k],
                    );
                    array_walk($value, array($this, 'validateRule'), $args);
                }
            } else {
                $name .= '[' . $key . ']';
                $to[$key] = array();
                $args = array (
                    $name,
                    $from[$key],
                    &$to[$key],
                );
                array_walk($value, array($this, 'validateRule'), $args);
            }
        }
    }


    /**
     * Returns true if a form has posted
     * @return Boolean
     */
    public function hasPosted() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Returns true if the current form has posted, this will only work if you use the $form->submitButtom() function to generate your submit button
     * @param String $name
     * @param Int $errorCode
     * @return Boolean
     */
    public function isMe() {
        return $this->hasPosted() && isset($_POST[get_class($this)]);
    }

    /**
     * Returns true if the form has validation errors
     * @return Boolean
     */
    public function hasErrors() {
        return count($this->errors) > 0;
    }

    /**
     * Returns true if the specified form element has an error, also by specifying an error code you can check if the element has a specific error
     * @param String $name
     * @param Int $errorCode
     * @return Boolean
     */
    public function elementHasError($name, $errorCode=false) {
        $error = $this->getDataForName($name, $this->errors);
        if (isset($error)) {
            if (!$errorCode) {
                return true;
            } else {
                if (is_array($error)) {
                    return in_array($errorCode, $error);
                } else {
                    return $error == $errorCode;
                }
            }
        }
        return false;
    }

    /**
     * Invalidates the element $name with the errorcode
     * @param String $name
     * @param Int $errorCode
     */
    public function invalidateElement($name, $errorCode) {
        $element = &$this->getDataForName($name, $this->errors);
        if (isset($element)) {
            if (!is_array($element)) {
                $element = array($element);
            }
            $element[] = $errorCode;
        } else {
            $this->setDataForName($errorCode, $name, $this->errors);
        }
    }

    /**
     * Echos out any errors the form has
     * @param String $name
     * @param mixed $message
     */
    public function error($name, $message) {
        $error = $this->getDataForName($name, $this->errors);
        if (isset($error)) {
            if (is_array($message)) {
                $er = array();
                foreach ($message as $errorCode => $m) {
                    if ($this->elementHasError($name, $errorCode)) {
                        $er[] = '<div>' . $m . '</div>';
                    }
                }
                echo implode("\n", $er);
            } else {
                if ($this->elementHasError($name, $message)) {
                    echo '<div>' . $message . '</div>';
                }
            }
        }
    }

    /**
     * Creates an input element with the attributes provided
     * @param String $name
     * @param array $elementAttributes
     */
    public function input($name, $elementAttributes=array()) {
        $defaultAttributes = array(
            'name'  => $name,
            'type'  => 'text',
            'value' => ''
        );
        $attributes = array_merge($defaultAttributes, $elementAttributes);

        // Add the error class if the element has an error
        if ($this->elementHasError($name)) {
            if (isset($attributes['class'])) {
                $attributes['class'] .= ' ' . self::$cssErrorClass;
            } else {
                $attributes['class'] = self::$cssErrorClass;
            }
        }

        // Preserve the saved values if the form fails validation
        $data = $this->getDataForName($name, $this->data);
        if (isset($data) && $attributes['type'] != 'password') {
            $attributes['value'] = $data;
        }

        // Convert the name/value key pairs into strings
        $a = array();
        foreach ($attributes as $name => $value) {
            $a[] = sprintf('%s="%s"', $name, htmlentities($value, ENT_QUOTES));
        }

        // Handle textarea needing a different value format
        if ($attributes['type'] == 'textarea') {
            echo '<textarea ' . implode(' ', $a) . '>' . $attributes['value'] . '</textarea>';
        } else {
            echo '<input ' . implode(' ', $a) . ' />';
        }
    }

    public function &getDataForName($name, &$base) {
        $pieces = explode('[', $name);
        if ($pieces[0] === '') {
            array_shift($pieces);
        }
        foreach ($pieces as $piece) {
            $piece = preg_replace('/\]$/', '', $piece);
            if (isset($piece, $base)) {
                $base = &$base[$piece];
            } else {
                $base = NULL;
                break;
            }
        }
        return $base;
    }

     public function setDataForName($data, $name, &$base) {
        $pieces = explode('[', $name);
        if ($pieces[0] === '') {
            array_shift($pieces);
        }
        foreach ($pieces as $piece) {
            $piece = preg_replace('/\]$/', '', $piece);
            if (isset($piece, $base)) {
                $base = &$base[$piece];
            } else {
                $base[$piece] = array();
                $base = &$base[$piece];
            }
        }
        $base = $data;
    }

    /**
     * Creates an submit button that this class can identify
     * @param Mixed $elementAttributes
     */
    public function submitButton($value, $elementAttributes=array()) {
        if (isset($value)) {
            $elementAttributes['value'] = $value;
        }
        $elementAttributes['type'] = 'submit';
        $this->input(get_class($this), $elementAttributes);
    }

    /**
     * Creates a select element
     * @param array $elementAttributes
     * @param array $values
     * @param Boolean $useKeys
     */
    public function select($name, $elementAttributes=array(), $values=array(), $useKeys=false) {
        $defaultAttributes = array(
            'name' => $name,
            'type' => 'normal',
        );
        $attributes = array_merge($defaultAttributes, $elementAttributes);

        $selected   = false;
        $data = $this->getDataForName($name, $this->data);
        if (isset($data)) {
            $selected = $data;
        }

        // If the passed values are empty, try to get it from the list data the class holds
        if (empty($values)) {
            if ($list = $this->getListData($name)) {
                $values = $list;
            }
        }

        // Handle custom select types
        switch($attributes['type']) {
            case 'timezone' :
                $values = timezone_identifiers_list();
                break;
        }
        unset($attributes['type']);

        // Convert the name/value key pairs into strings
        $a = array();
        foreach ($attributes as $name => $value) {
            $a[] = sprintf('%s="%s"', $name, $value);
        }

        // Echo out the first part of the select element
        echo '<select ' . implode(' ', $a) . ' >\n';

        // Echo out the values included within the select element
        foreach ($values as $value => $name) {
            $html = '<option ';

            if ($useKeys) {
                $html .= sprintf('value="%s"', htmlentities($value, ENT_QUOTES));
                if ($selected == $value || $selected == $name) {
                    $html .= ' selected="selected" ';
                }
            } else {
                if ($selected == $name) {
                    $html .= ' selected="selected" ';
                }
            }
            echo $html . '>' . $name . '</option>'  . "\n";
        }
        echo '</select>' . "\n";
    }

}

class FileDoesntExistException extends \Exception{}
