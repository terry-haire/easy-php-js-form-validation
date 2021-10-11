<link rel="stylesheet" href="style-feedback.css">
<link rel="stylesheet" href="style.css">

<div class="root">
    <?php
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);

        $INPUT_NAME = "email";
        $FORM_NAME = "myForm";
        $INPUT_RULES_FILE = "../rules/email.json";
        $PLACEHOLDER = "E-mail adres";
        $SUBMIT_ID = "login";

        require_once $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";

        // Load default template.
        $template = new \TerryHaire\EasyFormValidation\Template();

        $forms = new \TerryHaire\EasyFormValidation\Forms();

        // Register inputs.
        $forms->add($INPUT_NAME, new \TerryHaire\EasyFormValidation\Form($FORM_NAME, $INPUT_NAME, $template,
                    $INPUT_RULES_FILE));
        $forms->add("pw", new \TerryHaire\EasyFormValidation\Form($FORM_NAME, "password", $template,
                    "../rules/password.json"));

        // Set the current status of the inputs.
        $forms->set_status();

        if (isset($forms) && $forms->valideer()) {
            $email = $forms->get($INPUT_NAME)->get_input();
            $password = $forms->get("pw")->get_input();

            echo "<span id=\"logged_in\"><p>LOGGED IN AS</p><p>" . $email . "</p></span>";
        }
    ?>

    <p class="bold header">Log in</p>

    <form name=<?php echo $FORM_NAME ?> action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <input type="email" <?php $forms->get($INPUT_NAME)->init(); ?> placeholder=<?php echo $PLACEHOLDER ?> required>
        <?php $forms->get($INPUT_NAME)->print(); ?>

        <input type="password" <?php $forms->get("pw")->init(); ?> placeholder="Password" required>
        <?php $forms->get("pw")->print(); ?>

        <input id=<?php echo $SUBMIT_ID ?> class="submit-on bold" type="submit" value="Log in">

        <?php \TerryHaire\EasyFormValidation\for_get(["ref"]); ?>
    </form>
</div>


<script src=<?php echo \TerryHaire\EasyFormValidation\get_js_script_path() ?>></script>
<?php
    $forms->add_knop($SUBMIT_ID);
    $forms->init_js();
?>
