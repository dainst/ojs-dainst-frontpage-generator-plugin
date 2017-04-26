<?php
/**
 * 
 * this class creates a pdf front page in the dainst style
 * 
 * it is designed to work in different contexts (the OJS Importer maybe for example, or
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
	class pdfWorker {
		public $settings = array(
			'tcpdf_path'		=> '',
			'tmp_path'			=> '',
			'files_path'		=> '',
			'tex_path'			=> ''
		);
		
		/**
		 * a set of data needed for the frontpage
		 * it's easier to work from this point on with this, not with OJS-Objects (also, this was created indiependend of the OJS first)
		 * 
		 */
		public $metadata = array(
			'article_author'	=> '###',
			'article_title'		=> '###',
			'editor'			=> '###',
			'issn_online'		=> '',
			'issn_printed'		=> '',
			'issue'			=> '###',
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
			'zenon_id'			=> ''
		);
		// '###' -> means missing, will be printed and warning, '' means unset, will not be printed
		
		public $smallMode = false; // false: A4 Formatj, true: A5 Format

		public $texTemplate = 'default';
		
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
			if (($this->metadata['issue'] == '###') and isset($this->metadata['volume']) and isset($this->metadata['year'])) {
				$this->metadata['issue'] = "{$this->metadata['volume']} • {$this->metadata['year']}";
			}
		}
		
		/**
		 * to be overwritten
		 */
		function setMetadata($data) {
			$this->metadata = $data;
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
			$dir = $this->createWorkingDirectory();
			$this->logger->log('work on' . print_r($dir,1));

			$tex = $this->createTexCommands();
			$tex[] = "\\input{{$this->settings['tex_path']}/{$this->texTemplate}/frontmatter.tex}";
			$tex = implode(' ', $tex);
			$lualatex = "cd $dir && lualatex -output-directory=$dir -interaction=nonstopmode -halt-on-error -recorder \"$tex\"";
			$return = exec($lualatex, $output, $exitstatus);


			$this->logger->log($lualatex);
			if ($exitstatus > 0) {
				$error = $this->logger->error($return);
				$error->debuginfo = implode("\n", $output);
				throw new \Exception("Compilation of frontmatter with Lualatex failed");
			} else {
				$this->logger->log($return);
			}

			return $dir;
		}

		function createTexCommands() {
			$tex = array();
			foreach ($this->metadata as $var => $val) {
				if ((substr($val,1,1) != "#") and ($val !== "")){
					$v = str_replace('_', '', $var);
					$tex[] = "\\newcommand{\\fm$v}{{$val}}"; // @ TODO escape bogus chars
				}

			}
			return $tex;
		}


		function createWorkingDirectory() {
			$dir = $this->settings['tmp_path'] . '/' . md5(microtime() . rand());
			if (mkdir($dir)) {
				return $dir;
			} else {
				throw new \Exception("could not create working dir:" . $dir);
			}
		}
				
		function getTmpFileName() {
			return $this->settings['tmp_path'] . '/' . md5(microtime() . rand()) . '.pdf';
		}
		
		function createPDFObject() {

			if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
				define('K_TCPDF_EXTERNAL_CONFIG', true);
			}
			if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
				define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
			}
			require_once($this->settings['tcpdf_path'] . '/tcpdf.php');
			
			require_once('daipdf.class.php');
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
			$this->logger->log("update updatePDFMetadata: $file ");			
		
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

			$return[] = "-Description=" . escapeshellarg("{$metadata['journal_title']}; {$metadata['tag']}; {$metadata['pages']}");
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
			array_map('unlink', glob($this->settings['tmp_path'] . '/*'));
		}

		public function checkPrerequisites() {

			if (!is_dir($this->settings['tmp_path'])) {
				throw new \Exception("No proper tmp path defined: " . $this->settings['tmp_path']);
			}
			if (!is_writable($this->settings['tmp_path'])) {
				throw new \Exception("tmp path is not writable: " . $this->settings['tmp_path']);
			}
			$this->logger->debug("check tmp path: ". $this->tmp_path);

			$texlive = exec('lualatex --version', $output, $status);
			if ($status > 0) {
				throw new \Exception("lualatex is not installed or does not work properly:\n" . implode("\n", $output));
			}
			$this->logger->log('check lualatex: ' . $output[0]);

			$template = "{$this->settings['tex_path']}/{$this->texTemplate}/frontmatter.tex";
			if (!file_exists($template)) {
				throw new \Exception("no Tex-Templates found: $template");
			}
			$this->logger->log('check lualatex templates: OK, present');


		}
		
	}
}