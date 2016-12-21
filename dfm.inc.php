<?php
import('lib.pkp.classes.plugins.GenericPlugin');

class dfm extends GenericPlugin {
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

	/*
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
	
	var $messages = []; // format: {'text': <string>, 'type': <string:alert,danger,...>}
	
	var $galleysToUpdate = []; // format: {'galley': <ArticleGalley>, 'articleId': <int>}
	
	function message($msg, $type = 'default') {
		$numtypes = array(
			1 => 'warning',
			2 => 'danger'
		);
		if (is_numeric($type) and in_array($type, $numtypes)) {
			$type = $numtypes[$type];
		}
		$this->messages[] = array(
			"text" => $msg,
			"type" => $type
		);
	}
	
	function registerGalleys($galleys, $articleId) {
		if (!is_array($galleys)) {
			$galleys = array($galleys);
		}
		
		foreach ($galleys as $galley) {
			
			if (!$galley or (get_class($galley) != "ArticleGalley")) {
				$this->message("galley skipped, no galley: " . print_r($galley,1),1);
				continue;
			}
				
			if (!$galley->isPdfGalley()) {
				$this->message("galley skipped, no pdf galley",1);
				continue;
			}
			
			if ($galley->_data['fileStage'] != 7) {
				$this->message("galley skipped, not public", 1);
				continue;
			}
			
			$this->galleysToUpdate[] = array(
				'galley' => $galley,
				'articleId' => $articleId
			);
		}

	}
	
	function startUpdateFrontapges($id, $type) {
		$type = "journal";
		$id = 2;
		try {
			if ($type == "journal") {
				$this->getJournalGalleys($id);
			} elseif ($type == "article") {
				$this->getArticleGalleys($id);
			} elseif ($type == "galley") {
				$this->getGalley($id);
			}
			$this->updateFrontpages();
			
		} catch (Exception $e) {
			echo "<div style='background:red'>ERROR:> " . $e->getMessage() . "</div>";
		}
		
		foreach ($this->messages as $msg) {
			echo "<div class='alert-{$msg['type']}'>{$msg['text']}</div>";
		}
	}
	

	
	function getGalley($id) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$galley =& $galleyDao->getGalley($id);		
		$this->registerGalleys($galley, 1234564564); //!?s
	}

	function getArticleGalleys($id) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$this->registerGalleys($galleyDao->getGalleysByArticle($id), $id);
	}
	
	function getJournalGalleys($id) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$result = $articleDao->getArticlesByJournalId($id);

		foreach ($result->records as $record) {
			$this->getArticleGalleys($record["article_id"]);
		}
		
	}
	
	function updateFrontpages() {
		if (!$this->galleysToUpdate or !count($this->galleysToUpdate)) {
			throw new Exception("no galleys given");
		}
		foreach ($this->galleysToUpdate as $galleyItem) {
			$this->updateFrontpage($galleyItem);
		}
	}
	
	function updateFrontpage($galleyItem) {
		$articleId = $galleyItem['articleId'];
		$galley = $galleyItem['galley'];
		
		echo "<hr><div><b>UPDATE ",$articleId,"</b><pre>";
		
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($articleId);
		$articleFile = $articleFileManager->getFile($galley->_data['fileId']);
		
		$path = $articleFileManager->filesDir .  $articleFileManager->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
		$this->message('updateing file ' . $path . ' of article ' . $articleId);
		
		
		
		var_dump($path);
		echo "</pre></div><hr>";
	}
}
?>