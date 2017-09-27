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
 *			// create new front matter
 *			$newFrontmatterFile = $pdfWorker->createFrontPage();	
 *
 *			// attach frontpage to file
 *			$tmpFile = $pdfWorker->updateFrontpage($oldFile, $newFrontmatterFile);
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
            parent::__construct($logger, $settings);
            require_once($this->settings->lib_path . '/tcpdf/tcpdf.php');
		}

		

		
		function createFrontPage() {
			$pdf = $this->createPDFObject();
			$pdf->frontpage(); // default frontpage layout
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

            $this->settings->theme_path = $this->settings->ojs_path . '/' . $this->settings->dfm_path . '/classes/themes/' . $this->settings->theme;
			require_once($this->settings->theme_path . '/theme.php');
            $classname = '\tcpdf\\' . $this->settings->theme;
            if (!class_exists($classname)) {
                throw new \Exception("Class $classname not found!");
            }
			$pdf = new $classname($this->log,$this->settings);
            if (method_exists($this->journalpreset, applyToTheme)) {
                $this->journalpreset->applyToTheme($pdf, $this->metadata);
            }
			$pdf->init($this->metadata);
			return $pdf;

            //function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false
            //array(160, 240)
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
			$this->log->debug("update file: $oldFile with front matter $newFrontpage (replace: $replace)");
			
			$newFrontpage = escapeshellarg($newFrontpage);
			$oldFile = escapeshellarg($oldFile);
			$tmpFile = $this->getTmpFileName();
			
			$pages = $replace ? "2-end" : "";
			
			$shell = "pdftk A=$newFrontpage B=$oldFile cat A1 B$pages output $tmpFile 2>&1"; // in production: $tmpFile == $oldFile
						
			$this->log->debug($shell);
			
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
			$this->log->log("update PDF-Metadata: $file ");
		
			$shell = 'exiftool ' . $this->_pdfMetadataCommand() . ' ' . escapeshellarg($file) . " 2>&1";
	
			$this->log->debug($shell);
	
			$response = shell_exec($shell);
				
			$this->log->debug($response);
	
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