# Easy Form Validation for PHP and JS

## Overview

Form validation with PHP and JS made easy! Simple is best, for quick development, user experience, and especially for security! All the basic security measures are taken care of (slashes, special characters). Just make sure to use the correct regex, and to access your database securely. By seperating the conditions from the code, you won't have to write and maintain the same thing twice in JS and PHP. You also won't have to write nearly as much code, significantly reducing the prevalence of bugs and security issues. This also works with Javascript disabled!

## Usage

1. Define the rules for the input in a JSON file inside `form-validatie` with the following fields. Or use one of the included json files I used for a webshop project.

* `TYPE <str>` - HTML form type e.g. `text`, `email`, etc.
* `MINIMUM_LENGTH <int>` - Minimum input length.
* `MAXIMUM_LENGTH <int>` - Maximum input length.
* `REQUIRED <bool>` - Fails if not filled in.
* `REGEX_PATTERN <str>` - Regex pattern to match.
* `CONDITION_IDENTIFIER <str>` - id suffix for further javascript access.
* `VALID_INPUT_DESCRIPTION <str>` - Description of valid input for the end user.

```
{
    "type":"<TYPE>",
    "min":<MINIMUM_LENGTH>,
    "max":<MAXIMUM_LENGTH>,
    "required":<REQUIRED>,
    "patronen":[
        {
            "patroon":"<REGEX_PATTERN>",
            "conditie":"<CONDITION_IDENTIFIER>",
            "bericht":"<VALID_INPUT_DESCRIPTION>"
        }
    ]
}
```
2. Add the following before the form to initialize all required objects and to process POST requests:
* `INPUT_NAME <str>` - This will be the name attribute of the form. Make it unique.
* `FORM_NAME <str>` - Name of the form being used. Don't use any characters like '`-`' that don't work for variables in JS.
* `INPUT_RULES_FILE <str>` - Filename containing the rules.
* `PLACEHOLDER <str>` - HTML placeholder attribute value.
* `SUBMIT_ID <str>` - ID of the submission button.
```
<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/form-validatie/functies.php";

    $template = new Template();
    $forms = new Forms();

    // Register an input.
    $forms->add('<INPUT_NAME>', new Form("<FORM_NAME>", "<INPUT_NAME>", $template, "<INPUT_RULES_FILE>"));

    // [No JS] Check the submitted form.
    $forms->set_status();

    if (isset($forms) && $forms->valideer()) {
        // Get input value.
        $value = $forms->get('<INPUT_NAME>')->get_input();

        // Do whatever needs to be done with the input.
    }
?>
```

3. Paste the following snippet to create the input for the end user.
```
<form name="<FORM_NAME>" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    // Create the actual input in HTML. The init function adds all the required attributes.
    <input type="<INPUT_TYPE>" <?php $forms->get('<INPUT_NAME>')->init(); ?> placeholder="<PLACEHOLDER>" required>

    // Write the input conditions in HTML for the end user.
    <?php $forms->get('<INPUT_NAME>')->print(); ?>

    // Your submission button.
    <input id="<SUBMIT_ID>" type="submit">

    // [No JS] Keep get values on form submission.
    <?php for_get(['ref']); ?>
</form>
```
4. Add the following at the end to automatically create the Javascript and make the form dynamic.
```
<script src="/form-validatie/functies.js"></script>
<?php
    // Register the submission button.
    $forms->add_knop("<SUBMIT_ID>");

    $forms->init_js();
?>
```
