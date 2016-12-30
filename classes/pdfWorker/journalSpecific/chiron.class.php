<?php
namespace dfm\pdfWorkers {
	class chiron extends \dfm\pdfWorker {
		function setMetadata($data) {
	
			$this->metadata['journal_sub'] 		= 'Mitteilungen der Kommission für alte Geschichte und Epigraphik des Deutschen Archäologischen Instituts';
			$this->metadata['editor'] 			= "<br>Kommission für Alte Geschichte und Epigraphik des DAI, Amalienstr. 73b, 80799 München";
			
			if ($this->metadata['volume'] > 35) {
				$this->metadata['publisher'] 	= "Walter de Gruyter GmbH, Berlin";
			} else {
				$this->metadata['publisher'] 	= "Verlag C. H. Beck, München";
			}
		}
		
		function checkFile($file) {
			return true;
		}
		
		
		public $smallMode = false;
		
	}
}
?>
