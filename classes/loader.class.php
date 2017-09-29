<?php
namespace dfm;


class loader {

    const dependencies = '';

    public $settings = array();
    public $log;

    /* loads all other classes! */
    function load(&$logger, &$settings) {

        $dependenciesList = array();
        $warnings = array();
        
        try {

            if (!isset($settings->dependencies) or
                !is_array($settings->dependencies)) {
                    $settings->dependencies = array();
            }
            $firstLoad = false;
            if (!isset($settings->dependencies_resolved) or
                !is_array($settings->dependencies_resolved) or
                !count($settings->dependencies_resolved)) {
                    $settings->dependencies_resolved = array();
                    $firstLoad = true;
            }



            $classPaths = array(
                'classes'           => '/',
                'checks'            => '/checks/',
                'generators'        => '/generators/',
                'journalpresets'    => '/journalpresets/',
                'themes'            => '/themes/',
                'thumbnailmodes'    => '/thumbnailmodes/'
            );

            foreach ($classPaths as $classClass => $classPath) {
                $fullClassPath = $settings->dfm_path . '/classes' . $classPath;
                $registry[$classClass] = glob($fullClassPath . '*');
                foreach ($registry[$classClass] as $nr => $filename) {
                    if (!is_file($filename)) {
                        continue;
                    }

                    $classname = str_replace(array($fullClassPath, '.class.php'), '', $filename);

                    require_once($filename);

                    $fullclassname = "\dfm\\" . $classname;

                    if (class_exists($fullclassname)) {
                        $dependencies = (defined("$fullclassname::dependencies")) ?  explode('|', $fullclassname::dependencies) : array();
                        $dependencies = array_filter($dependencies, function ($elem) {return ($elem !== '');});
                        $dependenciesList = array_merge($dependenciesList, $dependencies);

                        // register class if $registerAll or if all dependencies where resolved last time

                        $carry = true;
                        if (!$firstLoad) {
                            foreach ($dependencies as $dependency) {
                                $carry = (isset($settings->dependencies_resolved[$dependency]) and $settings->dependencies_resolved[$dependency] and $carry);
                            }
                        }

                        if ($carry) {
                            $settings->registry[$classClass][] = $classname;
                        } else {
                            $warnings[] = "$classname skipped";
                        }

                    } else {
                        throw new \Exception("Error: Class [$fullclassname] not Found.");
                    }

                }
            }

            if (is_null($logger)) {
                $logger = new \dfm\logger();
                $logger->logTimestamps = false;
                $logger->import($warnings);
            }

            // settings dependencies contains a list of all necessary dependencies
            $settings->dependencies = (array_unique($dependenciesList));

            // if firstLoad, check for all collected dependencies for next time
            if ($firstLoad) {
                $checker = new \dfm\systemChecker($logger, $settings);
                $logger->log("System Check Required");
                $checker->check();
                //$logger->log($settings);
            }

            $this->settings = $settings;
            $this->log = $logger;

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