<?php
namespace dfm\pdfWorkers {
	class test extends \dfm\pdfWorker {
		function setMetadata($data) {	
			$this->metadata['journal_sub'] 		= 'Unglaubliche Testdatei';
			$this->metadata['publisher'] 		= "Überschrieben! Gumpg Verlag, berlin";
		}
	
		function checkFile($file) {
			return true;
		}
	}
}

?>