<?php
/**
 * 
 * plugin that generated front pages for our pfds
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
 *  
 * 
 * 
 * 
 * Baustellen:
 * - log / richtigen user
 * # log / Message
 * # Oberfläche: log richtig anzeigen
 * - Ergebnis richtig handlen
 * - URN Loop brechen: URN zuerst erzeugen oder pdf selbst austauschen
 * - 
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
	function manage($verb, $args, &$message, &$messageParams) {
		if (!parent::manage($verb, $args, $message, $messageParams)) return false;

		$journal =& Request::getJournal();
		$templateMgr =& TemplateManager::getManager();
		//$templateMgr->debugging = true;
		$templateMgr->register_function('themResults', array($this, "showLog"));
		$thePath = Request::getBaseUrl() . '/' . $this->pluginPath;
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
		
		switch ($verb) {
			case 'settings':
				$journal =& Request::getJournal();
				$journalId = ($journal ? $journal->getId() : CONTEXT_ID_NONE);
				
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$templateMgr->register_function('selectJournal', array(&$this, 'selectJournal'));
				$templateMgr->assign('additionalHeadData', "<link rel='stylesheet' href='$thePath/dfm.css' type='text/css' />\n<script src='$thePath/js/urlExtractor.js' ></script>");
				
				$this->import('classes.form.selectToRefreshForm');
				$form = new selectToRefreshForm($this, $journalId);
				
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						ob_start();
						$form->execute();
						$this->log = ob_get_clean();
						$templateMgr->display(dirname(__FILE__) . '/templates/log.tpl');
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
	function startUpdateFrontapges($ids, $type) {	
		require_once('classes/frontpageCreator.class.php');
		$frontpageCreator = new frontpageCreator($this);
		foreach (explode(',', $ids) as $id) {
			$frontpageCreator->runFrontpageUpate((int) $id, $type);
		}

	}
	
	/* helping hands */
	
	function selectJournal() {
		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$r = '<select id="dfm_journalselect">';
		$r .= '<option value="-1">--select journal--</option>';
		foreach ($journalDao->getJournalTitles(true) as $id => $title) {
			$r .= "<option value='$id'>$title</option>";
		}
		$r .= '</select>';
		return $r;
		
	}
	
	public $log; // @TODO replace with contact to $logger object
	function showLog() {
		echo $this->log;
		
	}
}
?>