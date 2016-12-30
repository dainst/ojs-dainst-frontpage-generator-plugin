<?php
namespace dfm\pdfWorkers {
	class test extends \dfm\pdfWorker {
		function setMetadata($data) {	
			$this->metadata['journal_sub'] 		= 'Unglaubliche Testdatei';
		}
	
		function checkFile($file) {
			return true;
		}
		
		public $smallMode = false;
	}
}

?>