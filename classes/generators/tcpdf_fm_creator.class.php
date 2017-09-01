<?php
/**
 * 
 * this class creates a pdf front page in the dainst style
 * 
 * it is designed to work in different contexts (the OJS Importer maybe for axample, or
 * an OMP instacne or...), so everything wich is OJS-specific code is kept outside this class
 * 
 * @author Philipp Franck
 * 
 * abstract for different journal extensions
 * 
 * 
 * usage:
 * 
 * 
 * 		try {
 * 
 * 			$pdfWorker->fileToUpdate = $oldFile;
 * 
 *			// create new front matter
 *			$newFrontmatterFile = $pdfWorker->createFrontPage();	
 *
 *			// attach frontpage to file
 *			$tmpFile = $pdfWorker->updateFrontpage($pdfWorker->fileToUpdate, $newFrontmatterFile);
 *		
 *			// update pdf metadata
 *			$newFile = $pdfWorker->updatePDFMetadata($tmpFile);
 *
 * 		} catch (\Exception $e) {
 * 			echo "Error:" . $e->getMessage();
 * 		}
 * 
 *		
 * 
 *
 */

namespace dfm {
	class tcpdf_fm_creator extends generator {

	    const dependencies = 'tcpdf|pdftk|exiftool';
	    const title = 'TCPDF Frontpage Creator';

		function __construct($logger, $settings) {
			//$this->lang = json_decode(file_get_contents($this->settings->files_path . '/common.json')); @ TODO put somwhere else
            parent::__construct($logger, $settings);
		}

        public $smallMode = false; // false: A4 Formatj, true: A5 Format

        public $lang = array();
		
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
			$pdf = $this->createPDFObject();
			$pdf->daiFrontpage(); // default frontpage layout
			$path = $this->getTmpFileName();
			$pdf->Output($path, 'F');
			return $path;
		}
				
		function getTmpFileName() {
			return $this->settings->tmp_path . '/' . md5(microtime() . rand()) . '.pdf';
		}
		
		function createPDFObject() {

			if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
				define('K_TCPDF_EXTERNAL_CONFIG', true);
			}
			if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
				define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
			}
			
			require_once('theme.php');
			//function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false
			//array(160, 240)
			$pdf = new \daiPDF($this->smallMode);
			$pdf->logger = $this->logger;
			$pdf->settings = $this->settings;
			$pdf->daiInit($this->lang, $this->metadata);
			return $pdf;
		}
		
		/**
		 * 
		 * replaces the first page of the file $oldFile with $newFrontpage
		 * - if $replace is set fo false, $newFrontpage just gets attached in the Beginning of $oldFile
		 * 
		 * @param <string> $oldFile - fullpath
		 * @param <string> $newFrontpage - fullpath
		 * @param <bool> $replace
		 * 
		 * @return <string> fullpath of file with new frontmatter
		 */
		public function updateFrontpage($oldFile, $newFrontpage, $replace = true) {
			$this->logger->debug("update file: $oldFile with front matter $newFrontpage (replace: $replace)");
			
			$newFrontpage = escapeshellarg($newFrontpage);
			$oldFile = escapeshellarg($oldFile);
			$tmpFile = $this->getTmpFileName();
			
			$pages = $replace ? "2-end" : "";
			
			$shell = "pdftk A=$newFrontpage B=$oldFile cat A1 B$pages output $tmpFile 2>&1"; // in production: $tmpFile == $oldFile
						
			$this->logger->debug($shell);
			
			$cut = shell_exec($shell);
			
			if($cut != '') {
				throw new \Exception($cut);
			}
			
			return $tmpFile;
		}
		
		/**
		 * updates a file with the current metadata set
		 * @param <string> $file - fullpath
		 */
		public function updatePDFMetadata($file) {
			$this->checkFile($file);
			$this->logger->log("update PDF-Metadata: $file ");
		
			$shell = 'exiftool ' . $this->_pdfMetadataCommand() . ' ' . escapeshellarg($file) . " 2>&1";
	
			$this->logger->debug($shell);
	
			$response = shell_exec($shell);
				
			$this->logger->debug($response);
	
			if (strpos($response, 'Warning:') !== false) {
				$this->log->warning('exiftool warning:' . $response);
			}
	
			if (strpos($response, 'Error:') !== false) {
				throw new \Exception('Error while trying to write pdf metadata:' . $response);
			}
	
			if (strpos($response, 'exiftool: not found') !== false) {
				throw new \Exception('Error: exiftool missing: ' . $response);
			}
			
			return $file;
		}
		
		/**
		 * creates a string containing dai specific metadata, wich we write in in the dc:relation field, abusing it somehow
		 * @param unknown $article
		 */
		private function _pdfMetadataCommand() {
			
			$metadata = $this->metadata;
			
			$writeRelations = array('zenon_id', 'url', 'urn', 'pub_id');
		
			$return = array();
			foreach ($writeRelations as $r) {
				if (isset($metadata[$r]) and ($metadata[$r] !== "###")) {
					$return[] = "-Relation=" . escapeshellarg("$r:{$metadata[$r]}");
				}
			}

			$return[] = "-Description=" . escapeshellarg("{$metadata['journal_title']}; {$metadata['issue_tag']}; {$metadata['pages']}");
			$return[] = '-Title=' . escapeshellarg($metadata['article_title']);
			$return[] = '-Author=' . escapeshellarg($metadata['article_author']);
			$return[] = '-Creator="DAINST OJS Frontmatter Plugin"';
		
			return implode(' ', $return);
		}
		
		public function checkFile($file) {
			if (!file_exists($file)) {
				throw new Exception("File " . $file . ' does not exist!');
			}
			return true;
		}
		
		public function cleanTmpFolder() {
			array_map('unlink', glob($this->settings->tmp_path . '/*'));
		}
		
	}
}