<?php


import('lib.pkp.classes.form.Form');

class selectToReFreshForm extends Form {
	/** @var string Name of parent plugin */
	var $parentPluginName;

	/** @var int ID of the object for review assignment */
	var $assignmentId;

	/** @var int ID of the object for review assignment */
	var $objectId;

	
	private $_plugin;
	private $_journalId;

	var $supportedTypes;

	/**
	 * selectToReFreshForm constructor.
	 * @param $plugin
	 * @param $journalId
	 */
	function selectToReFreshForm(&$plugin, $journalId) {
		$this->_plugin = $plugin;
		$this->_journalId = $journalId;
		$this->supportedTypes = \dfm\processor::supportedTypes;

        parent::Form($this->_plugin->getTemplatePath() . 'templates/selectToRefreshForm.tpl');
	}
	
	function display($request = null, $template = null) {;
		return parent::display($request, $template);
	}
	

	
	function readInputData() {
		$vars = array(
			'idlist' 	=> "str",
			'type' 		=> $this->supportedTypes,
			'replace' 	=> 'bool'
		);
		
		foreach ($vars as $k => $type) {
			$v = Request::getUserVar($k);
			if ($type == "int") {
				$v = (int) $v;
			}
			if (is_array($type)) {
				$v = (in_array($v, $type)) ? $v : null;
			}
			if ($type == "bool") {
				$v = $v == "on";
			}
			if ($v !== null) {
				$this->setData($k, $v);
			}
		}
	}
	
	

	
	
}

?>
