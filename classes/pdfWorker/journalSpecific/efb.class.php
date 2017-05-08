<?php
namespace dfm\pdfWorkers {
	class aa extends \dfm\pdfWorker {
		function setMetadata($data) {
			$this->metadata['terms'] 	= "Die e-Forschungsberichte {$this->metadata['year']}-{$this->metadata['volume']} des DAI stehen unter der Creative-Commons-Lizenz Namensnennung – Nicht kommerziell – Keine Bearbeitungen 4.0 International. 
			Um eine Kopie dieser Lizenz zu sehen, besuchen Sie bitte http://creativecommons.org/licenses/by-nc-nd/4.0/";
		}
		
		function checkFile($file) {
			return true;
		}
		
		
		public $smallMode = false;
		
	}
}
?>
