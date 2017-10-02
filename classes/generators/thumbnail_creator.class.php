<?php
namespace dfm;
class thumbnail_creator extends generator {

    const dependencies = 'convert|tmppath';
    const title = 'Thumpnail creator';

    function createThumbnail($fileToUpdate, $meta) {
        $this->log->log($fileToUpdate);

        if (!isset($this->settings->thumbMode) or ($this->settings->thumbMode == 'none')) {
            throw new \Exception("No proper Thumnail Mode set: " . print_r($this->settings->thumbMode,1));
            return "";
        }

        $classname = "\dfm\\" . $this->settings->thumbMode;

        if (!class_exists($classname)) {
            throw new \Exception("Class $classname does not exist");
            return "";
        }

        $mode = new $classname($this->log, $this->settings);

        return $mode->createThumbnail($this, $fileToUpdate);
    }




    function createImages($inputfile, $pages) {
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