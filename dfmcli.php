<?php
/**
 * call it from cli (as the importer will do)
 * 
 * works with OJS2
 */

/*
error_reporting(E_ALL);
ini_set('display_errors', 'on');
//*/

// load OJS2
if (!file_exists('tools/bootstrap.inc.php')) {
    echo ("Error: You must run this from OJS2 root path.\n");
    die();
}

require('tools/bootstrap.inc.php');

// load dfm stuff
require_once(dirname(__FILE__) . '/classes/abstraction.class.php');
require_once(dirname(__FILE__) . '/classes/processor.class.php');


//require_once('classes/processor.class.php');

class dfmcli extends CommandLineTool {
	
	var $plugin;
	var $command;
	var $thumbnails = false;
	var $type;
	var $idlist;	
	var $commands = array('update', 'add', 'keep', 'create', 'replace'); // supports update and add wich are synonym to create and replace due to compatibility reasons
	var $types = \dfm\processor::supportedTypes;

	/**
	 * Constructor.
	 * @param $argv array command-line arguments (see usage)
	 */
	function __construct($argv = array()) {

		parent::CommandLineTool($argv);

		$this->command = array_shift($this->argv);
		if (!$this->command or !in_array($this->command, $this->commands)) {
			return $this->help("command not not found: '{$this->command}'");
		}

		$this->thumbnails = ($this->argv[0] == 'thumbnails');
        if ($this->thumbnails) {
            array_shift($this->argv);
		}

		$this->type = array_shift($this->argv);
		if (!$this->type or !in_array($this->type, $this->types)) {
			return $this->help("type not not found: '{$this->type}'");
		}


		$idlist = array_shift($this->argv);
		$idlist = array_map('trim', explode(',', $idlist));
		$idlist = array_filter($idlist, 'is_numeric');
		if (($this->type != 'missing') and (!$idlist or !count($idlist))) {
			return $this->help("id-list not okay: " . print_r($idlist,1));
		}
		$this->idlist = $idlist;
		
		$this->go();
		
	}
	
	function go() {
		$plugin = PluginRegistry::getPlugin('generic', 'dfm');

		try {
            $plugin->loadDfm();
        } catch (Exception $e) {
            $this->help($e->getMessage());
        }
		$plugin->settings->doFrontmatters = 'keep';
		$plugin->settings->doFrontmatters = in_array($this->command, array('update', 'replace')) ? 'replace' : $plugin->settings->doFrontmatters;
		$plugin->settings->doFrontmatters = in_array($this->command, array('add', 'create')) ? 'create' : $plugin->settings->doFrontmatters;
		$plugin->settings->doThumbnails = $this->thumbnails;
		$plugin->settings->checkPermission = false; //!
        $plugin->startUpdateFrontpages($this->idlist, $this->type, true);


	}
	

	function __destruct() {
		echo "\n";
	}
	
	function help($err) {
		echo "Error: $err";
		//echo "available commands:\n * " . implode("\n * ", $this->commands);
		echo "\nusage: <" . implode("|", $this->commands) . '> <' . implode("|", $this->types) . '> <comma-separated-list>';
	}

}

$tool = new dfmcli(isset($argv) ? $argv : array());
?>
