# FormValidator [![Build Status](https://travis-ci.org/jridgewell/FormValidator.png?branch=master)](https://travis-ci.org/jridgewell/FormValidator)

FormValidator allows you to create and validate forms using a simple
rule based approach. It uses an API very similar to Rails' ActiveRecord.

## Basics

### Setting up your first form

A form file is just a class that extends the \FormValidator\Form class
In this example, the form validator checks if `name` isn't empty

#### test.form.php (the model)

```php
<?php
    use \FormValidator\Form;
    use \FormValidator\Validation;

    class TestForm extends \FormValidator\Form {
        public function __construct() {
            $this->validations = array( // Contains a hash array of form elements
                "name" => Validation::presence() // name field must contain something
            );
        }
    }
?>
```

#### index.php (the controller)

```php
<?php
    require_once('test.form.php')
    $form = new TestForm();

    /* Checks if the form has submitted then the form is checked for validation against the rules contained
       within the $validations array of TestForm returning the validated data if its successful
     */

    if($form->hasPosted() && ($data = $form->validate())) {
        // Form passes validation, use the $data for validated POST data

    } else {
        // Form hasn't posted or hasn't passed validation, so we load our html file
        require_once('form.html.php');
    }
?>
```

#### form.html.php (the view)

```php
    <form name='input' method='POST'>
    <?php $form->error('name', 'There was an error'); ?>

    Please Enter your name: <?php $form->input('name'); ?><br/>
    <?php $form->submitButton('Submit');?>
    </form>
```

**Note:** If the form fails validation, by using the `$form->input`
method, we preserve whatever value was in that field (**except for
password fields**)

## Installation

### Via Composer

    composer require "jridgewell/form-validator:~1.0"

Then just add `require 'vendor/autoload.php';` to any code that requires
FormValidator.


## The Validations Array

The `$validations` array contains all the form fields and rules that
need to pass, for the form to be valid. In the example above, it showed
a single rule applying to one form element, but you can apply multiple
rules to an element by using an array.

```php
<?php
    class TestForm extends Form{
        public function __construct() {
            $this->validations = array(
                'name' => Validation::presence(),
                'age'   => array( //Specifiy multiple rules
                    Validation::presence(),
                    Validation::numericality()
                )
            );
        }
    }
?>
```
In our html file, if we wanted to show the errors for the validations,
we could do the following:

```php
<?php
    <form name='input' method='POST'>
    <?php $form->error('name', 'There was an error'); ?>

    Please Enter your name: <?php $form->input('name'); ?><br/>

    <?php $form->error('age', 'This is an optional custom message about age'); ?>

    Please Enter your age: <?php $form->input('age'); ?><br/>
    <?php $form->submitButton('Submit');?>
    </form>
?>
```

### Validation Array Options

Most validations also support passing in an options array. This allows
for custom messages, and can allow for a field to be optional (blank).
Please see the validation for acceptable parameters.

```php
<?php
    class TestForm extends Form {
        public function __construct() {
            $this->validations = array(
                'name' => Validation::length(array(
                    'minimum'  => 0,
                    'maximum' => 100
                )),
                'age'   => Validation::numericality(array(
                    'optional' => true,
                    'only_integer' => true
                )),
                'username' => Validation::exclusion(array(
                    'admin',
                    'superuser'
                ), array(
                    'message' => 'You are not our master!'
                ))
            );
        }
    }
?>
```

## List of validations

### Simple Validations

<table>
    <tr>
        <th>Validation</th>
        <th>Options</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>Validation::anything()</td>
        <td>No options</td>
        <td>This field is always valid</td>
    </tr>
    <tr>
        <td>Validation::acceptance()</td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>accept => 'truthy'</dt>
                    <dd>The value that this field will be compared (==)
                    with. Defaults to true</dd>
            </dl>
        </td>
        <td>This field must be accepted (truthy)</td>
    </tr>
    <tr>
        <td>Validation::email()</td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td>This field must be a valid email</td>
    </tr>
    <tr>
        <td>Validation::length()</td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
                <dt>is => $x</dt>
                    <dd>The length of this field must be equal to $x</dd>
                <dt>minimum => $x</dt>
                    <dd>The length of this field must be at least (<=) $x</dd>
                <dt>maximum => $x</dt>
                    <dd>The length of this field must be at most (>=) $x</dd>
            </dl>
        </td>
        <td>This field's number of characters must be in the supplied
        range. If no options are passed, this field will always be valid</td>
    </tr>
    <tr>
        <td>Validation::numericality()</td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
                <dt>only_integer => true</dt>
                    <dd>Only whole integers are acceptable. If not
                    supplied, whole integers or floats are acceptable.</dd>
                <dt>even => true</dt>
                    <dd>Only even numbers are acceptable</dd>
                <dt>odd => true</dt>
                    <dd>Only odd numbers are acceptable</dd>
                <dt>equal_to => $x</dt>
                    <dd>The number must be equal to $x</dd>
                <dt>less_than => $x</dt>
                    <dd>The number must be less than $x</dd>
                <dt>less_than_or_equal_to => $x</dt>
                    <dd>The number must be less than or equal to $x</dd>
                <dt>greater_than => $x</dt>
                    <dd>The number must be greater than $x</dd>
                <dt>greater_than_or_equal_to => $x</dt>
                    <dd>The number must be greater than or equal to $x</dd>
            </dl>
        </td>
        <td>This field must be a number</td>
    </tr>
    <tr>
        <td>Validation::presence()</td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
            </dl>
        </td>
        <td>This field must not be empty</td>
    </tr>
    <tr>
        <td>Validation::url()</td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td>This field must be a valid url</td>
    </tr>
</table>

### Advanced Validations (require parameters)

<table>
    <tr>
        <th>Validation</th>
        <th>Parameter</th>
        <th>Options</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>Validation::confirmation($other_field_func)</td>
        <td>
            <dl>
                <dt>$other_field_func</dt>
                    <dd>A (callable) callback to match against. It's
                    return value will be type and value checked
                    (===) against this field</dd>
            </dl>
        </td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td>This field must match the return value of $other_field_func.
        Useful for confirming a password in a second field.</td> </tr>
    <tr>
        <td>Validation::exclusion($array)</td>
        <td>
            <dl>
                <dt>$array</dt>
                    <dd>A list of unacceptable values.</dd>
            </dl>
        </td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td> This field must not be equal (==) to a value inside
        $array.</td>
    </tr>
    <tr>
        <td>Validation::format($regex)</td>
        <td>
            <dl>
                <dt>$regex</dt>
                    <dd>The regex to match this field against</dd>
            </dl>
        </td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td>This field must match against the supplied $regex</td>
    </tr>
    <tr>
        <td>Validation::inclusion($array)</td>
        <td>
            <dl>
                <dt>$array</dt>
                    <dd>A list of acceptable values.</dd>
            </dl>
        </td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td> This field must be equal (==) to a value inside
        $array.</td>
    </tr>
    <tr>
        <td>Validation::validate_with($func)</td>
        <td>
            <dl>
                <dt>$func</dt>
                    <dd>A custom (callable) callback to match against.</dd>
            </dl>
        </td>
        <td>
            <dl>
                <dt>message => 'message'</dt>
                    <dd>Use a custom error message</dd>
                <dt>optional => true</dt>
                    <dd>Will accept a blank field. If this field is not
                    blank, will preform the validations.</dd>
            </dl>
        </td>
        <td>This validation allows for a custom function to preform the
        field validation. It's return value must be (===) true, or else
        it will use the return value as the field's error message</td>
    </tr>
</table>

#### Advanced Examples

##### Validation::confirmation($other_field_func)

```php
<?php
    // TestForm.php
    class TestForm extends Form{
        public function __construct() {
            $this->validations = array(
                'password' => Validation::confirmation(function() {
                    return $_POST['password_confirmation'];
                })
            );
        }
    }
?>
```

##### Validation::exclusion($array)

```php
<?php
    // TestForm.php
    class TestForm extends Form{
        public function __construct() {
            $this->validations = array(
                'usernames' => Validation::exclusion(array(
                    'admin',
                    'superuser'
                ))
            );
        }
    }
?>
```

##### Validation::format($regex)

```php
<?php
    // TestForm.php
    class TestForm extends Form{
        public function __construct() {
            $this->validations = array(
                'mp3Url' => Validation::format('/\.mp3$/')
            );
        }
    }
?>
```

##### Validation::inclusion($array)

```php
<?php
    class TestForm extends Form{
        public function __construct() {
            $this->validations = array(
                'usernames' => Validation::inclusion(array(
                    'Matt',
                    'Thor',
                    'Asa'
                ))
            );
        }
    }
?>
```

##### Validation::validate_with($func)

This validation requires a (callable) callback. This callback is
then provided with the submitted field data as it's only parameter. The
callback can either `return true` and the validation will pass, or
return anything else and the return will be used as the error message
for the field.

```php
<?php
    class TestForm extends Form {
        public function __construct() {
            $this->validations = array(
                'checkCustom' => Validation::validate_with(function($val) {
                    if ($val === 'supahSecret') {
                        return true;
                    }
                    return (substr($val, 0, 2) == 'st');
                })
            );
        }
    }
?>
```
