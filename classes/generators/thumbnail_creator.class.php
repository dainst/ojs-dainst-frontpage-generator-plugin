<?php
namespace dfm;
class thumbnail_creator extends generator {

    const dependencies = 'convert|tmppath';
    const title = 'Thumpnail creator';

    function createThumbnail($fileToUpdate, $meta) {
        $this->log->log($fileToUpdate);
        return $this->_createThumbnail_page1($fileToUpdate);
    }

    private function _createThumbnail_page0($fileToUpdate) {
        $files =  $this->_createImages($fileToUpdate, array(0));
        return isset($files[0]) ? $files[0] : '';
    }

    private function _createThumbnail_page1($fileToUpdate) {
        $files =  $this->_createImages($fileToUpdate, array(1));
        return isset($files[0]) ? $files[0] : '';
    }

    private function _createImages($inputfile, $pages) {
        $names = [];
        $tmp_folder = $this->settings->tmp_path . '/';
        $outputfile = md5(microtime() . rand());

        foreach ($pages as $page) {
            $name = (count($pages) > 1) ? "{$tmp_folder}$page.$outputfile.jpg" : "{$tmp_folder}$outputfile.jpg";
            $names[] = $name;
            $shell = "convert -quality 100 -thumbnail x300 -flatten $inputfile\[$page\] $name 2>&1";
            $return = shell_exec($shell);
            if ($return != '') {
                $this->log->warning('Thumbnail could not be created: ' . $return);
                continue;
            }
            $this->log->log('Thumbnail created: ' . $name);
        }
        return $names;
    }

}

?>