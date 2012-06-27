# FormValidator
FormValidator allows you to create and validate forms using a simple rule based approch.

## Basics

### Setting up your first form

A form file is just a class that extends the Form class
In this example, the form validator checks if `name` isn't empty

#### test.form.php

```php
<?php
    class TestForm extends Form {
        public $validation = array( // Contains a hash array of form elements
            "name" => VALIDATE_NOT_EMPTY // name field must contain something
        );
    }
?>
```


Then to use this form:

#### index.php

```php
<?php
    $form = new TestForm();

    /* Checks if the form has submitted then the form is checked for validation against the rules contained
       within the $validation array of TestForm returning the validated data if its successful
     */

    if($form->hasPosted() && ($data = $form->validate())) {
        // Form passes validation, use the $data for validated POST data

    } else {
        // Form hasn't posted or hasn't passed validation, so we load our html file
        require_once("form.html.php");
    }
?>
```

Now in our view:

#### form.html.php

```php
    <form name="input" method="POST">
    <?php $form->error("name", "There was an error"); ?>

    Please Enter your name: <?php $form->input("name"); ?><br/>
    <?php $form->submitButton("Submit");?>
    </form>
```

**Note:** If the form fails validation, by using the `$form->input` method, we preserve whatever value was in that field (except for password fields)


## The Validation Array (AKA. The Model)

The `$validation` array contains all the form fields and rules that need to pass, for the form to be valid.
In the example above, it showed a single rule applying to one form element, but you can apply multiple rules to an element by using an array.

```php
<?php
    class TestForm extends Form{
        public $validation = array(
            "name" => VALIDATE_NOT_EMPTY,
            "age"   => array( //Specifiy multiple rules
                VALIDATE_NOT_EMPTY,
                VALIDATE_NUMBER
            )
        );
    }
?>
```

In our html file, if we wanted to show different errors for the different age validations, we could do the following:

```php
<?php
    <form name="input" method="POST">
    <?php $form->error("name", "There was an error"); ?>

    Please Enter your name: <?php $form->input("name"); ?><br/>

    <?php $form->error("age", array(
        VALIDATE_NOT_EMPTY    => "Sorry, age can't be left empty",
        VALIDATE_NUMBER       => "Sorry, age has to be a number"
    )); ?>

    Please Enter your age: <?php $form->input("age"); ?><br/>
    <?php $form->submitButton("Submit");?>
    </form>
?>
```

### Validation Array Params
The `$validation` array also supports passing in parameters into the validation constants. This is done by using an array with the constant being the first value and the parameters being the rest of the array.

```php
<?php
    class TestForm extends Form {
        public $validation = array(
            "name" => VALIDATE_NOT_EMPTY, // name field must contain something
            "age"   => array(
                VALIDATE_NOT_EMPTY,
                VALIDATE_NUMBER,
                array(
                    VALIDATE_LENGTH,
                    "min"  => 0,
                    "max" => 100
                )
            )
        )
    }
?>
```


## List of `$validation` Array Constants


<table>
    <tr>
        <td>VALIDATE_DO_NOTHING:</td>
        <td>The field is always valid</td>
    </tr>
    <tr>
        <td>VALIDATE_NOT_EMPTY:</td>
        <td>The field must not be empty</td>
    </tr>
    <tr>
        <td>VALIDATE_NUMBER:</td>
        <td>The field must be all numbers</td>
    </tr>
    <tr>
        <td>VALIDATE_EMAIL:</td>
        <td>The field must be a valid email</td>
    </tr>
    <tr>
        <td>VALIDATE_TIMEZONE:</td>
        <td>The field must be a valid timezone</td>
    </tr>
    <tr>
        <td>VALIDATE_URL:</td>
        <td>The field must be a url</td>
    </tr>
</table>

Constants with parameters:

<table>
    <tr>
        <td>VALIDATE_IN_DATA_LIST:</td>
        <td>See Using Lists below</td>
    </tr>
    <tr>
        <td>VALIDATE_CUSTOM:</td>
        <td>The field value is checked against the provided callback. This takes the two parameters: the first being a valid PHP callback, and the second being the validation errorCode to raise if the callback returns false</td>
    </tr>
    <tr>
        <td>VALIDATE_LENGTH:</td>
        <td>The field must be between the provided values. This takes two optional parameters, "min" and "max"</td>
    </tr>
    <tr>
        <td>VALIDATE_MUST_MATCH_FIELD:</td>
        <td>The field must be the same value as another field (think checking passwords)</td>
    </tr>
    <tr>
        <td>VALIDATE_MUST_MATCH_REGEX:</td>
        <td>The field must match the regex provided</td>
    <tr>
</table>

## Advanced

### Using Lists
FormValidator can also generate and validate select lists. There are two methods to do this:

1. By suppling an array as VALIDATE_IN_DATA_LIST's parameter
2. Using the method `$form->addListData("FieldName", array(...));` and
   supplying no parameter to VALIDATE_IN_DATA_LIST

**Note:** The array can either be a hash of key/values or just values. If you pass in a hash, then the key will be returned.

For Example lets say we wanted to show and validate a list of usernames

test.form.php

```php
<?php
    class TestForm extends Form{
        public $validation = array( // Contains a hash array of form elements
                "usernames" => VALIDATE_IN_DATA_LIST
                );

        public function __construct(){
            $usernames = array(500 => "Matt", 300 => "Thor", 1 => "Asa", 5 => "Martina", 9 => "John", 12 => "Kate"); // Fetch our usernames from the database, with the keys being their userID
            $this->addListData("usernames", $usernames); // Add the list data
        }
    }
?>
```


Now whenever this form is used, it will have a list of usernames within it, to show this within a html page you can use Form::Select($fieldname [, $elementAttributes [, $values [, $useKeys])

```php
<?php
    $form->select("usernames"); // loads and displays the data from the stored data
?>
```

### Multiple Form Fields On One Page

If you want to use multiple form fields on one page, then all you have to do is use `Form::submitButton()` to generate the submit button. In your controller, use the `$form->isMe()` method to check if `$form` is form which triggered the POST.
