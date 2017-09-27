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
        $ojsPath = dirname(dirname(dirname(dirname(__FILE__))));

        $journal =& Request::getJournal();
        $journalId = ($journal ? $journal->getId() : CONTEXT_ID_NONE);

		$journal =& Request::getJournal();
		$templateMgr =& TemplateManager::getManager();
        $templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
		//$templateMgr->debugging = true;
		$templateMgr->register_function('themResults', array($this, "returnLog"));
        $templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
        $templateMgr->register_modifier('get_title', array(&$this, 'getModuleTitle'));
        $templateMgr->register_modifier('get_availability', array(&$this, 'getModuleAvailability'));
       
        $templateMgr->assign('additionalHeadData', $templateMgr->get_template_vars('additionalHeadData') . "<link rel='stylesheet' href='$theUrl/dfm.css' type='text/css' />");
        $templateMgr->assign('thePath', $ojsPath . $this->pluginPath); // we need this?

		$dfm_dr = ($verb != 'systemcheck') ? $this->getSetting(CONTEXT_ID_NONE, 'dfm_dr') : '';
		$theme = $this->getSetting(CONTEXT_ID_NONE, 'dfm_theme');

        $this->settings = (object) array(
            'tmp_path'				=> Config::getVar('dainst', 'tmpPath'),
            'lib_path'				=> $this->pluginPath . '/lib',
            'dfm_path'				=> $this->pluginPath,
            'files_path'			=> $this->pluginPath . '/classes/pdfWorker/files', //TODO remove?!
			'ojs_path'				=> $ojsPath,
			'url'					=> $theUrl,
			'dependencies_resolved' => is_null($dfm_dr) ? array() : $dfm_dr,
			'registry'				=> array(),
			'theme'					=> $theme
        );

        try {

			// load dfm stuff
			require_once('classes/loader.class.php');
			$loader = new \dfm\loader();
			if (!$loader->load($this->logger, $this->settings)) {
                throw new Exception("Error Loading DFM");
			}
			// if we had no system check results stored, store them for next time
			if (is_null($dfm_dr)) {
				$dfm_dr = $this->updateSetting(CONTEXT_ID_NONE, 'dfm_dr', $this->settings->dependencies_resolved);
			}

			$pickerloader = new \dfm\ojs2ui($this->logger, $this->settings);

			switch ($verb) {

				case 'settings':
					$this->import('classes.form.settingsForm');
					$form = new settingsForm($this, $journalId);

                    if (Request::getUserVar('save')) {
                        $form->readInputData();
                        if ($form->validate()) {
                            $this->settings->theme = $form->execute();
                            $this->updateSetting(CONTEXT_ID_NONE, 'dfm_theme', $theme);
                        }
                    }

					$templateMgr->assign('settings', (array) $this->settings);
					$form->display();
                    break;

				case 'generate':
					if (!$pickerloader->load()) {
						throw new Exception("Article Picker not Found");
					}
					$this->import('classes.form.selectToRefreshForm');
					$form = new selectToRefreshForm($this, $journalId);
					if (Request::getUserVar('save')) {
						$form->readInputData();
						if ($form->validate()) {
                           	$this->startUpdateFrontpages($form->getData('idlist'), $form->getData('type'), $form->getData('replace'));
							$templateMgr->display(dirname(__FILE__) . '/templates/log.tpl');
							break;
						}
					}
                    $templateMgr->assign('settings', (array) $this->settings);
					$form->display();
					break;

				case 'api':
					$pickerloader->load();
					$pickerloader->handleApiCall();
                    break;

				case 'systemcheck':
					// systemcheck is performed by loader, we just need to show results
                    $templateMgr->assign('settings', (array) $this->settings);
					$templateMgr->display(dirname(__FILE__) . '/templates/system_check.tpl');
                    break;

				default:
                    throw new Exception('Unknown management verb');
			}

        } catch (Exception $e) {
        	$this->logger->danger($e->getMessage());
            $templateMgr->display(dirname(__FILE__) . '/templates/error.tpl');
		}
        return true;
	}


	
	
	/* the function itself */ 
	function startUpdateFrontpages($ids, $type, $updateFrontpages = true, $is_cli = false) {
		$processor = new \dfm\processor($this->logger, $this->settings);
		$ids = (!is_array($ids)) ? explode(',', $ids) : $ids;
		$success = $processor->runFrontpageUpate($ids, $type, $updateFrontpages);

		if ($is_cli) {
			echo ($success === true) ? "\nSUCCESS \n" : "\nERROR: $success \n";
		} else {
			//echo ($success === true) ? '' : "<div class='alert alert-danger'>ERROR: $success</div>";
		}

		//echo $processor->log->dumpLog(true, $is_cli);

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

	
	function returnLog() {
        if (!is_null($this->logger)) {
            return $this->logger->dumpLog(true);
        } else {
            return "[unknown error]";
        }
	}

	function getModuleTitle($input) {
        $sc = new \dfm\systemChecker($this->logger, $this->settings);

        echo $sc->getTitle($input);
	}

    function getModuleAvailability($input) {
        $sc = new \dfm\systemChecker($this->logger, $this->settings);
        return $sc->getAvailability($input);
    }


}
?>
