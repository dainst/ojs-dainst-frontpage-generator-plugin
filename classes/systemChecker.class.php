<?php
namespace dfm;
class systemChecker extends abstraction {

    public $settings = array();

    function __construct($logger, $settings) {
	    parent::__construct($logger, $settings);
        $this->log->log('System Test Start');
    }

    function check() {

        if (!isset($this->settings['dependencies'])) {
            $this->log->warning('No dependencies found; this is most likely a Problem.');
            return;
        }

        $result = true;

        foreach ($this->settings['dependencies'] as $test) {
            $class_name = '\dfm\\' . $test;

            if (!class_exists($class_name)) {
                $this->log->danger("Necessary check could not be performed: $test");
                continue;
            }

            $test = new $class_name($this->log, $this->settings);
            $result = $result and $test->check();
        }


        if ($result) {
            $this->log->success("All System Checks passed.");
        } else {
            $this->log->danger("Some System Checks failed.");
        }

    }


}
?>
