<?php
namespace dfm\pdfWorkers {
	class chiron extends \dfm\pdfWorker {
		function setMetadata($data) {
			if ($this->metadata['volume'] > 35) {
				$this->metadata['publisher'] 	= "Walter de Gruyter GmbH, Berlin";
			} else {
				$this->metadata['publisher'] 	= "Verlag C. H. Beck, München";
			}
		}
		
		
	}
}
?>
