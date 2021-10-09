<?php
/**
 * @author Terry Haire
 *         https://github.com/terry-haire
 *         https://linkedin.com/in/terry-haire
 *
 * January 2019
 */


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
 * Deze class bevat alle stijlen voor condities bij initialisatie, goed en fout voor zowel javascript en noscript.
 * Het wordt gebruikt in de form class als om de juiste stijl voor condities op dat moment te bepalen.
 */
class Template {
    public $tag = "li";
    public $attributen = "";

    public $class_goed_js = "";
    public $class_init_js = "";
    public $class_fout_js = "";

    public $class_goed = "";
    public $class_init = "";
    public $class_fout = "";

    function __construct() {
        /** Load config JSON. */
        $json = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
        if (!$json) {
            throw new Exception("Error loading config json");
        }

        $classes = $json["classes"];

        $this->class_goed_js = $classes["valid"] . "_js";
        $this->class_init_js = $classes["init"] . "_js";
        $this->class_fout_js = $classes["invalid"] . "_js";

        $this->class_goed = $classes["valid"];
        $this->class_init = $classes["init"];
        $this->class_fout = $classes["invalid"];
    }
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
 * Deze class bevat een patroon, conditie en bericht die gebruikt worden voor het valideren en printen van condities.
 */
class Patroon {
    public $patroon = "";
    public $conditie = "";
    public $bericht = "";

    /**
     * Maak een nieuw Patroon object en set alle waarden.
     * @param patroon     Regex.
     * @param conditie    Naam van de conditie.
     * @param bericht     Bericht van de conditie.
     */
    function __construct($patroon, $conditie, $bericht) {
        if ($conditie) {
            $this->patroon = $patroon;
            $this->conditie = $conditie;
        } else {
            throw new Exception("Error: conditie niet aangegeven!!!");
        }

        if ($bericht) {
            $this->bericht = $bericht;
        }
    }

    /**
     * Valideer een string door met het patroon te vergelijken.
     * @param str De string om te valideren
     * @return boolean Wel of geen match (true/false).
     */
    public function valideer_patroon($str) {
        return preg_match($this->patroon, $str);
    }
}

/**
 * Deze class beschrijft de validiteit van een form. Het gebruikt hierbij de gegeven eigenschappen.
 */
class Form {
    private $min = -1;
    private $max = -1;
    private $required = true;
    private $patronen = [];
    private $form = "";
    private $naam = "";
    private $type = "";
    private $template;
    private $json = "";
    private $status = false;
    private $input = "";
    private $output = "";
    private $autofill = "";
    private $disabled = false;

    /**
     * Maak een nieuw form object.
     * Voor de zekerheid is het het beste om $naam als unieke prefix te houden zodat er geen elementen
     * zijn met dezelfde id/name.
     * @param form      Naam van de form
     * @param naam      Naam mag geen "-" bevatten! Gebruik als het kan enkele woorden om styling consistent te houden!
     * @param json_naam JSON Bestand naam bijv:"wachtwoord.json" (niet hele pad)
     */
    function __construct($form, $naam, $template, $json_naam) {
        /** Haal de gegeven JSON op als string. */
        $str = file_get_contents(__DIR__ . "/rules/" . $json_naam);
        if (!$str) {
            throw new Exception("Error bij het laden van het bestand!!!");
        }

        /** Decode de JSON. */
        $json = json_decode($str, true);
        if (!$json) {
            throw new Exception("Error bij het decoden van het bestand!!!");
        }

        /** Zet het type van de form met de waarde uit de JSON. */
        $this->type = $json['type'];
        if (!$this->type) {
            throw new Exception("Error: Type niet gedefineerd!!!");
        }

        /** Zet de minimale en maximale lengte van de input. */
        $this->min = $json['min'];
        $this->max = $json['max'];
        $this->controlleer_min_max();

        /** Bepaal of de form moet worden ingevuld. */
        $this->required = $json['required'];

        /** Controlleer dat er minstens 1 patroon is. */
        if (count($json['patronen']) < 1) {
            throw new Exception("Error: minstens 1 patroon!!!");
        }

        /** Loop over patronen en voeg ze aan de form toe. */
        for ($i = 0; $i < count($json['patronen']); $i++) {
            $patroon = $json['patronen'][$i];
            array_push($this->patronen, new Patroon($patroon['patroon'], $patroon['conditie'], $patroon['bericht']));
        }

        /** Zet de overige eigenschappen. */
        $this->naam = $naam;
        $this->template = $template;
        $this->json = $str;
        $this->form = $form;
    }

    /**
     * Initialiseer de javascript form validatie.
     */
    public function init_js() {
        $var_naam = $this->naam;

        /** Vervang de backslashes zodat de string goed wordt gelezen in javascript. */
        $json = str_replace(PHP_EOL, '', $this->json);
        $json = str_replace('\\', '\\\\\\\\', $json);

        $class_goed = $this->template->class_goed_js;
        $class_init = $this->template->class_init_js;
        $class_fout = $this->template->class_fout_js;

        /** Print de javascript uit. */
        echo <<<HTML
        <script>
            var $var_naam = new Form(
                '$this->naam',
                '$this->form',
                '$json',
                '$class_goed',
                '$class_init',
                '$class_fout');

            forms.add($var_naam);

            document.getElementById('$this->naam').addEventListener(
                    "input", function(){ $var_naam.valideer(); forms.valideer(); }, true);
        </script>\n
HTML;
    }

    /**
     * Print de form eigenschappen.
     */
    public function init() {
        echo $this->get_init();
    }

    /**
     * Haal de string met de form eigenschappen op.
     */
    public function get_init() {
        /** TODO: Voeg regex toe */
        $pattern = "." . "{" . $this->min . "," . $this->max . "}";

        /** Haal vorige waarde op of de autofill. Leeg veld? Print geen value attribuut. */
        $form_value = isset($_POST[$this->naam]) ? $_POST[$this->naam] : $this->autofill;
        if ($form_value !== "") {
            $form_value_str = "value=" . "'$form_value'";
        } else {
            $form_value_str = "";
        }

        /** Zet de disabled flag. */
        $disabled = "";
        if ($this->disabled) {
            $disabled = " disabled";
        }

        /** Print de eigenschappen. */
        return "id='$this->naam'" . " name='$this->naam'" . " pattern='$pattern'" . $form_value_str . $disabled;
    }

    /** Get de naam van de form. */
    public function get_naam() {
        return $this->naam;
    }

    /** Valideer de form. */
    public function valideer() {
        $resultaat = false;
        $str = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (empty($_POST[$this->naam])) {
                if (!$this->required) {
                    $resultaat = true;
                }
            } else {
                $str = test_input($_POST[$this->naam]);

                $this->print_teller(strlen($str), false);

                /** Geen && gebruiken voor als de eerste fout is */
                if ($this->valideer_lengte($str)) {
                    $resultaat = true;
                }

                if ($this->valideer_condities($str) && $resultaat) {
                    $resultaat = true;
                } else {
                    $resultaat = false;
                }

                $this->status = $resultaat;
                $this->input = $str;

                return $resultaat;
            }
        }

        $this->input = "";

        /** Print de init status. */
        $this->print_teller(0, true);
        if ($this->min == $this->max) {
            $this->write_exact($this->min, -1);
        } else {
            $this->write_min($this->min, -1);
            $this->write_max($this->max, -1);
        }

        /** Loop over patronen */
        for ($i = 0; $i < count($this->patronen); $i++) {
            $patroon = $this->patronen[$i];
            $this->write($patroon->bericht, $patroon->conditie, -1);
        }

        $this->status = $resultaat;

        return $resultaat;
    }

    /** Return de status van de form. */
    public function get_status() {
        return $this->status;
    }

    /** Return de input van de form. */
    public function get_input() {
        return $this->input;
    }

    /** Zet de autofill waarde */
    public function set_autofill($str) {
        $this->autofill = $str;
        /** Zet POST anders wordt de waarde niet opgenomen. */
        $_POST[$this->naam] = $this->autofill;
    }

    public function set_disabled() {
        $this->disabled = true;
    }

    /** Return of de form disabled is. */
    public function get_disabled() {
        return $this->disabled;
    }

    /** Print de condities van de form. */
    public function print() {
        echo $this->output;
    }

    /** Maak de conditie strings van de form. */
    public function get_print() {
        return $this->output;
    }

    /**
     * Print de lengte van de input.
     * @param len  Lengte van de input.
     * @param init Initialisatie(true) of niet(false).
     */
    private function print_teller($len, $init) {
        if ($this->type == "teller") {
            if ($init) {
                $this->write($len . "/" . $this->max, "-status-teller", -1);
            } else if ($len > $this->max || $len < $this->min) {
                $this->write($len . "/" . $this->max, "-status-teller", 0);
            } else {
                $this->write($len . "/" . $this->max, "-status-teller", 1);
            }
        }
    }

    /**
     * Valideer de lengte van de input.
     */
    private function valideer_lengte($str) {
        $len = strlen($str);
        $resultaat = true;

        if ($this->min == $this->max) {
            if ($len != $this->min) {
                $this->write_exact($this->min, 0);
                return false;
            } else {
                $this->write_exact($this->min, 1);
                return true;
            }
        }

        if ($len < $this->min) {
            $this->write_min($this->min, 0);
            $resultaat = false;
        } else {
            $this->write_min($this->min, 1);
        }

        if ($len > $this->max) {
            $this->write_max($this->max, 0);
            $resultaat = false;
        } else {
            $this->write_max($this->max, 1);
        }

        return $resultaat;
    }

    /**
     * Valideer de string met regex vergelijkingen gedefinieerd in
     * het bijbehorende JSON bestand.
     */
    private function valideer_condities($str) {
        $resultaat = true;

        /** Loop over patronen */
        for ($i = 0; $i < count($this->patronen); $i++) {
            $match = false;
            $patroon = $this->patronen[$i];

            if ($patroon->patroon == "") {
                /**
                 * Speciale types.
                 * */
                if ($this->type == "email") {
                    $match = filter_var($str, FILTER_VALIDATE_EMAIL);
                } else {
                    throw new Exception("Patroon is leeg maar heeft geen speciaal type");
                }
            } else {
                /** Vergelijk met de regex */
                $match = preg_match($patroon->patroon, $str);
            }

            /** Print het bericht. */
            if ($match) {
                $this->write($patroon->bericht, $patroon->conditie, 1);
            } else {
                $this->write($patroon->bericht, $patroon->conditie, 0);
                $resultaat = false;
            }
        }

        return $resultaat;
    }

    /**
     * Voeg de conditie aan de output waarde toe.
     * @param str       Het bericht van de conditie.
     * @param conditie  De id van de conditie.
     * @param status    De status van de conditie.
     */
    private function write($str, $conditie, $status) {
        if (!$this->disabled) {
            $this->output = $this->output . maak_output($this->template, $this->naam . $conditie, $str, $status);
        }
    }

    /**
     * Maak de output van de minimum conditie.
     * @param min    Het minimum.
     * @param status De status van de conditie.
     */
    private function write_min($min, $status) {
        $this->write("At least $min characters", "-status-min", $status);
    }

    /**
     * Maak de output van de maximum conditie.
     * @param max    Het maximum.
     * @param status De status van de conditie.
     */
    private function write_max($max, $status) {
        $this->write("At most $max characters", "-status-max", $status);
    }

    /**
     * Maak de output van de exacte lengte conditie.
     * @param len    De exacte lengte.
     * @param status De status van de conditie.
     */
    private function write_exact($len, $status) {
        $this->write("Exactly $len characters", "-status-exact", $status);
    }

    /**
     * Controlleer de minimum en maximum waardes van de form geldig zijn. Gooi een exception als ze niet geldig zijn.
     */
    private function controlleer_min_max() {
        if ($this->min > 0 && $this->max > 1) {
            if ($this->min > $this->max) {
                throw new Exception("Error min is groter dan max!!!");
            }
        } else {
            throw new Exception("Error min of max ongeldig of niet gedefineerd");
        }
    }
}

/**
 * Deze class bevat een collectie van Form objecten.
 */
class Forms {
    private $forms = [];
    private $knoppen = [];

    /**
     * Link een knop element aan de forms.
     * @param id Het ID van de knop.
     */
    function add_knop($id) {
        array_push($this->knoppen, $id);
    }

    /**
     * Voeg een form toe aan de class.
     * @param key  Key om bij de form te komen.
     * @param form het Form object.
     */
    function add($key, $form) {
        if (array_key_exists($key, $this->forms)) {
            throw new Exception("Error: key bestaat al");
        }

        $this->forms[$key] = $form;
    }

    /**
     * Return het Form object.
     * @param key Key van het Form object.
     */
    function get($key) {
        if (array_key_exists($key, $this->forms)) {
            return $this->forms[$key];
        }

        throw new Exception("Error: niet gevonden");
    }

    /**
     * Print het javascript gedeelte van de form validatie.
     */
    function init_js() {
        /** Maak het forms object in JS. */
        echo <<<HTML
        <script>
            forms = new Forms();
        </script>
HTML;

        /** Link alle knoppen in JS. */
        for ($i = 0; $i < count($this->knoppen); $i++) {
            $id = $this->knoppen[$i];
            echo <<<HTML
            <script>
                forms.addKnop("$id");
            </script>
HTML;
        }

        /** Initialiseer alle forms in JS. */
        foreach($this->forms as $x => $form) {
            if (!$form->get_disabled()) {
                $form->init_js();
            }
        }

        /** Voeg een event toe om de condities te controlleren na laden. */
        echo <<<HTML
        <script>
            window.onload = function() {
                forms.init();
            }
        </script>
HTML;
    }

    /**
     * Valideer de forms.
     */
    function set_status() {
        foreach($this->forms as $x => $form) {
            $form->valideer();
        }

        return true;
    }

    /**
     * Controlleer dat de forms allemaal goed zijn ingevuld.
     */
    function valideer() {
        foreach($this->forms as $x => $form) {
            if (!$form->get_status()) {
                // echo $form->get_naam() . " gefaald<br>";
                return false;
            }
        }

        return true;
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
