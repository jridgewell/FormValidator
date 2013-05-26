<form name="input" method="POST">
    <?php $form->error("user[name]", "What's your name?!"); ?>
    Please Enter your name*: <?php $form->input("user[name]"); ?><br/>

    <?php $form->error("user[age]"); ?>
    Please Enter your age*: <?php $form->input("user[age]", array('type' => 'number')); ?><br/>

    <?php $form->error("user[homepage]"); ?>
    Have a homepage?: <?php $form->input("user[homepage]"); ?><br/>

    <?php $form->submitButton("Submit");?>
</form>
