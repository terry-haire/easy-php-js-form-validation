<?php

namespace TerryHaire\EasyFormValidation;

require_once "helpers.php";

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
     * @param json_path JSON path
     */
    function __construct($form, $naam, $template, $json_path) {
        /** Haal de gegeven JSON op als string. */
        $str = file_get_contents($json_path);
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

                $resultaat = false;

                if ($this->valideer_lengte($str) & $this->valideer_condities($str)) {
                    $resultaat = true;
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
