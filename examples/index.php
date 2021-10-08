<link rel="stylesheet" href="/examples/style.css">
<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/form-validatie/functies.php";

    $template = new Template();
    $forms = new Forms();

    $forms->add('email', new Form("myForm", "email", $template, "email.json"));
    $forms->add('wachtwoord', new Form("myForm", "wachtwoord", $template, "wachtwoord.json"));

    $forms->set_status();

    if (isset($forms) && $forms->valideer()) {
        $email = $forms->get('email')->get_input();
        $wachtwoord = $forms->get('wachtwoord')->get_input();

        echo "LOGGED IN";
    }
?>

<h1>Log in</h1>

<form name="myForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    <input type="email" <?php $forms->get('email')->init(); ?> placeholder="E-mail adres" required><br>
    <?php $forms->get('email')->print(); ?>

    <input type="password" <?php $forms->get('wachtwoord')->init(); ?> placeholder="Wachtwoord" required><br>
    <?php $forms->get('wachtwoord')->print(); ?>

    <!-- Inloggen -->
    <input id="login" class="submit-aan" type="submit" value="Log in"><br>

    <?php for_get(['ref']); ?>
</form>


<script src="/form-validatie/functies.js"></script>
<?php
    $forms->add_knop("login");
    $forms->init_js();
?>
