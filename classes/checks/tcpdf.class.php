<?php
namespace dfm;

class tcpdf extends check {

    function check() {
        $log = $this->log->info("Check TCPDF Presence");

        if (!is_dir($this->settings['lib_path'])) {
            return $log->danger('Lib Path ($this->settings[\'lib_path\']) is not existant or no folder.');
        }

        if (!file_exists($this->settings['lib_path'] . '/tcpdf/tcpdf.php')) {
            return $log->danger('TCPDF not found');
        }

        try {
            require_once($this->settings['lib_path'] . '/tcpdf/tcpdf.php');
            $tcpdf = new TCPDF();
        } catch (\Exception $e) {
            return $log->danger('Could not initialize tcpdf.');
        }

        if (file_exists($this->settings['lib_path'] . '/tcpdf/CHANGELOG.TXT')) {
            $f = fgets(fopen($this->settings['lib_path'] . '/tcpdf/CHANGELOG.TXT', 'r'));
            $this->log->info('TCPDf Version: ' . $f);
        }


        return $log->success('OK');


    }


}
?>