<?php

namespace TerryHaire\EasyFormValidation;

require_once "helpers.php";

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
