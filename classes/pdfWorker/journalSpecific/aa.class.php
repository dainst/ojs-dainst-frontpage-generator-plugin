<?php
namespace dfm\pdfWorkers {
	class aa extends \dfm\pdfWorker {
		function setMetadata($data) {	
			$this->metadata['journal_sub'] 		= '';
		}
	
		function checkFile($file) {
			return true;
		}
	}
}
?>
