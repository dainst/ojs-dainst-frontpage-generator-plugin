<pre>
<?php
require_once('classes/loader.class.php');

$loader = new \dfm\loader();

$logger = null;

$path = dirname(__FILE__);

$settings = array(
    'tmp_path'		=> '/somefolder',
    'lib_path'		=> $path . '/lib',
    'dfm_path'		=> $path,
    'files_path'	=> $path . '/classes/pdfWorker/files'
);

if (!$loader->load($logger, $settings)) {
    echo 'error loading dfm'; die();
}

$checker = new \dfm\systemChecker($logger, $settings);
$checker->check();

$logger->dumpLog(false, true);
?>
</pre>
