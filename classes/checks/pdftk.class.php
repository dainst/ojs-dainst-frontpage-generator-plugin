<?php

// checks if ojs2 is present

namespace dfm;

class pdftk extends check {
    function check() {

        $log = $this->log->info("Check pdftk presence");

        $shell = shell_exec("command -v pdftk >/dev/null 2>&1 || { echo \"NO\"; exit 1; }");
        $shell = (substr($shell, 0, 2) !== 'NO');

        if (!$shell) {
            $log->danger('not installed!');
            return false;
        }

        $shell = shell_exec("pdftk --version");

        $shell = trim(substr($shell, 0, 12));

        $log->info("Version $shell.");

        $log->success('OK');

        return true;

    }
}
?>