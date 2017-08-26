<?php

namespace dfm;

class check extends abstraction {
    function check() {
        $log = $this->log->log("Is there a proper Tmp folder?");

        // is set
        if (!isset($this->settings['tmp_path'])) {
            return $log->danger("No proper Tmp folder defined.");
        }

        // is directory
        if (!is_dir($this->settings['tmp_path'])) {
            return $log->danger("Tmp folder non-existant or no folder: " . $this->settings['tmp_path']);
        }

        // check writability
        if (!is_writable($this->settings['tmp_path'])) {
            return $log->danger("Tmp folder not writable: " . $this->settings['tmp_path']);
        }

        $freespace = disk_free_space($this->settings['tmp_path']);
        if ($freespace < 1024*1024*500) {
            $freespaceH = $this->_convertBytes($freespace);
            $this->log->warning("Free space in Tmp folder ({$this->settings['tmp_path']}) is only: $freespaceH bytes.");
        }

        $log->debug("Tmp folder OK (". $this->settings['tmp_path'] . ")");
    }
}
?>