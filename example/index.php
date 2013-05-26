<?php
    require_once("TestForm.php");
    $form = new TestForm();

    /* Checks if the form has submitted then the form is checked for validation against the rules contained
       within the $validations array of TestForm returning the validated data if its successful
     */
    if($form->isMe() && ($data = $form->validate())) {
        // Form passes validation, use the $data as the validated POST data

    } else {
        // Form hasn't posted or hasn't passed validation, so we load our html file
        require_once("form.html.php");
    }
?>
