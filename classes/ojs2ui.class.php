<?php


namespace dfm;

class ojs2ui extends abstraction {

    const dependencies = "ojs2|ojs2picker";

    function __construct($a, $b) {

        parent::__construct($a, $b);

    }

    function load() {
        $check = new \dfm\ojs2picker($this->log, $this->settings);

        if (!$check->check()) {
            return false;
        }

        require_once($this->settings['lib_path'] . '/article_picker/article_picker.class.php');

        $picker = new \das\article_selector($this->settings['full_path'] . '/lib/article_picker/', $this->settings['full_url']);
        $picker->setTemplateEnvironment();

        return $picker;

    }


}
?>