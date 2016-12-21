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


	function selectToReFreshForm(&$plugin, $journalId) {
		$this->_plugin = $plugin;
		$this->_journalId = $_journalId;
		parent::Form($this->_plugin->getTemplatePath() . 'templates/selectToRefreshForm.tpl');
	}
	
	function display($request = null, $template = null) {	
		return parent::display($request, $template);
	}
	


	function execute() {

		var_dump($this->_data);
		return  $this->_plugin->startUpdateFrontapges($this->getData('id'), $this->getData('type'));	

	}
	
	function readInputData() {
		$vars = array(
			'id' => "int",
			'type' => array('galley')
		);
		foreach ($vars as $k => $type) {
			$v = Request::getUserVar($k);
			if ($type == "int") {
				$v = (int) $v;
			}
			if (is_array($type)) {
				$v = (in_array($v, $type)) ? $v : null;
			}
			
			if ($v !== null) {
				$this->setData($k, $v);
			}
		}
	}
	
	

	
	
}

?>
