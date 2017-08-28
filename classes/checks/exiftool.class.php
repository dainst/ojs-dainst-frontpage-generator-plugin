<?php

// checks if ojs2 is present

namespace dfm;

class exiftool extends check {
    function check() {

        $log = $this->log->info("Check Exiftool presence");

        $etpresent = shell_exec("command -v exiftool >/dev/null 2>&1 || { echo \"NO\"; exit 1; }");
        $etpresent = (substr($etpresent, 0, 2) !== 'NO');

        if (!$etpresent) {
            $log->danger('not installed!');
            return false;
        }

        $etpresent = shell_exec("exiftool -ver");

        $log->info("Version $etpresent.");

        $log->success('OK');

        return true;
        
    }
}
?>