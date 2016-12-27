<?php
/**
 * 
 * plugin that generated front pages for  our pfds
 * 
 * it is a little bit complicated structures since here come together the OO-Strcutires from OJS, TCPDF and the importer wor wich
 * this code was previously written
 * 
 * dfm (extends GenericPlugin) = OJS plugin
 *  |
 *  +- creates: frontpageCreator = class to sum up functions to retrieve the data for frontapge and do the hard file stuff
 *  |            |
 *  |            +- creates: logger
 *  |            +- creates: \dfm\journal (or extending \dfm\journals/{xxx}) = bring the metdata in a form we want on the frointpage and so
 *  |                         |
 *  |                         +- uses: logger
 *  |                         +- creates: daiPDF (extends TCPDF) = a TCPDF implementation as TCPDF works like that
 *  +- creates: pdfMetadata = class to sum up functions to write metadata in the PDF
 *  
 * 
 * 
 * 
 * 
 */



import('lib.pkp.classes.plugins.GenericPlugin');

class dfm extends GenericPlugin {
	
	public $user; // will be set by form
	
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				HookRegistry::register('ArticleGalleyDAO::insertNewGalley', array(&$this, 'callback') );
			}

			return true;
		}
		return false;
	}

	function callback($hookName, $args) {
		// code here
	}

	function getName() {
		return 'dfm';
	}

	function getDisplayName() {
		return "Dainst - Frontmatter Generator";
	}

	function getDescription() {
		return "Renews Frontmatters";
	}

	function isSitePlugin() {
		return true;
	}
	

	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
		parent::setEnabled($enabled);
		$journal =& Request::getJournal();
		return false;
	}

	/**
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', 'settings');
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Execute a management verb on this plugin
	 * @param $verb string
	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
	 * @return boolean
	 */
	function manage($verb, $args, &$message) {
		if (!parent::manage($verb, $args, $message)) return false;

		$journal =& Request::getJournal();

		$templateMgr =& TemplateManager::getManager();

		switch ($verb) {
			case 'settings':
				
				$journal =& Request::getJournal();
				$journalId = ($journal ? $journal->getId() : CONTEXT_ID_NONE);
				$templateMgr =& TemplateManager::getManager();
				
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
				
				$this->import('classes.form.selectToRefreshForm');
				$form = new selectToRefreshForm($this, $journalId);
				
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						//Request::redirect(null, 'manager', 'plugin', array('generic', 'dainstFrontMatter', 'settings'));
					} else {
						
						$form->display();
					}
				} else {					
					$form->display();
				}
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
	
	
	/* the function itself */ 
	function startUpdateFrontapges($id, $type) {
		
		require_once('classes/frontpageCreator.class.php');
		$frontpageCreator = new frontpageCreator($this);
		$frontpageCreator->runFrontpageUpate($id, $type);
	}
}
?>