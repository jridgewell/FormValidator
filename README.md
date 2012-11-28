# FormValidator
FormValidator allows you to create and validate forms using a simple rule based approch.

## Basics
### Setting up your first form

A form file is just a class that extends the Form class
In this example, the form validator checks if `name` isn't empty

#### test.form.php (the model)
```php
<?php
    class TestForm extends Form {
        public $validation = array( // Contains a hash array of form elements
            "name" => VALIDATE_NOT_EMPTY // name field must contain something
        );
    }
?>
```

#### index.php (the controller)
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

#### form.html.php (the view)
```php
    <form name="input" method="POST">
    <?php $form->error("name", "There was an error"); ?>

    Please Enter your name: <?php $form->input("name"); ?><br/>
    <?php $form->submitButton("Submit");?>
    </form>
```

**Note:** If the form fails validation, by using the `$form->input` method, we preserve whatever value was in that field (except for password fields)


## The Validation Array
The `$validation` array contains all the form fields and rules that need to pass, for the form to be valid. In the example above, it showed a single rule applying to one form element, but you can apply multiple rules to an element by using an array.

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

Constants with parameters: (Examples below)

<table>
    <tr>
        <td>VALIDATE_IN_DATA_LIST:</td>
        <td>See below</td>
    </tr>
    <tr>
        <td>VALIDATE_CUSTOM:</td>
        <td>The field value is checked against the provided callback. This takes the at least two parameters: the first being a valid PHP callback, and the second being the validation errorCode to raise if the callback returns false. The call back must take two parameters, the first being the value of the field, the second an array with all the data from validation.</td>
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

### VALIDATE_IN_DATA_LIST
FormValidator can also generate and validate select lists. There are two methods to do this:

1. By suppling an array as VALIDATE_IN_DATA_LIST's list parameter
2. Using the method `$form->addListData("FieldName", array(...));` and
   supplying no list parameter to VALIDATE_IN_DATA_LIST

**Note:** The array can either be a hash of key/values or just values. If you pass in a hash, then the key will be returned.

For example lets say we wanted to show and validate a list of usernames

```php
<?php
    class TestForm extends Form{
        public $validation = array( // Contains a hash array of form elements
            "usernames" => array(
                VALIDATE_IN_DATA_LIST,
                'useKeys' => true,
                'list' => array(
                    500 => 'Matt',
                    300 => 'Thor',
                    1   => 'Asa'
                )
            )
        );
    }

    //....
    // Display errors for usernames
    $form->error('usernames' array(
        VALIDATE_IN_DATA_LIST => 'Not in list'
    ));
    // Display a select containing the usernames
    $form->select("usernames", null, array(
        500 => 'Matt',
        300 => 'Thor',
        1   => 'Asa'
    ), true);
?>
```

or

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

    //....
    // Display errors for usernames
    $form->error('usernames' array(
        VALIDATE_IN_DATA_LIST => 'Not in list'
    ));
    // Display a select containing the usernames
    // Select will be populated from $form->listData
    $form->select("usernames");
?>
```

### VALIDATE_CUSTOM
```php
<?php
    class TestForm extends Form {
        public $validation = array(
            "checkCustom" => array(
                array(
                    VALIDATE_CUSTOM,
                    "callback" => 'myCallback',
                    "param1"   => 'These will be passed',
                    "param2"   => 'to myCallback as $params'
                )
            )
        )

        public function myCallback($fieldValue, $params) {
            // This method must be public!
            // Check the submitted value ($fieldValue)
            // with the $params you specified in $validation
        }
    }
?>
```

### VALIDATE_LENGTH
```php
<?php
    class TestForm extends Form {
        public $validation = array(
            "length" => array(
                VALIDATE_NOT_EMPTY,
                array(
                    VALIDATE_LENGTH,
                    "min" => 0,
                    "max" => 100
                )
            )
        )
    }
?>
```

### VALIDATE_MUST_MATCH_FIELD
```php
<?php
    class TestForm extends Form {
        public $validation = array(
            "password"      => VALIDATE_NOT_EMPTY,
            "repassword"    => array(
                VALIDATE_NOT_EMPTY,
                array(
                    VALIDATE_MUST_MATCH_FIELD,
                    "field" => 'password'
                )
            )
        )
    }
?>
```

### VALIDATE_MUST_MATCH_REGEX
```php
<?php
    class TestForm extends Form {
        public $validation = array(
            "regCheck" => array(
                array(
                    VALIDATE_MUST_MATCH_REGEX,
                    "regex" => '/[a-zA-Z0-9]+/'
                )
            )
        )
    }
?>
```

### Multiple Form Fields On One Page

If you want to use multiple form fields on one page, then all you have to do is use `Form::submitButton()` to generate the submit button. In your controller, use the `$form->isMe()` method to check if `$form` is form which triggered the POST.
