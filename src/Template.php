<?php

namespace TerryHaire\EasyFormValidation;

require_once "helpers.php";

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

    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct'.$numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    /**
     * Create the CSS template.
     * @param init       CSS classname when the input is initialized / empty / default.
     * @param invalid    CSS classname when the input is invalid.
     * @param valid      CSS classname when the input is valid.
     * @param init_js    CSS classname when javascript is enabled.
     * @param invalid_js CSS classname when javascript is enabled.
     * @param valid_js   CSS classname when javascript is enabled.
     */
    function __construct6($init, $invalid, $valid, $init_js, $invalid_js, $valid_js) {
        $this->class_init = $init;
        $this->class_fout = $invalid;
        $this->class_goed = $valid;

        $this->class_init_js = $init_js;
        $this->class_fout_js = $invalid_js;
        $this->class_goed_js = $valid_js;
    }

    /**
     * Load default classes from config.json.
     */
    function __construct0() {
        /** Load config JSON. */
        $json = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
        if (!$json) {
            throw new Exception("Error loading config json");
        }

        $classes = $json["classes"];

        $this->__construct6(
            $classes["init"],
            $classes["invalid"],
            $classes["valid"],
            $classes["init"] . "_js",
            $classes["invalid"] . "_js",
            $classes["valid"] . "_js",
        );
    }
}
