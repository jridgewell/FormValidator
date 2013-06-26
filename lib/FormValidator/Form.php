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
     * Return true if this form has no errors
     * @return Boolean
     */
    public function isValid()
    {
        return !$this->hasError();
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

            foreach ($errors as $error) {
                $output[] = sprintf(
                    '<%s class="%s">%s</%s>',
                    $this->errorWrapperTag,
                    $this->cssErrorClass,
                    $error,
                    $this->errorWrapperTag
                );
            }
            echo implode('', $output);
        }
    }

    /**
     * Creates an submit button that this class can identify
     * @param Mixed $elementAttributes
     */
    public function submit($value = null, $elementAttributes = array())
    {
        if (!isset($value)) {
            $value = 'Submit';
        }
        $elementAttributes['value'] = $value;
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
        $value = (array_key_exists('value', $elementAttributes)) ? $elementAttributes['value'] : '';
        $attributes = $this->toHTMLAttributes(
            $name,
            $elementAttributes,
            array(
                'name'  => $name,
                'class' => '',
                'type' => 'text'
            )
        );

        // Preserve the saved values if the form fails validation
        // EXCEPT for password fields
        if ($elementAttributes['type'] != 'password') {
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
        echo '<select ' . implode(' ', $attributes) . '>';

        // Echo out the values included within the select element
        $useKeys = $this->isAssociativeArray($values);
        foreach ($values as $key => $text) {
            $value = ($useKeys) ? $key : $text;
            $selectedText = (in_array($value, $selected)) ? 'selected="selected"' : '';
            $value = sprintf('value="%s"', $value);
            printf('<option %s %s>%s</option>', $value, $selectedText, $text);
        }
        echo '</select>';
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
    protected function validationWalk($validation, $key, $fieldName)
    {
        if ($key === '[]') {
            $postedKeys = array_keys($this->getDataForName($fieldName, $_POST));
        } else {
            $postedKeys = array($key);
        }
        foreach ($postedKeys as $key) {
            $fName = $fieldName;
            if (!is_callable($validation)) {
                $fName = $this->toHTMLName($fName, $key);
                array_walk($validation, array($this, 'validationWalk'), $fName);
            } else {
                // This allows a field to have either a array of validations,
                // or a single validation.
                $fieldValidations = $this->getDataForName($fieldName, $this->validations, array('isSafe' => true));
                if ($this->isAssociativeArray($fieldValidations)) {
                    $fName = $this->toHTMLName($fieldName, $key);
                }

                $postedData = $this->getDataForName($fName, $_POST);
                $this->setDataForName($postedData, $fName, $this->data);
                $ret = $validation($postedData);
                if ($ret !== true) {
                    $this->invalidateElement($fName, $ret);
                }
            }
        }
    }

    protected function toHTMLName($base, $nested)
    {
        if (strlen($base) === 0) {
            return $nested;
        }
        return sprintf('%s[%s]', $base, $nested);
    }

    protected function toHTMLAttributes($name, $elementAttributes, $defaultAttributes = array())
    {
        $attributes = array_merge($defaultAttributes, $elementAttributes);

        // Add the error class if the element has an error
        if ($this->hasError($name)) {
            $elementClass = (array_key_exists('class', $attributes)) ? $attributes['class'] : '';
            $elementClass .= " $this->cssErrorClass";
            $attributes['class'] = $elementClass;
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

    protected function isAssociativeArray($array)
    {
        if (is_array($array)) {
            $i = 0;
            $keys = array_keys($array);
            foreach ($keys as $key) {
                if ($key !== $i) {
                    return true;
                }
                ++$i;
            }
        }
        return false;
    }

    /**
     * Invalidates the element $name with the errorcode
     * @param String $name
     * @param String $error
     */
    protected function invalidateElement($name, $error)
    {
        $element = &$this->getDataForName($name, $this->errors);
        if (isset($element)) {
            $element[] = $error;
        } else {
            $error = array($error);
            $this->setDataForName($error, $name, $this->errors);
        }
    }

    protected function &getDataForName($name, &$base, $options = array())
    {
        $create = (array_key_exists('create', $options) && $options['create']);
        $isSafe = (array_key_exists('isSafe', $options) && $options['isSafe']);

        preg_match_all('/[^\[\]]+/', $name, $pieces);
        $pieces = array_shift($pieces);
        foreach ($pieces as $piece) {
            if (!isset($base)) {
                if (!$create) {
                    $base = null;
                    break;
                }
                $base[$piece] = array();
            }
            if ($isSafe && array_key_exists('[]', $base)) {
                $base = &$base['[]'];
                continue;
            }
            $base = &$base[$piece];
        }
        return $base;
    }

    protected function setDataForName($data, $name, &$base)
    {
        $base = &$this->getDataForName($name, $base, array('create' => true));
        $base = $data;
    }
}
