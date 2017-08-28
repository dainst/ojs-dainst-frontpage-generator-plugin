<?php

// checks if ojs2 is present

namespace dfm;

class ojs2 extends check {

    function check() {

        $msg = 'No OJS2 environment!';

        if (!class_exists('\PKPApplication')) {
            $this->log->danger($msg);
            return false;
        }

        $application =& \PKPApplication::getApplication();

        if ($application->getName() != 'ojs2') {
            $this->log->danger($msg . '(PKP application is ' . $application->getName() . ')');
            return false;
        }

        return true;


    }

}
