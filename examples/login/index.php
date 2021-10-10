<link rel="stylesheet" href="style-feedback.css">
<link rel="stylesheet" href="style.css">

<div class="root">
    <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        require_once $_SERVER['DOCUMENT_ROOT'] . "/src/EasyFormValidation.php";

        // Load default template.
        $template = new Template();
        $forms = new Forms();

        // Register inputs.
        $forms->add('email', new Form("myForm", "email", $template, "../rules/email.json"));
        $forms->add('password', new Form("myForm", "password", $template, "../rules/password.json"));

        // Set the current status of the inputs.
        $forms->set_status();

        if (isset($forms) && $forms->valideer()) {
            $email = $forms->get('email')->get_input();
            $password = $forms->get('password')->get_input();

            echo "<span id=\"logged_in\"><p>LOGGED IN AS</p><p>" . $email . "</p></span>";
        }
    ?>

    <p class="bold header">Log in</p>

    <form name="myForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <input type="email" <?php $forms->get('email')->init(); ?> placeholder="E-mail adres" required>
        <?php $forms->get('email')->print(); ?>

        <input type="password" <?php $forms->get('password')->init(); ?> placeholder="Password" required>
        <?php $forms->get('password')->print(); ?>

        <input id="login" class="submit-on bold" type="submit" value="Log in">

        <?php for_get(['ref']); ?>
    </form>
</div>


<script src="/src/EasyFormValidation.js"></script>
<?php
    $forms->add_knop("login");
    $forms->init_js();
?>
