<?php
/**
 * 
 * this class creates a pdf front page in the dainst style
 * 
 * it is designed to work in different cntexts, so everything wich nis OJS code is kept away
 * 
 * @author Philipp Franck
 * 
 * abstract for different journal extensions
 * 
 * 
 * usage:
 *
 */

namespace dfm {
	class journal {
		public $settings = array(
			'tcpdf_path'		=> '',
			'tmp_path'			=> '',
			'files_path'		=> ''
		);
		
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
		
		
		public $logger;
		
		function __construct($logger, $settings) {
			//include_once($this->_base_path  . 'settings.php');
			$this->settings = $settings;
			$this->logger = $logger;
			$this->lang = json_decode(file_get_contents($this->settings['files_path'] . '/common.json'));
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
		 * to be overwritten
		 * @param unknown $data
		 */
		function setMetadata($data) {
			
		}
		
		/**
		 * checks if metdata is set, else raises an error
		 */
		function checkMetadata() {
			foreach($this->metadata as $key => $value) {
				if ($value == '###') {
					$this->logger->warning('Metadata ' . $key . ' not set');
				} 
			}
		}
		
		/**
		 * 
		 * @param unknown $data
		 */
		function createMetadata($data) {
			$this->setDefaultMetadata($data);
			$this->setMetadata($data);
			$this->checkMetadata();
		}
		
		function createFrontPage() {
			$pdf = $this->createPDF();
			$pdf->daiFrontpage(); // default frontpage layout
			$path = $this->settings['tmp_path'] . '/' . md5(time()) . '.pdf';
			$pdf->Output($path, 'F');
			return $path;
		}
				
		function createPDF() {

			if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
				define('K_TCPDF_EXTERNAL_CONFIG', true);
			}
			if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
				define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
			}
			require_once($this->settings['tcpdf_path'] . '/tcpdf.php');
			
			require_once('daipdf.class.php');
			//function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false
			
			error_reporting(E_ALL & ~ E_DEPRECATED);
			ini_set('display_errors', 'on');
			
			$pdf = new \daiPDF('P', 'mm', 'A4', true, 'UTF-8', false, false);

			$pdf->logger = $this->logger;
			$pdf->settings = $this->settings;
			
			$pdf->daiInit($this->lang, $this->metadata);
			
			return $pdf;
		}
		
		public function checkFile($file) {

			if (!file_exists($file)) {
				throw new Exception("File " . $file . ' does not exist!');
			}
			return true;
		}
		
	}
}