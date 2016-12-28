<?php
class frontpageCreator {
		
	public $galleysToUpdate = []; // format: {'galley': <ArticleGalley>, 'article': <Article>, 'journal': <Journal>}
	
	public $log;
	
	public $plugin;
	
	public $tmp_path = '/var/www/tmp'; // @ TODO
	
	public $user;
	
	function __construct($plugin) {
		$this->plugin = $plugin;
		require_once("pdfWorker/logger.class.php");
		$this->log = new \sometools\logger();
		
		error_reporting(E_ALL & ~ E_DEPRECATED);
		ini_set('display_errors', 'on');//*/
	}
	
	
	function runFrontpageUpate($id, $type) {
		try {
			
			$this->cleanTmpFolder();

			if ($type == "journal") {
				$this->getJournalGalleys($id);
			} elseif ($type == "article") {
				$this->getArticleGalleys($id);
			} elseif ($type == "galley") {
				throw new \Exception("by galley does not work");
				$this->getGalley($id);
			}
			$this->processList();
	
		} catch (Exception $e) {
			echo "<div class='alert alert-danger'>ERROR: " . $e->getMessage() . "</div>";
		}
		
		$this->log->dumpLog();
	}
	

	/**
	 * collect all the OJS object we need later to make front matter and stuff
	 * don't worry. being a messy is nothign crucial nowerdays and also we have pills and some gargabe collection
	 * 
	 * @param <ArticleGalley|array:ArticleGalley> $galleys
	 * @param <Article|integer> $article or $article-id
	 * @param <Journal|bool> $journal
	 */
	function registerGalleys($galleys, $article, $journal = false) {

		if (!is_array($galleys)) {
			$galleys = array($galleys);
		}
		
		foreach ($galleys as $galley) {

			// make sure, we have a proper galley here
			if (!$galley or (get_class($galley) != "ArticleGalley")) {
				$this->log->warning("galley skipped, no galley: " . print_r($galley,1));
				continue;
			}
			
			if (!$galley->isPdfGalley()) {
				$this->log->warning("galley skipped, no pdf galley");
				continue;
			}
			
			if ($galley->_data['fileStage'] != 7) {
				$this->log->warning("galley skipped, not public");
				continue;
			}
			
			// make sure we have an article, else: get it
			if (is_numeric($article)) {
				$article = $this->getArticle($article);
			}
				
			if (!article or (get_class($article) != 'Article')) {
				$this->log->warning("galley skipped, article not found: " . print_r($article,1));
				continue;
			}
			
			// make sure thatw we have a journal, or get it
			if (!$journal) {
				$journal = $this->getJournal($article->getJournalId());
			}
			
			if (!$journal or (get_class($journal) != "Journal")) {
				$this->log->warning("not journal given, but a " . get_class($journal));
				continue;
			}
			
			// ok
			$this->galleysToUpdate[] = array(
				'galley'	=> $galley,
				'article'	=> $article,
				'journal'	=> $journal
			);
		}
	}
	

	
	function getGalley($id) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$galley =& $galleyDao->getGalley($id);
		$this->registerGalleys($galley);
	}
	
	/**
	 * get all galleys of an article 
	 * 
	 * @param <integer> $id - article id
	 * @param <Journal*> $journal
	 */
	function getArticleGalleys($id, $journal = null) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$this->registerGalleys($galleyDao->getGalleysByArticle($id), $id, $journal);
	}
	
	/**
	 * 
	 * 
	 * @param <integer> $id - journal id
	 */
	function getJournalGalleys($id) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$result = $articleDao->getArticlesByJournalId($id);
		$journal = $this->getJournal($id);
		foreach ($result->records as $record) {
			$this->getArticleGalleys($record["article_id"], $journal);
		}
	}
	
	/**
	 * 
	 * @param <integer> $id - articleID
	 * @return unknown
	 */
	function getJournal($id) {
		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$journal =& $journalDao->getJournal($id);
		return $journal;
	}

	function getArticle($id) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		return $articleDao->getArticle($id);
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
		$logToken = &$this->log->log('update galley "' . $galleyItem['galley']->getLabel() . '" of article "' . $galleyItem['article']->getArticleTitle() . '"');

		// get journalController
		$journalController = $this->getJournalController($galleyItem);	
		
		// create new front matter
		$newFrontmatterFile = $journalController->createFrontPage();	

		// attach frontpage to file
		$tmpFile = $journalController->updateFrontpage($journalController->fileToUpdate, $newFrontmatterFile);
		
		// update pdf metadata
		$tmpFile = $journalController->updatePDFMetadata($tmpFile);
		
		// now that everythings seems to have worked (otherwise we would not be here but in an exception handler hopefully),
		// we can copy back the shiny and overwrite the old one...
		$this->replaceFile($galleyItem, $tmpFile);

		// log that marvellous success!
		$logToken->text .= ' ... success!';
		$logToken->type = 'success';
	}
	
	
	function replaceFile($galleyItem, $newFile) {
		
		// Do the Object Limbo, Baby!
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$oldGalley = $galleyItem['galley'];
		$galley = new ArticleGalley();
		$galley->setLabel('PDF');
		$galley->setArticleId($galleyItem['article']->getId());		
		$galley->setLocale($oldGalley->getLocale());		
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($galleyItem['article']->getId());
		$fileId = $articleFileManager->copyPublicFile($newFile, 'application/pdf');
		$galley->setFileId($fileId);
		$galleyDao->insertGalley($galley);
		$galleyDao->deleteGalley($oldGalley);
		
		import('classes.article.log.ArticleLog');
		ArticleLog::logEventHeadless(
			$journal, 
			0, // @TODO insert correct user id!
			$galleyItem['article'],
			ARTICLE_LOG_TYPE_DEFAULT,
			'plugins.generic.dainstFrontmatter.updated',
			array(
				'userName' => 'Der Lustige user"',  // @TODO insert correct user id!
				'articleId' => $galleyItem['article']->getId()
			)
		);
		// NUR DASS ES SO NICHT GEHT! DA KENNT MAN JA WIEDER DIE URN NICHT IM VORFELD...
		// wir müssen sie selber generieren, das sollte doch gehen...
		// a) doch einfach den file ersetzen, keine neue Galley anlegen
		//     * sollte man die galley nicht archivieren können, kann man das ruhig machen 
		// b) erst die galley erstellen, URN erzeugen, dann file erzeugen und attachen... ob das geht?
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
		$article = $galleyItem['article'];
		$articleId = $article->getId();
		$galley = $galleyItem['galley'];
		$journal = $galleyItem['journal'];
		$journalAbb = $journal->getPath();
		$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		$journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());
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
			'urn'				=> isset($galley->_data['pub-id::other::urnDNB']) ? $galley->_data['pub-id::other::urnDNB'] : (isset($galley->_data['pub-id::other::urn']) ? $galley->_data['pub-id::other::urn'] : ''), // take the URN created by the ojsde-dnburn pugin, if not present try the normla pkugins urn or set ###
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