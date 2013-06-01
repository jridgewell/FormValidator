<?php

namespace FormValidator;

use \FormValidator\Validation;

class Form
{

    /**
     * Stores the name of the css error to be included on elements that don't pass validation
     * Will also be included in the $this->error() output
     * @var String
     */
    protected $cssErrorClass = 'error';

    /**
     * Stores the tagname that wil wrap error messages
     * @var String
     */
    protected $errorWrapperTag = 'p';

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
     * Convenience constructor to set $this->validations
     * @param Mixed $validations
     */
    public function __construct($validations = array())
    {
        $this->validations = $validations;
    }

    /**
     * Convenience method to get posted form data
     * @param String $name
     */
    public function __get($name)
    {
        return $this->getDataForName("[$name]", $this->data);
    }

    /**
     * Returns true if this form has posted
     * @return Boolean
     */
    public function hasPosted()
    {
        return isset($_POST[get_class($this)]);
    }

    /**
     * If $name is supplied, returns true if $name has a validation error
     * Else, returns true if the form has validation errors
     * @param String $name
     * @return Boolean
     */
    public function hasError($name = null)
    {
        if (!$name) {
            return (count($this->errors) > 0);
        }
        $error = $this->getDataForName($name, $this->errors);
        return isset($error);
    }

    /**
     * validates the form against the current validation rules
     * @return array
     */
    public function validate()
    {
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
        return (!$this->hasError()) ? $this->data : null;
    }



    ################################################################################
    ### HTML Methods ###############################################################
    ################################################################################

    /**
     * Echos out any errors the form has
     * @param String $name
     * @param String $message
     */
    public function error($name, $message = null)
    {
        $errors = $this->getDataForName($name, $this->errors);
        if (isset($errors)) {
            if ($message) {
                printf(
                    '<%s class="%s">%s</%s>',
                    $this->errorWrapperTag,
                    $this->cssErrorClass,
                    $message,
                    $this->errorWrapperTag
                );
                return;
            }

            $output = array();
            preg_match('/(\w+)\]?$/', $name, $matches);
            $prettyName = ucwords($matches[1]);

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
    public function submit($value = null, $elementAttributes = array())
    {
        if (!isset($value)) {
            $elementAttributes['value'] = 'Submit';
        }
        $elementAttributes['type'] = 'submit';
        $this->input(get_class($this), $elementAttributes);
    }

    /**
     * Creates an input element with the attributes provided
     * @param String $name
     * @param array $elementAttributes
     */
    public function input($name, $elementAttributes = array())
    {
        $type = (array_key_exists('type', $elementAttributes)) ? $elementAttributes['type'] : 'text';
        $value = (array_key_exists('value', $elementAttributes)) ? $elementAttributes['value'] : '';
        $attributes = $this->toHTMLAttributes(
            $name,
            $elementAttributes,
            array(
                'name'  => $name,
                'type'  => $type,
                'class' => '',
            )
        );

        // Preserve the saved values if the form fails validation
        // EXCEPT for password fields
        if ($type != 'password') {
            $data = $this->getDataForName($name, $this->data);
            if (isset($data)) {
                $value = htmlentities($data, ENT_QUOTES);
            }
        }

        // Handle textarea needing a different value format
        if ($elementAttributes['type'] == 'textarea') {
            echo '<textarea ' . implode(' ', $attributes) . ">$value</textarea>";
        } else {
            $attributes[] = sprintf('%s="%s"', 'value', $value);
            echo '<input ' . implode(' ', $attributes) . ' />';
        }
    }

    /**
     * Creates a select element
     * @param array $elementAttributes
     * @param array $values
     */
    public function select($name, $values = array(), $elementAttributes = array())
    {
        $attributes = $this->toHTMLAttributes(
            $name,
            $elementAttributes,
            array(
                'name'  => $name,
                'class' => '',
            )
        );

        $selected = array();
        $data = $this->getDataForName($name, $this->data);
        if (isset($data)) {
            $selected = (is_array($data)) ? $data : array($data);
        }

        // Echo out the first part of the select element
        echo '<select ' . implode(' ', $attributes) . ">\n";

        // Echo out the values included within the select element
        foreach ($values as $value => $text) {
            $selectedText = (in_array($text, $selected)) ? 'selected="selected"' : '';
            printf("<option %s>%s</option>\n", $selectedText, $name);
        }
        echo "</select>\n";
    }



    ################################################################################
    ### Private Methods ############################################################
    ################################################################################

    /**
     * Validates posted data against a particular rule
     * @param String $value
     * @param String $key
     * @param String $name
     */
    private function validationWalk($validation, $key, $fieldName)
    {
        if (is_callable($validation)) {
            //If this is a key in the root of $validations
            if (strlen($fieldName) === 0) {
                $fieldName = $key;
            }

            // This allows a field to have either a array of validations,
            // or a single validation.
            $fieldValidations = $this->getDataForName($fieldName, $this->validations);
            if ($this->isAssociativeArray($fieldValidations)) {
                $fieldName = sprintf('%s[%s]', $fieldName, $key);
            }

            $postedData = $this->getDataForName($fieldName, $_POST);
            $this->setDataForName($postedData, $fieldName, $this->data);
            $ret = $validation($postedData);
            if ($ret !== true) {
                $this->invalidateElement($fieldName, $ret);
            }
        } else {
            if ($key === '[]') {
                $postedKeys = array_keys($this->getDataForName($fieldName, $_POST));
            } else {
                $postedKeys = array($key);
            }
            foreach ($postedKeys as $key) {
                $fName = sprintf('%s[%s]', $fieldName, $key);
                array_walk($validation, array($this, 'validationWalk'), $fName);
            }
        }
    }

    private function toHTMLAttributes($name, $elementAttributes, $defaultAttributes = array())
    {
        $attributes = array_merge($defaultAttributes, $elementAttributes);

        // Add the error class if the element has an error
        if ($this->hasError($name)) {
            $elementClass = (array_key_exists('class', $attributes)) ? $attributes['class'] : '';
            $class .= " $this->cssErrorClass";
            $attributes['class'] = $class;
        }

        // Convert the name/value key pairs into strings
        $a = array();
        foreach ($attributes as $name => $value) {
            if ($name == 'value') {
                // Taken care of in output itself...
                continue;
            }
            $a[] = sprintf('%s="%s"', $name, htmlentities($value, ENT_QUOTES));
        }
        return $a;
    }

    private function isAssociativeArray($array)
    {
        if (is_array($array)) {
            return (bool)count(array_filter(array_keys($array), 'is_string'));
        }
        return false;
    }

    /**
     * Invalidates the element $name with the errorcode
     * @param String $name
     * @param String $error
     */
    private function invalidateElement($name, $error)
    {
        $element = &$this->getDataForName($name, $this->errors);
        if (isset($element)) {
            $element[] = $error;
        } else {
            $error = array($error);
            $this->setDataForName($error, $name, $this->errors);
        }
    }

    private function &getDataForName($name, &$base, $create = false)
    {
        $pieces = explode('[', $name);
        if ($pieces[0] === '') {
            array_shift($pieces);
        }
        foreach ($pieces as $piece) {
            $piece = preg_replace('/\]$/', '', $piece);
            if (!isset($base)) {
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

    private function setDataForName($data, $name, &$base)
    {
        $base = &$this->getDataForName($name, $base, true);
        $base = $data;
    }
}
