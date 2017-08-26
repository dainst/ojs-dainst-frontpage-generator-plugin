<?php
namespace dfm;
class systemChecker extends abstraction {

    public $settings = array();

    function __construct($logger, $settings) {
	    parent::__construct($logger, $settings);
        $this->log->log('System Test Start');
    }

    function check() {
        foreach ($this->settings['dependencies'] as $test) {
            $class_name = '\dfm\\' . $test;

            if (!class_exists($class_name)) {
                $this->log->danger("Necessary check could not be performed: $test");
                continue;
            }

            $test = new $class_name($this->log, $this->settings);
            $test->check();
        }
    }


}
?>
