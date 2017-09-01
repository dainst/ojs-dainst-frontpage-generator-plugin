<?php


namespace dfm;

class ojs2ui extends abstraction {

    const dependencies = "ojs2|ojs2picker";

    public $picker = false;

    function __construct($a, $b) {
        parent::__construct($a, $b);
    }

    function load() {
        $checker = new \dfm\systemChecker($this->log, $this->settings);

        if (!$checker->getAvailability('ojs2ui')) {
            return false;
        }

        require_once($this->settings->lib_path . '/article_picker/article_picker.class.php');

        $this->picker = new \das\article_selector(
            $this->settings->ojs_path . '/' . $this->settings->lib_path . '/article_picker',
            $this->settings->url . '/lib/article_picker'
        );
        $this->picker->setTemplateEnvironment();

        return $this->picker;

    }

    function handleApiCall() {
        if (!$this->picker) {
            $return = array(
                'success'	=> false,
                'message'	=> 'OJS2 Web-API not present',
            );
            header('Content-Type: application/json');
            echo json_encode($return);
            die();
        }

        define('OJS_PRESENT', true);
        $includePath = $this->settings->ojs_path . '/' . $this->settings->lib_path . '/article_picker/webapi/';
        include($this->settings->lib_path . "/php_default_api/index.php");
        die();

    }


}
?>