<?php
namespace dfm\pdfWorkers {
	class aa extends \dfm\pdfWorker {
		function setMetadata($data) {
			if ($this->metadata['year'] => 2013) {
				$this->metadata['publisher'] 	= "Ernst Wasmuth Verlag GmbH & Co. Tübingen";
			}
			if ($this->metadata['year'] < 2013) {
				$this->metadata['publisher'] 	= "Hirmer Verlag GmbH, München";
			}
			else {
				$this->metadata['publisher'] 	= "Verlag Philipp von Zabern GmbH, München";
			}
		}
		
		function checkFile($file) {
			return true;
		}
		
		
		public $smallMode = false;
		
	}
}
?>
