<?php
    require_once("test.form.php");
    $form = new TestForm();

    /* Checks if the form has submitted then the form is checked for validation against the rules contained
       within the $validation array of TestForm returning the validated data if its successful
     */
    if($form->isMe() && ($data = $form->validate())) {
        // Form passes validation, use the $data for validated POST data

    } else {
        // Form hasn't posted or hasn't passed validation, so we load our html file
        require_once("form.html.php");
    }
?>