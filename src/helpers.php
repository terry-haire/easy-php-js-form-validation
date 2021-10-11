<?php
/**
 * @author Terry Haire
 *         https://github.com/terry-haire
 *         https://linkedin.com/in/terry-haire
 *
 * January 2019
 */


namespace TerryHaire\EasyFormValidation;

/**
 * Bron: W3Schools
 * Verwijder leading en trailing whitespace en maak de string veilig.
 * @param data De gegevens om veilig te maken.
 * @return data De verwerkte gegevens.
 */
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Vergelijk de lengte van de string met de gegeven minimum lengte.
 * @param str De string om mee te vergelijken.
 * @param min De minimum lengte.
 * @return int 0 als kleiner dan min, anders 1.
 */
function min_length($str, $min) {
    if (strlen($str) < $min) {
        return 0;
    }

    return 1;
}

/**
 * Vergelijk de lengte van de string met de gegeven maximum lengte.
 * @param str De string om mee te vergelijken.
 * @param max De maximum lengte.
 * @return int 0 als groter dan max, anders 1.
 */
function max_length($str, $max) {
    if (strlen($str) > $max) {
        return 0;
    }

    return 1;
}

/**
 * Vergelijk de lengte van de string met de gegeven exacte lengte.
 * @param str De string om mee te vergelijken.
 * @param len De exacte lengte.
 * @param int 0 als niet gelijk, anders 1.
 */
function exact_length($str, $len) {
    if (strlen($str) != $len) {
        return 0;
    }

    return 1;
}

/**
 * Maak de html voor condities als een string met gebruikt van de gegeven template.
 * @param template  Template.
 * @param id        Element id.
 * @param content   Content van de tag.
 * @param conditie  Status van de conditie. -1: init, 0: false, 1: true.
 * @return html     return de print-ready html.
 */
function maak_output($template, $id, $content, $conditie) {
    $output = "";

    /** Maak de opening. */
    $output = "<" . $template->tag . ' id="' . $id . '"' . ' name="' . $id . '"';

    /** Bepaal de class. */
    $class = $class_js = "";
    if ($conditie == -1) {
        $class    = $template->class_init;
        $class_js = $template->class_init_js;
    } else if ($conditie) {
        $class    = $template->class_goed;
        $class_js = $template->class_goed_js;
    } else {
        $class    = $template->class_fout;
        $class_js = $template->class_fout_js;
    }

    /** Content en eind class. */
    $output    .= ' class="' . $class    . '"' . ">" . $content . "</" . $template->tag . ">";

    /** Return html. */
    return <<<HTML
    <!-- No script. -->
    $output\n
    <!-- Script. -->
    <script>
        document.getElementById("$id").classList.remove("$class");
        document.getElementById("$id").classList.add("$class_js");
    </script>\n
HTML;
}


/**
 * Bron: https://stackoverflow.com/questions/7201124/how-to-keep-already-set-get-parameter-values-on-form-submission
 * Herhaal de get waarden na form submission.
 * @param keys Array van keys van de get variabelen
 */
function for_get($keys) {
    // $keys = array('name', 'lName', ...);
    foreach($keys as $name) {
        if(isset($_GET[$name])) {
            $value = htmlspecialchars($_GET[$name]);
            $name = htmlspecialchars($name);
            echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
        }
    }
}

/**
 * Valideer de gelacht form
 * @param naam Form naam
 */
function valideer_geslacht($naam) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST[$naam])) {
        $str = test_input($_POST[$naam]);
        if ($str == "Dhr." || $str == "Mevr.") {
            return true;
        }
    }

    return false;
}

/**
 * Return het geslacht.
 * @param naam Form naam
 */
function get_geslacht($naam) {
    return test_input($_POST[$naam]);
}

/**
 * Controlleer de checkbox
 * @param naam Form naam
 */
function check_check($naam) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST[$naam])) {
            return false;
        } else {
            $str = test_input($_POST[$naam]);
            if ($str == "on") {
                return true;
            }
        }
    }

    return false;
}

/**
 * Vergelijk form a en b
 * @param a Form naam van a
 * @param b Form naam van b
 */
function vergelijk_form ($a, $b) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST[$a]) || empty($_POST[$b])) {
            return false;
        } else {
            $str_a = test_input($_POST[$a]);
            $str_b = test_input($_POST[$b]);

            if ($str_a == $str_b) {
                return true;
            }
        }
    }

    return false;
}

// https://stackoverflow.com/questions/4517067/
function get_js_script_path() {
    $prefix = $_SERVER["DOCUMENT_ROOT"];
    $str = __DIR__ . "/EasyFormValidation.js";

    if (substr($str, 0, strlen($prefix)) == $prefix) {
        $str = substr($str, strlen($prefix));
    }

    return $str;
}
