<?php
class frontpageCreator {
		
	public $galleysToUpdate = []; // format: {'galley': <ArticleGalley>, 'articleId': <int>, 'journalCode': <string>}
	
	public $log;
	
	public $plugin;
	
	function __construct($plugin) {
		$this->plugin = $plugin;
		require_once("logger.class.php");
		$this->log = new logger;
	}
	
	function runFrontpageUpate($id, $type) {
		
		$type = "journal"; // for testing
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
		echo "<hr><b>Warnings</b>";
		foreach ($this->log->warnings as $msg) {
			echo "<div class='alert-warning'>$msg</div>";
		}
		echo "<hr>";
		foreach ($this->log->log as $msg) {
			echo "<div class='alert-warning'>$msg</div>";
		}
	}
	

	
	function registerGalleys($galleys, $articleId, $journal) {
		if (!is_array($galleys)) {
			$galleys = array($galleys);
		}
	
		foreach ($galleys as $galley) {
				
			if (!$galley or (get_class($galley) != "ArticleGalley")) {
				$this->log->warning("galley skipped, no galley: " . print_r($galley,1));
				continue;
			}
	
			if (!$galley->isPdfGalley()) {
				$this->warning("galley skipped, no pdf galley");
				continue;
			}
				
			if ($galley->_data['fileStage'] != 7) {
				$this->warning("galley skipped, not public");
				continue;
			}
			
			if (!$journal or (get_class($journal) != "Journal")) {
				$this->warning("not journal given, but a " . get_class($journal));
				continue;
			}
				
			$this->galleysToUpdate[] = array(
				'galley' => $galley,
				'articleId' => $articleId,
				'journal' => $journal
			);
		}
	}
	

	
	function getGalley($id) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$galley =& $galleyDao->getGalley($id);
		$this->registerGalleys($galley, 1234564564); // @TODO how get article & journal
	}
	
	function getArticleGalleys($id, $journal) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		
		if (!$journal) {
			// @TODO how get journal
		}
		
		$this->registerGalleys($galleyDao->getGalleysByArticle($id), $id, $journal);
	}
	
	function getJournalGalleys($id) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$result = $articleDao->getArticlesByJournalId($id);
		$journal = $this->getJournal($id);
		foreach ($result->records as $record) {
			$this->getArticleGalleys($record["article_id"], $journal);
		}
	}
	
	function getJournal($id) {
		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$journal =& $journalDao->getJournal($id);
		return $journal;
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

		// do the OJS object madness (wich clearly emerged from a poor java-bloated mind)
		$articleId = $galleyItem['articleId'];
		$galley = $galleyItem['galley'];
		$journal = $galleyItem['journal'];
		$journalAbb = $journal->getPath();
		$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		$journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article = $articleDao->getArticle($articleId);
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issue =& $issueDao->getIssueByArticleId($articleId, $journal->getId(), true);
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($articleId);
		$articleFile = $articleFileManager->getFile($galley->_data['fileId']);
		$path = $articleFileManager->filesDir .  $articleFileManager->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
		
		// now that we have everything, we can create our front page		
		$this->log->log('update file ' . $path . ' of article ' . $articleId . ' in journal ' . $journalAbb);
		
		// get our own, frontpage creating object
		require_once("journal.class.php");		
		if (stream_resolve_include_path("journals/{$journalAbb}.class.php")) {			
			require_once("journals/{$journalAbb}.class.php");
			$class = "\\dfm\\journals\\{$journalAbb}";		
		} else {
			$class = "\\dfm\\journal";
		}		
		
		$journalController = new $class(
			$this->log, 
			array(
				'tmp_path'		=> '/var/www/tmp',
				'tcpdf_path'	=> $this->plugin->pluginPath . '/tcpdf',
				'files_path'	=> $this->plugin->pluginPath . '/classes/journals/files' // artwork files and stuff
			)
		);

		$this->log->log('using controller ' . $class);
		
		// fill it with data
		$journalController->createMetadata(array(
			'article_author'	=> $this->_noDoubleSpaces($article->getAuthorString(false, ' – ')),
			'article_title'		=> $this->_getLocalized($article->_data['cleanTitle']),
			'editor'			=> '<br>' . $this->_noLineBreaks($journalSettings['contactName'] . ' ' . $this->_getLocalized($journalSettings['contactAffiliation'])),
			'issn'				=> isset($journalSettings['onlineIssn']) ? $journalSettings['onlineIssn'] : (isset($journalSettings['printIssn']) ?	$journalSettings['printIssn'] : '###'),
			'journal_title'		=> $this->_getLocalized($journalSettings['title']), 
			'journal_url'		=> Config::getVar('general', 'base_url') . '/' . $journalAbb,
			'pages'				=> $article->_data['pages'],
			'pub_id'			=> $articleId,
			'publisher'			=> $this->_noLineBreaks($journalSettings['publisherInstitution']  . ' ' . $this->_getLocalized($journalSettings['publisherNote'])),
			'url'				=> Config::getVar('general', 'base_url') . '/' . $journalAbb . '/' . $articleId . '/' . $galley->getId(),
			'urn'				=> isset($galley->_data['pub-id::other::urnDNB']) ? $galley->_data['pub-id::other::urnDNB'] : (isset($galley->_data['pub-id::other::urn']) ? $galley->_data['pub-id::other::urn'] : '###'), // take the URn created by the ojsde-dnburn pugin, if not present try the normla pkugins urn or set ###
			'volume'			=> $issue->_data['volume'],
			'year'				=> $issue->_data['year'],
			'zenon_id'			=> '####'
		));
		
		echo "<hr><div><b>UPDATE ",$articleId,"</b><pre>";
		//print_r();
		print_r($journalController->metadata);
		$journalController->createFrontPage();
		echo "</pre></div>";
	}
	
	private function _noLineBreaks($string) {
		return preg_replace( "/\r|\n/", " ", $string);
	}
	
	private function _getLocalized($array) {
		$default = AppLocale::getPrimaryLocale();
		return isset($array[$default]) ? $array[$default] : array_pop($array);
	}
	
	private function _noDoubleSpaces($string) {
		return preg_replace( "#\s{2,}#", " ", $string);
	}

}