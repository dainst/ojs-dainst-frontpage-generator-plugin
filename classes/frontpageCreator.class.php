<?php
class frontpageCreator {
		
	public $galleysToUpdate = []; 
	/* contains arrays in the form of: 
		{
			'galley': <ArticleGalley>,
			'newGalley': <ArticleGalley>,  
			'article': <Article>, 
			'journal': <Journal>,
			'dirty': <boolean> (when galley is created but file is missing)
		}
	*/
	public $log; // logger object
	
	public $plugin; // the dfm plugin
	
	public $tmp_path;
	
	public $updateFrontpages = true; // if true first page will be exchanged, otherwise a front matter will be added
	
	function __construct($plugin) {
		$this->plugin = $plugin;
		require_once("pdfWorker/logger.class.php");
		$this->log = new \sometools\logger();

		
		/*
		error_reporting(E_ALL & ~ E_DEPRECATED);
		ini_set('display_errors', 'on');//*/
	}
	

	
	/**
	 * This marvelous function extraordinaire does a fantastic job in creating in 
	 * doing the update of a pdf frontpage
	 * 
	 * it takes an id and a type as sent by the plugin's form and 
	 * flushes the log.
	 * 
	 * 
	 * 
	 * @param <integer> $id - id of an object whose galleys should be updated
	 * @param <type> $type - type of that object: journal, article or galley (or missing)
	 * @return <bool|string> - true if success, as text message if error
	 */
	function runFrontpageUpate($id, $type, $updateFrontpages = true) {
		
		try {
			
			// update or replace fm
			$this->updateFrontpages = $updateFrontpages;
			$this->log->log($updateFrontpages ? 'replace front matter mode' : 'add front matter mode');
			
			// get and clean tmpFolder
			$this->tmp_path = Config::getVar('dainst', 'tmpPath');
			$this->log->debug("tmp path: ". $this->tmp_path);
			if (!is_dir($this->tmp_path)) {
				throw new \Exception("No proper tmp path defined: " . $this->tmpPath);
			}
			$this->cleanTmpFolder();
			
			// get items to update and do it
			if ($type == "journal") {
				$this->getJournalGalleys($id);
			} elseif ($type == "article") {
				$this->getArticleGalleys($id);
			} elseif ($type == "galley") {
				$this->getGalley($id);
			} elseif ($type == "missing") {
				$this->getMissing();
			}
			$this->processList($type == "missing");
			

	
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		$this->removeUnfinishedGalleys();
		
		
		return true;
	}
	

	/**
	 * collect all the OJS object we need later to make front matter and stuff
	 * don't worry. being a messy is nothing crucial nowadays and also we have pills and some gargabe collection
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
			/*
			if ($galley->_data['fileStage'] != 7) {
				$this->log->warning("galley skipped, not public");
				continue;
			}
			*/
			// make sure we have an article, else: get it
			if (is_numeric($article)) {
				$article = $this->getArticle($article);
			}
				
			if (!$article or (get_class($article) != 'Article')) {
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
			$this->galleysToUpdate[] = (object) array(
				'galley'	=> $galley,
				'article'	=> $article,
				'journal'	=> $journal,
				'newGalley'	=> null
			);
		}
	}
	

	/**
	 * registers a galley by its id for going to be updated
	 * (my english grammar is outragous as usual)
	 * 
	 * @param <integer> $id
	 */
	function getGalley($id) {
		$galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
		$galley = $galleyDao->getGalley($id);
		if (method_exists($galley, 'getArticleId')) {
			$this->registerGalleys($galley, $galley->getArticleId());
		} else {
			$this->log->log("could not get galley nr " . $id);
		}
	}
	
	/**
	 * registers all galleys of an article for going to be updated 
	 * 
	 * @param <integer> $id - article id
	 * @param <Journal*> $journal
	 */
	function getArticleGalleys($id, $journal = null) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$this->registerGalleys($galleyDao->getGalleysByArticle($id), $id, $journal);
	}
	
	/**
	 * registers all galleys of a journal for going to be updated 
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
	 * get an Journal by it's ID
	 * 
	 * @param <integer> $id - journals ID
	 * @return unknown
	 */
	function getJournal($id) {
		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$journal =& $journalDao->getJournal($id);
		return $journal;
	}

	/**
	 * get an Article by it's ID
	 * 
	 * @param <integer> $id  - article ID
	 * @return Article
	 */
	function getArticle($id) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		return $articleDao->getArticle($id);
	}
	
	/**
	 * 
	 * get all articles galley wich are flaggged as missing in the zenon-id (the importer does that)
	 * 
	 * we misuse the zenonId-field to flag articles in the importer wich need to get an cover
	 * 
	 */
	function getMissing() {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$sql = "SELECT * FROM articles WHERE pages like '%#DFM'";
		$blub = $articleDao->retrieve($sql);
		$result = new DAOResultFactory($blub, $this, '_dummy');
		$result = $result->toArray();
		foreach ($result as $record) {
			$this->getArticleGalleys($record['article_id']);
		}
	}
	
	/**
	 * needed by DAOResultFactory above
	 */
	function _dummy($row) {
		return $row;
	} 
	
	/**
	 * runs over the list of registered galleys ($this->galleysToUpdate) and updates them
	 * 
	 * @param <bool> $removeMarker - if true, update the article to remove need-no-frontmatter-marker
	 * @throws Exception
	 */
	function processList($removeMarker = false) {
		if (!$this->galleysToUpdate or !count($this->galleysToUpdate)) {
			throw new Exception("no galleys given");
		}
		foreach ($this->galleysToUpdate as $galleyItem) {
			$this->log->log('next item: ' . $this->_getLocalized($galleyItem->article->getTitle()));
			$this->processItem($galleyItem, $removeMarker);
		}
	}
	
	/**
	 * the more than awesome updating process itself which will blow your mind with its sheer awesomeness
	 * 
	 * @param <bool> $removeMarker - if true, update the article to remove need-no-frontmatter-marker
	 * @param unknown $galleyItem
	 */
	function processItem($galleyItem, $removeMarker = false) {
		$logToken = &$this->log->log('update galley "' . $galleyItem->galley->getLabel() . '" of article "' . $galleyItem->article->getArticleTitle() . '"');

		// get journalController
		$pdfWorker = $this->getPDFWorker($galleyItem);	
		
		// create new front matter
		$newFrontmatterFile = $pdfWorker->createFrontPage();	

		// attach frontpage to file
		$tmpFile = $pdfWorker->updateFrontpage($pdfWorker->fileToUpdate, $newFrontmatterFile, $this->updateFrontpages);

		// update pdf metadata
		$tmpFile = $pdfWorker->updatePDFMetadata($tmpFile);
		
		// now that everythings seems to have worked (otherwise we would not be here but in an exception handler hopefully),
		// we can copy back the shiny and overwrite the old one...
		$this->replaceFile($galleyItem, $tmpFile);
		
		// if removeMarker is set (we come from the importer most likely)
		if ($removeMarker) {
			$this->removeMarker($galleyItem);
		}

		// log that marvelous success!
		$logToken->text .= ' ... success!';
		$logToken->type = 'success';
	}
	
	/**
	 * 
	 * @param <Galley> $galleyItem
	 * @param <string> $newFile (path + filename)
	 */
	function replaceFile($galleyItem, $newFile) {
		// Do the Object Limbo, Baby!
		$article = $galleyItem->article;
		$oldGalley = $galleyItem->galley;
		$newGalley = $galleyItem->newGalley;
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($article->getId());
		$fileId = $articleFileManager->copyPublicFile($newFile, 'application/pdf');
		if ($fileId == 0) {
			thorw new Exception("article " . $article->getId() . ": new file could not be copied!");
		}
		$newGalley->setFileId($fileId);	
		$newGalley->setSequence(0);
		$newGalley->setFileType('application/pdf'); // important!
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$galleyDao->updateGalley($newGalley);

		$galleyItem->dirty = false;
		$galleyDao->deleteGalley($oldGalley);
		
		$user = Request::getUser();
		import('classes.article.log.ArticleLog');

		ArticleLog::logEventHeadless(
			$journal, 
			!is_null($user) ? $user->getId() : '', // if cli, user is not given
			$article,
			ARTICLE_LOG_TYPE_DEFAULT,
			'plugins.generic.dainstFrontmatter.updated',
			array(
				'userName' => !is_null($user) ? $user->getFullName() : 'cli',
				'articleId' => $article->getId()
			)
		);
	}

	function removeMarker($galleyItem) {
		$article = $galleyItem->article;
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		
		$pages = $article->getPages();
		
		if (!$pages or !strstr($pages, '#DFM')) {
			$this->log->log('no marker to remove: ' . $pages);
			return;
		}

		$article->setPages(str_replace('#DFM', '', $pages));

		$articleDao->updateArticle($article);

		$this->log->log('marker removed');
		
	}
	
	
	
	/**
	 * creates pubIds fro all availabe plugins! 
	 * @param unknown $galley
	 * @param unknown $article
	 * @param string $preview
	 * @return multitype:NULL
	 */	
	function createPubIds($galley, $article, $preview = false) {		
		$pubIdPlugins =& PluginRegistry::loadCategory('pubIds', true, $article->getJournalId());
		$pubIds = array();
		foreach ($pubIdPlugins as $pubIdPlugin) {
			$pubIdType = $pubIdPlugin->getPubIdType();
			$pubIds[$pubIdType] = $pubIdPlugin->getPubId($galley, $preview);
		}
		return $pubIds;
	}
		
	
	/**
	 * collect all data, wich we need for new frontpage,
	 * and also for new PDF-metadata
	 * 
	 * 
	 * @param <object> $galleyItem
	 * @return $journal instance 
	 */
	function getPDFWorker($galleyItem) {

		// do the OJS object madness (wich most likely emerged from a poor java-bloated mind)
		$article = $galleyItem->article;
		$articleId = $article->getId();
		$galley = $galleyItem->galley;
		$journal = $galleyItem->journal;
		$journalAbb = $journal->getPath();
		$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		$journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issue =& $issueDao->getIssueByArticleId($articleId, $journal->getId(), true);
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($articleId);
		$articleFile = $articleFileManager->getFile($galley->_data['fileId']);
		$fileToUpdate = $articleFileManager->filesDir .  $articleFileManager->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$newGalley = new ArticleGalley();
		$newGalley->setLabel('PDF');
		$newGalley->setSequence(0);
		$newGalley->setFileType('application/pdf'); // important!
		$newGalley->setArticleId($articleId);
		$newGalley->setLocale($galley->getLocale());
		$galleyDao->insertGalley($newGalley); // why now? because for our pubids we maybe need the galley-ID, and otheriwse we would not have it
		$galleyItem->dirty = true;
		
		$pids = $this->createPubIds($newGalley, $article);
		$this->log->log("created the following PIDs: " . print_r($pids,1));
		$galleyItem->newGalley = $newGalley;
		
		// get journal Controller
		// some journals (may) need special treatment for example chiron has two different publishers, but we want only print the right one one th frontpage
		require_once("pdfWorker/pdfWorker.class.php");
		if (stream_resolve_include_path("pdfWorker/journalSpecific/{$journalAbb}.class.php")) {
			require_once("pdfWorker/journalSpecific/{$journalAbb}.class.php");
			$class = "\\dfm\\pdfWorkers\\{$journalAbb}";
		} else {
			$class = "\\dfm\\pdfWorker";
		}
		$pdfWorker = new $class(
			$this->log,
			array(
				'tmp_path'		=> $this->tmp_path,
				'tcpdf_path'	=> $this->plugin->pluginPath . '/tcpdf',
				'files_path'	=> $this->plugin->pluginPath . '/classes/pdfWorker/files' // artwork files and stuff
			)
		);		
		$this->log->log('using controller ' . $class);
		
		// fill it with data
		@$meta = array(
			'article_author'	=> $this->_noDoubleSpaces($article->getAuthorString(false, ' – ')),
			'article_title'		=> $this->_getLocalized($article->_data['title']),
			'editor'			=> '<br>' . $this->_noLineBreaks($journalSettings['contactName'] . ' ' . $this->_getLocalized($journalSettings['contactAffiliation'])),
			'journal_title'		=> $this->_getLocalized($journalSettings['title']), 
			'journal_url'		=> Config::getVar('general', 'base_url') . '/' . $journalAbb,
			'pages'				=> str_replace('#DFM', '', $article->_data['pages']),
			'pub_id'			=> $articleId,
			'publisher'			=> $this->_noLineBreaks($journalSettings['publisherInstitution']  . ' ' . $this->_getLocalized($journalSettings['publisherNote'])),
			'url'				=> Config::getVar('general', 'base_url') . '/' . $journalAbb . '/' . $articleId . '/' . $newGalley->getId(),
			'urn'				=> isset($pids['other::urnDNB']) ? $pids['other::urnDNB'] : (isset($pids['other::urn']) ? $pids['other::urn'] : ''), // take the URN created by the ojsde-dnburn pugin, if not present try the normla pkugins urn or set ###
			'volume'			=> $issue->_data['volume'],
			'year'				=> $issue->_data['year'],
			'zenon_id'			=> isset($pids['other::zenon']) ? $pids['other::zenon'] : '##'
		);
		

		if (isset($journalSettings['onlineIssn']) and $journalSettings['onlineIssn']) {
			$meta['issn_online']= $journalSettings['onlineIssn'];
		} elseif (isset($journalSettings['printIssn']) and $journalSettings['printIssn']) {
			$meta['issn_printed']= $journalSettings['printIssn'];
		}
		
		$pdfWorker->createMetadata($meta);
		$pdfWorker->fileToUpdate = $fileToUpdate;
		return $pdfWorker;
	}

	/**
	 * in case of an error while creating the frontpage, an empty galley would be created. 
	 * to prevent this, we run this function
	 *
	 */
	public function removeUnfinishedGalleys() {
		if (!$this->galleysToUpdate or !count($this->galleysToUpdate)) {
			return;
		}
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		foreach ($this->galleysToUpdate as $galleyItem) {
			if ($galleyItem->dirty === true) {
				$id = $galleyItem->newGalley->getId();
				$galleyDao->deleteGalley($galleyItem->newGalley);
				$galleyItem->dirty = false;
				$this->log->warning('file could not be finished, temporary galley is removed: ' . $id);
			}
		}
	}

	/**
	 * deleted whatever garbish was created on the way to the new frontmatter
	 */
	public function cleanTmpFolder() {
		array_map('unlink', glob($this->tmp_path . '/*'));
	}
	
	/**
	 * removes linebreaks from a string and replaces them with whitespace
	 * @param <string> $string
	 * @return <string>
	 */
	private function _noLineBreaks($string) {
		return preg_replace( "/\r|\n/", " ", $string);
	}
	
	/**
	 * selects the best string from an array of localized strings
	 * @param <array> $array
	 * @return <string>
	 */
	private function _getLocalized($array) {
		if (!is_array($array)) {
			return $array;
		}
		$default = AppLocale::getPrimaryLocale();
		return isset($array[$default]) ? $array[$default] : array_pop($array);
	}
	
	/**
	 * kills double spaces in a string
	 * @param <string> $string
	 * @return <string>
	 */
	private function _noDoubleSpaces($string) {
		return preg_replace( "#\s{2,}#", " ", $string);
	}

}
