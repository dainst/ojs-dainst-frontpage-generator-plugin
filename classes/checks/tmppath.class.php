<?php
namespace dfm;

class tmppath extends check {
    private function _convertBytes($bytes) {
        // from http://php.net/manual/en/function.disk-free-space.php
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
        return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
    }

    function check() {

        $log = $this->log->info("Check Tmp folder");

        if (!isset($this->settings['tmp_path'])) {
            return $log->danger('Tmp folder not set.');
        }

        $log->info($this->settings['tmp_path']);

        if (!is_dir($this->settings['tmp_path'])) {
            return $log->danger('is not existant or no folder.');
        }

        if (!is_writable($this->settings['tmp_path'])) {
            return $log->danger('is not writable.');
        }

        $freesspace = disk_free_space($this->settings['tmp_path']);

        if ($freesspace < 500 * 1024 *1024) {
            $this->log->warning("only $this->_convertBytes($freesspace) left on temp folder ({$this->settings['tmp_path']})");
        }

        return $log->success('OK');


    }
}
?>