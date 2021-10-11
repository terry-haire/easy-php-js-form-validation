<?php

namespace TerryHaire\EasyFormValidation;

require_once "helpers.php";

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
