<?php
namespace dfm\pdfWorkers {
	class ejb extends \dfm\pdfWorker {
		function setMetadata($data) {
			$this->metadata['terms'] 	= "Der e-Jahresbericht {$this->metadata['year']} des DAI steht unter der Creative-Commons-Lizenz Namensnennung – Nicht kommerziell – Keine Bearbeitungen 4.0 International.
Um eine Kopie dieser Lizenz zu sehen, besuchen Sie bitte http://creativecommons.org/licenses/by-nc-nd/4.0/";
		}
		
		function checkFile($file) {
			return true;
		}
		
		
		public $smallMode = false;
		
	}
}
?>
