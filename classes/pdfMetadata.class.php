class pdfMetadata {
	/**
	 * writes data into to the info directory of the newely created pdf
	 * @throws Exception
	 */
	function pdfMetadata() {
		$data = $this->data;
	
		foreach ($data->articles as $nr => $article) {
	
			$author	= str_replace('"', '', $this->_assembleAuthorlist($article));
			$title 	= str_replace('"', '', $article->title->value->value);
				
			$shell = 'exiftool ' . $this->_pdfMetadataCommand($article) . ' ' . $article->filepath . " 2>&1";
				
			$this->log->debug($shell);
				
			$response = shell_exec($shell);
				
			
			$this->log->debug($response);
				
			if (strpos($response, 'Warning:') !== false) {
				$this->log->warning('exiftool warning:' . $response);
			}
				
			if (strpos($response, 'Error:') !== false) {
				throw new Exception('Error while trying to write pdt metadata:' . $response);
			}
				
			if (strpos($response, 'exiftool: not found') !== false) {
				throw new Exception('Error: exiftool missing: ' . $response);
			}
				
				
		}
	
	}
	
	/**
	 * creates a string containing dai specific metadata, wich we write in in the dc:relation field, abusing it somehow
	 * @param unknown $article
	 */
	private function _pdfMetadataCommand($article) {
		$data = $this->data;	
		$metadata = array();
		$metadata['url']		= str_replace('"', '', $article->url);
		$metadata['pubId']		= str_replace('"', '', $article->urn);
		$metadata['zenonId']	= (int) $article->zenonId;
		$metadata['daiPubId']	= (int) $article->pubid;
		
		$return = array();
		foreach ($metadata as $k => $v) {
			$return[] = "-Relation=\"$k:$v\" "; 
		}
		
		
		$journal = $this->getJournal();
		$journal->createMetdata($article, $data->journal);
		$this->log->debug(print_r($journal->metadata['journal_title'],1));
		$return[] = "-Description=\"{$journal->metadata['journal_title']}; {$journal->metadata['issue_tag']};  {$journal->metadata['pages']}\"";
		
		$return[] = '-Title="' . $journal->metadata['article_title'] . '"';
		$return[] = '-Author="' . $journal->metadata['article_author'] . '"';
		$return[] = '-Creator="DAI OJS Importer"';
				
		
		return implode(' ', $return);
	}
}
