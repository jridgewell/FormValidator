<?php

/**
 * @author Matt Labrum <matt@labrum.me>
 * @author Justin Ridgewell
 * @license Beerware
 * @link url
 */

namespace FormValidator;

use \FormValidator\Validation;

class Form {

    /**
     * Stores the name of the css error to be included on elements that don't pass validation
     * Will also be included in the $this->error() output
     * @var String
     */
    public $cssErrorClass = 'error';

    /**
     * Stores the tagname that wil wrap error messages
     * @var String
     */
    public $errorWrapperTag = 'p';

    /**
     * Stores any errors the form has after validation
     * @var Map
     */
    protected $errors = array();

    /**
     * Stores the validation array, this is overridden by the child class
     * @var Map
     */
    protected $validations = array();

    /**
     * Stores the Form data
     * @var Map
     */
    protected $data = array();



    ################################################################################
    ### Pubilc Methods #############################################################
    ################################################################################

    /**
     * Returns true if this form has posted
     * @return Boolean
     */
    public function hasPosted() {
        return isset($_POST[get_class($this)]);
    }

    /**
     * Returns true if the form has validation errors
     * @return Boolean
     */
    public function hasErrors() {
        return (count($this->errors) > 0);
    }

    /**
     * //TODO
     * @param String $name
     * @return Boolean
     */
    public function elementHasError($name) {
        $error = $this->getDataForName($name, $this->errors);
        return isset($error);
    }

    /**
     * validates the form against the current validation rules
     * @return array
     */
    public function validate() {
        $this->errors = array();
        array_walk(
            $this->validations,
            array($this, 'validationWalk'),
            '' // The fieldName will be built recursively
        );
        // Call the subclass when the verify is finished, so they don't have to override the validation method
        if (method_exists($this, 'verify')) {
            $this->verify($this->data);
        }
        // Return the data if there isn't any errors
        return (!$this->hasErrors()) ? $this->data : null;
    }



    ################################################################################
    ### HTML Methods ###############################################################
    ################################################################################

    /**
     * Echos out any errors the form has
     * @param String $name
     * @param String $message
     */
    public function error($name, $message = null) {
        $errors = $this->getDataForName($name, $this->errors);
        if (isset($error)) {
            $output = array();
            $prettyName = '';
            if ($message) {
                $errors = array($message);
            } else {
                preg_match('/(\w+)\]?$', $name, $matches);
                $prettyName = ucwords($matches[1]);
            }
            foreach ($errors as $error) {
                $output[] = sprintf(
                    '<%s class="%s">%s %s</%s>',
                    $this->errorWrapperTag,
                    $this->cssErrorClass,
                    $prettyName,
                    $error,
                    $this->errorWrapperTag
                );
            }
            echo implode("\n", $output);
        }
    }

    /**
     * Creates an submit button that this class can identify
     * @param Mixed $elementAttributes
     */
    public function submitButton($value = 'Submit', $elementAttributes=array()) {
        if (isset($value)) {
            $elementAttributes['value'] = $value;
        }
        $elementAttributes['type'] = 'submit';
        $this->input(get_class($this), $elementAttributes);
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
            'class' => '',
            'value' => ''
        );
        $attributes = array_merge($defaultAttributes, $elementAttributes);

        // Add the error class if the element has an error
        if ($this->elementHasError($name)) {
            $attributes['class'] .= ' ' . $this->cssErrorClass;
        }

        // Preserve the saved values if the form fails validation
        // EXCEPT for password fields
        $value = '';
        if ($attributes['type'] != 'password') {
            $data = $this->getDataForName($name, $this->data);
            if (isset($data)) {
                $value = htmlentities($data, ENT_QUOTES);
            }
        }

        // Convert the name/value key pairs into strings
        $a = array();
        foreach ($attributes as $name => $value) {
            $a[] = sprintf('%s="%s"', $name, htmlentities($value, ENT_QUOTES));
        }

        // Handle textarea needing a different value format
        if ($attributes['type'] == 'textarea') {
            echo '<textarea ' . implode(' ', $a) . '>' . $value . '</textarea>';
        } else {
            $a[] = sprintf('%s="%s"', 'value', $value);
            echo '<input ' . implode(' ', $a) . ' />';
        }
    }

    /**
     * Creates a select element
     * @param array $elementAttributes
     * @param array $values
     */
    public function select($name, $elementAttributes=array(), $values=array()) {
        $defaultAttributes = array(
            'name' => $name,
            'class' => ''
        );
        $attributes = array_merge($defaultAttributes, $elementAttributes);

        // Add the error class if the element has an error
        if ($this->elementHasError($name)) {
            $attributes['class'] .= ' ' . $this->cssErrorClass;
        }

        $selected = array();
        $data = $this->getDataForName($name, $this->data);
        if (isset($data)) {
            $selected = (is_array($data)) ? $data : array($data);
        }

        // Convert the name/value key pairs into strings
        $a = array();
        foreach ($attributes as $name => $value) {
            $a[] = sprintf('%s="%s"', $name, htmlentities($value, ENT_QUOTES));
        }

        // Echo out the first part of the select element
        echo '<select ' . implode(' ', $a) . ">\n";

        // Echo out the values included within the select element
        foreach ($values as $value => $text) {
            $selectedText = (in_array($text, $selected)) ? 'selected="selected"' : '';
            printf("<option %s>%s</option>\n", $selectedText, $name);
        }
        echo '</select>' . "\n";
    }



    ################################################################################
    ### Protected Methods ##########################################################
    ################################################################################

    /**
     * Validates posted data against a particular rule
     * @param String $value
     * @param String $key
     * @param String $name
     */
    protected function validationWalk($validation, $key, $fieldName) {
        if (is_callable($validation)) {
            $postedData = $this->getDataForName($key, $_POST);
            $this->setDataForName($postedData, $key, $this->data);
            $ret = $validation($postedData);
            if ($ret !== true) {
                $this->invalidateElement($key, $ret);
            }
        } else {
            if ($key === '[]') {
                $postedKeys = array_keys($this->getDataForName($fieldName, $_POST));
            } else {
                $postedKeys = array($key);
            }
            foreach($postedKeys as $key) {
                $fName = sprintf('%s[%s]', $fieldName, $key);
                array_walk($validation, array($this, 'validationWalk'), $fName);
            }
        }
    }

    /**
     * Invalidates the element $name with the errorcode
     * @param String $name
     * @param String $error
     */
    protected function invalidateElement($name, $error) {
        $element = &$this->getDataForName($name, $this->errors);
        if (isset($element)) {
            $element[] = $error;
        } else {
            $error = array($error);
            $this->setDataForName($error, $name, $this->errors);
        }
    }

    protected function &getDataForName($name, &$base, $create = false) {
        $pieces = explode('[', $name);
        if ($pieces[0] === '') {
            array_shift($pieces);
        }
        foreach ($pieces as $piece) {
            $piece = preg_replace('/\]$/', '', $piece);
            if (!array_key_exists($piece, $base)) {
                if (!$create) {
                    $base = null;
                    break;
                }
                $base[$piece] = array();
            }
            $base = &$base[$piece];
        }
        return $base;
    }

    protected function setDataForName($data, $name, &$base) {
        $base = &$this->getDataForName($name, $base, true);
        $base = $data;
    }
}
