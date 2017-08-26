<?php
namespace dfm;


class loader {

    public $log = null;

    const dependencies = '';

    /* loads all other classes! */
    function load(&$logger, &$settings) {
        
        try {

            $dependenciesString = '';

            $classPaths = array(
                'classes' => '/',
                'checks' => '/checks/',
                'generators' => '/generators/',
                'journalpresets' => '/journalpresets/',
                'themes' => '/themes/',

            );

            foreach ($classPaths as $classClass => $classPath) {
                $fullClassPath = $settings['dfm_path'] . '/classes' . $classPath;
                //echo "\n <h3>fullClassPath: $fullClassPath </h3>\n";
                $registry[$classClass] = glob($fullClassPath . '*');
                foreach ($registry[$classClass] as $filename) {
                    if (!is_file($filename)) {
                        continue;
                    }
                    require_once($filename);
                    $classname = "\dfm\\" . str_replace(array($fullClassPath, '.class.php'), '', $filename);

                    $settings['registry'][$classClass][] = $classname;

                    if (class_exists($classname)) {
                        if (defined("$classname::dependencies")) {
                            $dependenciesString .= $classname::dependencies . '|';
                        } else {
                        }
                    } else {
                        throw new \Exception("PHP Error: Class [$classname] not Found.");
                    }

                }
            }

            $settings['dependencies'] = array_filter(array_unique(explode('|', $dependenciesString)), function ($elem) {
                return ($elem !== '');
            });

            if (is_null($logger)) {
                $logger = new \dfm\logger();
                $logger->logTimestamps = false;
            }
            return true;
        } catch (\Exception $e) {

            if (is_null($logger)) {
                require_once('logger.class.php');
                $logger = new \dfm\logger();
                $logger->logTimestamps = false;
                $logger->danger($e->getMessage());
            }

            return false;
        }

    }
}
?>