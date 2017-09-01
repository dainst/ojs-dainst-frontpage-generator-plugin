<?php

// checks if ojs2-ui parts are

namespace dfm;

class ojs2picker extends check {

    function check() {
        if (!is_dir($this->settings->lib_path)) {
            $this->log->danger("Lib Path ({$this->settings->lib_path}) is not existant or no folder.");
            return false;
        }

        if (!file_exists($this->settings->lib_path . '/article_picker/article_picker.class.php')) {
            $this->log->danger('ojs2 article picker not found');
            return false;
        }
        return true;
    }

}