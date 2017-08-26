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
 *  +- creates: processor = class to sum up functions to retrieve the data for frontapge and do the hard file stuff
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
 * 
 * 
 * 
 */



import('lib.pkp.classes.plugins.GenericPlugin');

class dfm extends GenericPlugin {
	
	public $user; // will be set by form

	public $settings;
	public $logger = null;
	
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
			$verbs[] = array('generate', 'Generate Front Matters');
            $verbs[] = array('api', 'api');
            $verbs[] = array('systemcheck', 'System Check');
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

        $theUrl = Request::getBaseUrl() . '/' . $this->pluginPath;
        $thePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $this->pluginPath;

		$journal =& Request::getJournal();
		$templateMgr =& TemplateManager::getManager();
		//$templateMgr->debugging = true;
		$templateMgr->register_function('themResults', array($this, "returnLog"));
        $templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
        $templateMgr->assign('additionalHeadData', "<link rel='stylesheet' href='$theUrl/dfm.css' type='text/css' />");
        $theUrl = Request::getBaseUrl() . '/' . $this->pluginPath;
		$thePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $this->pluginPath;
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);

        $this->settings = array(
            'tmp_path'		=> Config::getVar('dainst', 'tmpPath'),
            'lib_path'		=> $this->pluginPath . '/lib',
            'dfm_path'		=> $this->pluginPath,
            'files_path'	=> $this->pluginPath . '/classes/pdfWorker/files'
        );

        require_once('classes/loader.class.php');

        $loader = new \dfm\loader();

        if (!$loader->load($this->logger, $this->settings)) {
        	$verb = 'error';
		}

        // todo include in the rest?
        require_once('article_picker/article_picker.class.php');
        $picker = new \das\article_selector($thePath . '/article_picker/', $theUrl);

		switch ($verb) {
			case 'generate':
				$journal =& Request::getJournal();
				$journalId = ($journal ? $journal->getId() : CONTEXT_ID_NONE);
				
				$templateMgr->register_function('selectJournal', array(&$this, 'selectJournal'));
				$templateMgr->assign('thePath', $thePath);

                $picker->setTemplateEnvironment();

				$this->import('classes.form.selectToRefreshForm');
				$form = new selectToRefreshForm($this, $journalId);
				
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						ob_start();
						$form->execute();
						//$this->log = ob_get_clean(); return it correctly
						$templateMgr->display(dirname(__FILE__) . '/templates/log.tpl');
					} else {
						$form->display();
					}
				} else {					
					$form->display();
				}
				return true;

            case 'api':
                $picker->handleApiCall();
                return true;

            case 'systemcheck':
                $checker = new \dfm\systemChecker($this->logger, $this->settings);
                $checker->check();
                $this->logger->log($this->settings);
                $templateMgr->display(dirname(__FILE__) . '/templates/system_check.tpl');
                return true;

			case 'error':
                $templateMgr->display(dirname(__FILE__) . '/templates/error.tpl');
                return true;

			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
	
	
	/* the function itself */ 
	function startUpdateFrontpages($ids, $type, $updateFrontpages = true, $is_cli = false) {
		$processor = new processor($this);
		$ids = (!is_array($ids)) ? explode(',', $ids) : $ids;
		$success = $processor->runFrontpageUpate($ids, $type, $updateFrontpages);

		if ($is_cli) {
			echo ($success === true) ? "\nSUCCESS \n" : "\nERROR: $success \n";
		} else {
			echo ($success === true) ? '' : "<div class='alert alert-danger'>ERROR: $success</div>";
		}

		echo $processor->log->dumpLog(true, $is_cli);

		if ($processor->continue) {
			if ($is_cli) {
				echo "\n There where to many frontmatters to update them all. Please  enter the following command to continue:\n\n";
				echo "php plugins/generic/ojs-dainst-frontpage-generator-plugin/dfmcli.php ";
				echo $processor->continue['updateFrontpages'] ? 'update ' : 'add ';
				echo 'galley ' . implode(',', $processor->continue['galleyIds']);
				echo "\n\n";
			} else {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->setCacheability(CACHEABILITY_NO_STORE);
				$templateMgr->assign('continue_ids', implode(',', $processor->continue['galleyIds']));
				$templateMgr->assign('continue_left', count($processor->continue['galleyIds']));
				$templateMgr->assign('continue_updateFrontpages', $processor->continue['updateFrontpages']);
			}
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
	
	function returnLog() {
        if (!is_null($this->logger)) {
            return $this->logger->dumpLog(true);
        } else {
            "<unknown error>";
        }
	}


}
?>
