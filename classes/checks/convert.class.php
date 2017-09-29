<?php

// checks if convert is present

namespace dfm;

class convert extends check {
    function check() {
        $log = $this->log->info("Check ImageMagick presence");

        $etpresent = shell_exec("command -v convert >/dev/null 2>&1 || { echo \"NO\"; exit 1; }");
        $etpresent = (substr($etpresent, 0, 2) !== 'NO');

        if (!$etpresent) {
            $log->danger('not installed!');
            return false;
        }

        $shell = shell_exec("convert --version");
        preg_match_all('/Version\:.(.*)http\:\/\/www.*/m', $shell, $matches, PREG_SET_ORDER, 0);

        $version =  $matches[0][1];

        $log->info("Version $version.");

        $log->success('OK');
        return true;
    }
}