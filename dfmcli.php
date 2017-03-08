<?php
/**
 * call it from cli (as the importer will do)
 * 
 * 
 */

/*
error_reporting(E_ALL);
ini_set('display_errors', 'on');
//*/

require('tools/bootstrap.inc.php');

class dfmcli extends CommandLineTool {
	
	var $plugin;
	var $command;
	var $type;
	var $idlist;	
	var $commands = array('update', 'add', 'test');
	var $types = array('galley', 'article', 'journal', 'missing');
	

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

		if ($this->command == 'test') {
			$this->test();
			return;
		}
		
		$this->type = array_shift($this->argv);
		if (!$this->type or !in_array($this->type, $this->types)) {
			return $this->help("type not found: '{$this->type}'");
		}
		
		$idlist = array_shift($this->argv);
		$idlist = array_map('trim', explode(',', $idlist));
		$idlist = array_filter($idlist, 'is_numeric');
		if (!$idlist or !count($idlist)) {
			return $this->help("id-list not okay: " . print_r($idlist,1));
		}
		$this->idlist = $idlist;
		
		$this->go();
		
	}
	
	function go() {
		$plugin = PluginRegistry::getPlugin('generic', 'dfm');
		$plugin->isCli = true;
		$plugin->getFrontpageCreator();
		$plugin->startUpdateFrontpages($this->idlist, $this->type, $this->command == 'update', true);
	}

	function test() {
		$plugin = PluginRegistry::getPlugin('generic', 'dfm');
		$plugin->isCli = true;
		$plugin->getFrontpageCreator();
		$plugin->startTestRun();
	}
	

	function __destruct() {
		echo "\n";
	}
	
	function help($err) {
		echo "Error: $err";
		echo "\nusage: <" . implode("|", $this->commands) . '> <' . implode("|", $this->types) . '> <comma-separated-list>';
	}

}

$tool = new dfmcli(isset($argv) ? $argv : array());
?>
