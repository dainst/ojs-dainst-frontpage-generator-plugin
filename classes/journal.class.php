<?php
/**
 * 
 * 
 * STAND

 * 
 * 
 * 
 * 
 * @author Philipp Franck
 * 
 * abstract for different journal extensions
 *
 */

namespace dfm {
	class journal {
		public $settings = array();		// settings from settings file like paths and so
		
		/**
		 * a ste of data needed for the frontpage
		 * it's easier to work from this point on with this, not with OJS-Objects (also, this was created indiependend of the OJS first)
		 * 
		 */
		public $metadata = array(
			'article_author'	=> '###', 
			'article_title'		=> '###',
			'editor'			=> '###',
			'issn'				=> '###',
			'issue_tag'			=> '###',
			'journal_title'		=> '###',
			'journal_sub'		=> '###',
			'journal_url'		=> '###',
			'pages'				=> '###',
			'pub_id'			=> '###',
			'publisher'			=> '###',	
			'url'				=> '###',
			'urn'				=> '###',
			'volume'			=> '###',
			'year'				=> '###',
			'zenon_id'			=> '###'
		);
		
		public $doCut 		= true;
		public $doImport 	= true;
		
		public $lang = array();
		
		private $_base_path = "../";
		private $_journals_path = "../";
		
		public $logger;
		
		function __construct($logger, $settings, $base_path = "../") {
			$this->_base_path = $base_path;
			//include_once($this->_base_path  . 'settings.php');
			$this->settings = $settings;
			$this->logger = $logger;
			$this->lang = json_decode(file_get_contents(realpath(__DIR__ . '/common.json')));
		}
		
		final function setDefaultMetadata($data) {			
			foreach ($this->metadata as $key => $value) {
				if (isset($data[$key])) {
					$this->metadata[$key] = $data[$key];
				}
			}
			if (($this->metadata['issue_tag'] == '###') and isset($this->metadata['volume']) and isset($this->metadata['year'])) {
				$this->metadata['issue_tag']		= "{$this->metadata['volume']} • {$this->metadata['year']}";
			}
		}
		
		/**
		 * to be overwritten by implementation
		 * @param unknown $data
		 */
		function setMetadata($data) {
			$this->metadata['issue_tag']		= "{$this->metadata['volume']} • {$this->metadata['year']}";	
		}
		
		function checkMetadata() {
			foreach($this->metadata as $key => $value) {
				if ($value == '###') {
					$this->logger->warning('Metadata ' . $key . ' not set');
				} 
			}
		}
		
		function createMetdata($data) {
			$this->setDefaultMetadata($data);
			$this->setMetadata($data);
			$this->checkMetadata();
		}
		
		function createFrontPage($article, $issue) {
			$this->createMetdata($article, $issue);
			$pdf = $this->createPDF();		
			$pdf->daiFrontpage(); // default frontpage layout
			$path = $this->settings['tmp_path'] . '/' . md5($article->title->value->value) . '.pdf';
			$pdf->Output($path, 'F');
			return $path;
		}
		
		
		function createPDF() {
			if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
				define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
			}
			require_once('inc/TCPDF/tcpdf.php');
			require_once('daipdf.class.php');
			$pdf = new daiPDF('P', 'mm', 'A4', true, 'UTF-8', false);
			$pdf->logger = $this->logger;
			
			$pdf->daiInit($this->lang, $this->metadata);
			
			return $pdf;
		}
		
		public function assembleAuthorlist($article) {
			$author_list = [];
			foreach ($article->author->value as $author) {
				$author_list[] = "{$author->firstname} {$author->lastname}";
			}
			return implode(' – ', $author_list);
		
		}
	
		
		public function checkFile($file) {
			if (substr($file, 0, 1) != '/') { // relative path
				$file = $this->settings['rep_path'] . '/' . $file;
			}
			if (!file_exists($file)) {
				throw new Exception("File " . $file . ' does not exist!');
			}
			return true;
		}
		
	}
}