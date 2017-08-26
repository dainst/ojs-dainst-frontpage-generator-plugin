<?php
namespace dfm;


class abstraction {
    public $settings = array();

    const dependencies = 'xxx';

    public $log;

    function __construct($logger, $settings) {
        //include_once($this->_base_path  . 'settings.php');
        $this->settings = $settings;
        $this->log = $logger;

    }

    function bootstrap() {
        $dependenciesString = '';

        $classPaths = array(
            'classes' => '/',
            'checks' => '/checks/',
            'generators' => '/generators/',
            'journalpresets' => '/journalpresets/',
            'themes' => '/themes/',

        );

        foreach ($classPaths as $classClass => $classPath) {
            $fullClassPath = $this->pluginPath . '/classes' . $classPath;
            //echo "\n <h3>fullClassPath: $fullClassPath </h3>\n";
            $registry[$classClass] = glob($fullClassPath . '*');
            foreach ($registry[$classClass] as $filename) {
                if (!is_file($filename)) {
                    continue;
                }
                require_once($filename);
                $classname = "\dfm\\" . str_replace(array($fullClassPath, '.class.php'), '', $filename);

                $this->settings['registry'][$classClass][] = $classname;

                //echo "\n\n[$filename] -> [$classname]";
                if (class_exists($classname)) {
                    if (defined("$classname::dependencies")) {
                        //echo "\n<span style='color:green'> $classname: " . $classname::dependencies . '</span>';
                        $dependenciesString .= $classname::dependencies . '|';
                    } else {
                        //echo "\n<span style='color:yellow'> $classname: no dependencies</span>";
                    }
                } else {
                    //echo "\n<span style='color:red'>$classname: ERROR</span>";
                }

            }
        }


        //echo "\n\n<strong>$dependenciesString</strong>";
        $this->settings['dependencies'] = array_filter(array_unique(explode('|', $dependenciesString)), function($elem) {return ($elem !== '');});

        require_once('article_picker/article_picker.class.php');
    }

}
