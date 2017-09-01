<?php


import('lib.pkp.classes.form.Form');

class settingsForm extends Form {
	/** @var string Name of parent plugin */
	var $parentPluginName;

	/** @var int ID of the object for review assignment */
	var $assignmentId;

	/** @var int ID of the object for review assignment */
	var $objectId;

	
	private $_plugin;
	private $_journalId;

	/**
	 * constructor.
	 * @param $plugin
	 * @param $journalId
	 */
	function __construct(&$plugin, $journalId) {
		$this->_plugin = $plugin;
		$this->_journalId = $journalId;
        parent::Form($this->_plugin->getTemplatePath() . 'templates/settingsForm.tpl');
	}
	
	function display($request = null, $template = null) {
        $templateMgr =& TemplateManager::getManager();
        $templateMgr->assign($this->_data);
        $templateMgr->assign('isError', !$this->isValid());
        $templateMgr->assign('errors', $this->getErrorsArray());
        return parent::display($request, $template);
	}

	function execute() {
        $this->_plugin->updateSetting(CONTEXT_ID_NONE, 'dfm_theme', $this->getData('theme'));
        return $this->getData('theme');
	}
	
	function readInputData() {

		$theme = Request::getUserVar('dfm_theme');
		if ($theme and ($theme != 'none')) {
            $this->setData('theme', $theme);
		} else {
            $this->addError('theme', 'This Theme is not Valid. Select another.');
		}

	}


	
	

	
	
}

?>
