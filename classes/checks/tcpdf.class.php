<?php
namespace dfm;

class tcpdf extends check {

    function check() {
        $log = $this->log->info("Check TCPDF Presence");

        if (!is_dir($this->settings['lib_path'])) {
            $log->danger('Lib Path ($this->settings[\'lib_path\']) is not existant or no folder.');
            return false;
        }

        if (!file_exists($this->settings['lib_path'] . '/tcpdf/tcpdf.php')) {
            $log->danger('TCPDF not found');
            return false;
        }

        try {
            require_once($this->settings['lib_path'] . '/tcpdf/tcpdf.php');
            $tcpdf = new \TCPDF();
        } catch (\Exception $e) {
            $log->danger('Could not initialize tcpdf.');
            return false;
        }

        if (file_exists($this->settings['lib_path'] . '/tcpdf/CHANGELOG.TXT')) {
            $f = fgets(fopen($this->settings['lib_path'] . '/tcpdf/CHANGELOG.TXT', 'r'));
            $log->info('Version: ' . $f);
        }

        $log->success('OK');
        return true;


    }


}
?>