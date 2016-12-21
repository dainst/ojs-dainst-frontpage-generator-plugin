<?php
class frontpageCreator {
		
	public $galleysToUpdate = []; // format: {'galley': <ArticleGalley>, 'articleId': <int>, 'journalCode': <string>}
	
	public $log;
	
	function __construct($id, $type) {
		$type = "journal"; // for testing
		$id = 2;
		
		require_once("logger.class.php");
		$this->log = new logger;
		
		
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
	

	
	function registerGalleys($galleys, $articleId, $journalAbb) {
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
				
			$this->galleysToUpdate[] = array(
					'galley' => $galley,
					'articleId' => $articleId,
					'journalAbb' => $journalAbb
			);
		}
	}
	

	
	function getGalley($id) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		$galley =& $galleyDao->getGalley($id);
		$this->registerGalleys($galley, 1234564564); // @TODO how get article & journal
	}
	
	function getArticleGalleys($id, $journalAbb = false) {
		$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		
		if (!$journalAbb) {
			// @TODO how get journal
		}
		
		$this->registerGalleys($galleyDao->getGalleysByArticle($id), $id, $journalAbb);
	}
	
	function getJournalGalleys($id) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$result = $articleDao->getArticlesByJournalId($id);
		$journalId = $this->getJournalApp($id);
		foreach ($result->records as $record) {
			$this->getArticleGalleys($record["article_id"], $journalId);
		}
	}
	
	function getJournalApp($id) {
		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$journal =& $journalDao->getJournal($id);
		return $journal->getPath();
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
		$journalAbb = $galleyItem['journalAbb'];
		$articleId = $galleyItem['articleId'];
		$galley = $galleyItem['galley'];
	
		echo "<hr><div><b>UPDATE ",$articleId,"</b><pre>";
	
		import('classes.file.ArticleFileManager');
		$articleFileManager = new ArticleFileManager($articleId);
		$articleFile = $articleFileManager->getFile($galley->_data['fileId']);
	
		$path = $articleFileManager->filesDir .  $articleFileManager->fileStageToPath($articleFile->getFileStage()) . '/' . $articleFile->getFileName();
		$this->log->log('updateing file ' . $path . ' of article ' . $articleId . ' in journal ' . $journalAbb);
	
		require_once("journal.class.php");
		
		if (stream_resolve_include_path("journals/{$journalAbb}.class.php")) {			
			require_once("journals/{$journalAbb}.class.php");
			$class = "\\dfm\\journals\\{$journalAbb}";		
		} else {
			$class = "\\dfm\\journal";
		}
		
		$this->log->log('using controller ' . $class);
		
		$journalController = new $class;
		
	
		var_dump($path);
		echo "</pre></div>";
	}
	
}