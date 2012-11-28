<form name="input" method="POST">
<?php $form->error("name", "There was an error"); ?>

Please Enter your name: <?php $form->input("name"); ?><br/>
<?php $form->submitButton("Submit");?>
</form>