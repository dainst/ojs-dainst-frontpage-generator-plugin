<?php
namespace dfm;


class abstraction {
    public $settings = array();

    const dependencies = 'xxx';

    public $log;

    function __construct($logger, $settings) {
        //include_once($this->_base_path  . 'settings.php');
        $this->settings = $settings;
        $this->log = $logger;

    }

}
