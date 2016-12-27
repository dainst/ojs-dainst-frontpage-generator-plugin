<?php
class frontpageCreator {
		
	public $galleysToUpdate = []; // format: {'galley': <ArticleGalley>, 'articleId': <int>, 'journalCode': <string>}
	
	public $log;
	
	public $plugin;
	
	public $tmp_path = '/var/www/tmp'; // @ TODO
	
	public $user;
	
	function __construct($plugin) {
		$this->plugin = $plugin;
		require_once("pdfWorker/logger.class.php");
		$this->log = new logger();
		/*
		error_reporting(E_ALL & ~ E_DEPRECATED);
		ini_set('display_errors', 'on');//*/
	}
	
	function runFrontpageUpate($id, $type) {
		
		$type = "journal"; // for testing
		$id = 2;
		
		try {
			
			$this->cleanTmpFolder();
			
			if ($type == "journal") {
				$this->getJournalGalleys($id);
			} elseif ($type == "article") {
				$this->getArticleGalleys($id);
			} elseif ($type == "galley") {
				$this->getGalley($id);
			}
			$this->processList();
			
			
	
		} catch (Exception $e) {
			echo "<div style='background:red'>ERROR:> " . $e->getMessage() . "</div>";
		}
		echo "<hr><b>Warnings</b>";
		foreach ($this->log->warnings as $msg) {
			echo "<div class='alert-warning'><pre>$msg</pre></div><br>";
		}
		echo "<hr>";
		foreach ($this->log->log as $msg) {
			echo "<div class=''>$msg</div>";
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

	
	function processList() {
		if (!$this->galleysToUpdate or !count($this->galleysToUpdate)) {
			throw new Exception("no galleys given");
		}
		foreach ($this->galleysToUpdate as $galleyItem) {
			$this->processItem($galleyItem);
		}
	}
	
	function processItem($galleyItem) {
		$this->log->log('update galley of article ' . $galleyItem['articleId']);
			
		echo "<hr><div><b>UPDATE ",$galleyItem['articleId'],"</b><pre>";
		
		$journalController = $this->getJournalController($galleyItem);
		
		$newFrontmatterFile = $journalController->createFrontPage();
			
		$tmpFile = $journalController->updateFrontpage($journalController->fileToUpdate, $newFrontmatterFile);
		$tmpFile = $journalController->updatePDFMetadata($tmpFile);		// because testing... $tmpFile instead of $fileToUpdate
		
		// now that everythings seems to have worked (otherwise we would not be here but in an exception handler hopefully),
		// we can copy back the shiny and overwrite the old one...
		$this->replaceFile($galleyItem, $tmpFile);
		
		
		
		echo "</pre></div>";
	}
	
	
	function replaceFile($galleyItem, $newFile) {
		
		// Do the Object Limbo, Baby!
		$oldGalley = $galleyItem['galley'];
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$galley = new ArticleGalley();
		$galley->setLabel('PDF');
		$galley->setArticleId($galleyItem['articleId']);		
		$galley->setLocale($oldGalley->getLocale());		
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($galleyItem['articleId']);
		$fileId = $articleFileManager->copyPublicFile($newFile, 'application/pdf');
		$galley->setFileId($fileId);
		$galleyDao->insertGalley($galley);
		$galleyDao->deleteGalley($oldGalley);
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		import('classes.article.log.ArticleLog');
		$article = $articleDao->getArticle($galleyItem['articleId']);
		ArticleLog::logEventHeadless(
			$journal, 
			0, // @TODO insert correct user id!
			$article,
			ARTICLE_LOG_TYPE_DEFAULT,
			'plugins.generic.dainstFrontmatter.updated',
			array(
				'userName' => 'Der Lustige user"',  // @TODO insert correct user id!
				'articleId' => $article->getId()
			)
		);
	}

		
	
	/**
	 * collect all data, wich we need for new frontpage,
	 * and also for new PDF-metadata
	 * 
	 * 
	 * @param unknown $galleyItem
	 * @return $journal instance 
	 */
	function getJournalController($galleyItem) {

		// do the OJS object madness (wich most likely emerged from a poor java-bloated mind)
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
		$fileToUpdate = $articleFileManager->filesDir .  $articleFileManager->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
		
				
		// get journal Controller
		// some journals (may) need special treatment for example chiron has two different publishers, but we want only print the right one one th frontpage
		require_once("pdfWorker/pdfWorker.class.php");
		if (stream_resolve_include_path("pdfWorker/journalSpecific/{$journalAbb}.class.php")) {
			require_once("pdfWorker/journalSpecific/{$journalAbb}.class.php");
			$class = "\\dfm\\pdfWorkers\\{$journalAbb}";
		} else {
			$class = "\\dfm\\pdfWorker";
		}
		$journalController = new $class(
			$this->log,
			array(
				'tmp_path'		=> $this->tmp_path,
				'tcpdf_path'	=> $this->plugin->pluginPath . '/tcpdf',
				'files_path'	=> $this->plugin->pluginPath . '/classes/pdfWorker/files' // artwork files and stuff
			)
		);		
		$this->log->log('using controller ' . $class);
		
		// fill it with data
		$meta = array(
			'article_author'	=> $this->_noDoubleSpaces($article->getAuthorString(false, ' – ')),
			'article_title'		=> $this->_getLocalized($article->_data['cleanTitle']),
			'editor'			=> '<br>' . $this->_noLineBreaks($journalSettings['contactName'] . ' ' . $this->_getLocalized($journalSettings['contactAffiliation'])),
			'journal_title'		=> $this->_getLocalized($journalSettings['title']), 
			'journal_url'		=> Config::getVar('general', 'base_url') . '/' . $journalAbb,
			'pages'				=> $article->_data['pages'],
			'pub_id'			=> $articleId,
			'publisher'			=> $this->_noLineBreaks($journalSettings['publisherInstitution']  . ' ' . $this->_getLocalized($journalSettings['publisherNote'])),
			'url'				=> Config::getVar('general', 'base_url') . '/' . $journalAbb . '/' . $articleId . '/' . $galley->getId(),
			'urn'				=> isset($galley->_data['pub-id::other::urnDNB']) ? $galley->_data['pub-id::other::urnDNB'] : (isset($galley->_data['pub-id::other::urn']) ? $galley->_data['pub-id::other::urn'] : '###'), // take the URn created by the ojsde-dnburn pugin, if not present try the normla pkugins urn or set ###
			'volume'			=> $issue->_data['volume'],
			'year'				=> $issue->_data['year'],
			'zenon_id'			=> '##'
		);
		
		
		if (isset($journalSettings['onlineIssn']) and $journalSettings['onlineIssn']) {
			$meta['issn_online']= $journalSettings['onlineIssn'];
		} elseif (isset($journalSettings['printIssn']) and $journalSettings['printIssn']) {
			$meta['issn_printed']= $journalSettings['printIssn'];
		}
		
		$journalController->createMetadata($meta);
		$journalController->fileToUpdate = $fileToUpdate;
		return $journalController;
	}


	
	public function cleanTmpFolder() {
		array_map('unlink', glob($this->tmp_path . '/*'));
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