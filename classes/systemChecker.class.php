<?php
namespace dfm;
class systemChecker extends abstraction {

    public $settings = array();

    function __construct(&$logger, &$settings) {
	    parent::__construct($logger, $settings);

    }

    function check() {

        $this->log->log('System Test Start');

        if (!isset($this->settings->dependencies)) {
            $this->log->warning('No dependencies found; system check could not be performed');
            return;
        }

        $this->settings->dependencies_resolved = array();

        $result = true;

        foreach ($this->settings->dependencies as $test) {
            $class_name = '\dfm\\' . $test;

            try {

                if (!class_exists($class_name)) {
                    throw new \Exception("not found");
                }

                $module = new $class_name($this->log, $this->settings);
                $passed = true;
                if (is_a($module, '\dfm\check')) {
                    $passed = $module->check();
                } else {
                    $name = defined("$class_name::title") ? $class_name::title : $class_name;
                    $this->log->success("Check for module $name ... OK");
                }

            } catch(\Exception $e) {
                $this->log->danger("Check for module $class_name could not be loaded: " . $e->getMessage());
                $passed = false;
            }
            $this->settings->dependencies_resolved[$test] = (bool) $passed;

            $result = ($result and $passed);
        }

        if ($result) {
            $this->log->success("All System Checks passed.");
        } else {
            $this->log->warning("Some System Checks failed.");
        }


        return $result;

    }

    function getTitle($obj) {
        $classname = "\\dfm\\" . $obj;
        if (!class_exists($classname)) {
            return "[error: class $classname not found]";
        }
        echo defined("$classname::title") ? $classname::title : $obj;
    }

    function getAvailability($obj) {

        $class = "\\dfm\\" . $obj;

        if (!isset($this->settings->dependencies_resolved)) {
            $this->log->warning('Dependency check of ' . $class . ' failed.');
            return true;
        }

        if (!class_exists($class)) {
            return false;
        }

        $sum = true;
        foreach(explode('|', $class::dependencies) as $dep) {
            $sum = ($sum and (bool) $this->settings->dependencies_resolved[$dep]);
        }
        return $sum;
    }


}
?>
