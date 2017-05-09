<?php
namespace dfm\pdfWorkers {
	class ejb extends \dfm\pdfWorker {
		function setMetadata($data) {
			$this->lang->terms->de 	= "<b style=\"font-family:calibrib\">Nutzungsbedingungen:</b> Die e-Forschungsberichte {$this->metadata['year']}-{$this->metadata['volume']} des DAI stehen unter der Creative-Commons-Lizenz Namensnennung – Nicht kommerziell – Keine Bearbeitungen 4.0 International.\n 
			Um eine Kopie dieser Lizenz zu sehen, besuchen Sie bitte http://creativecommons.org/licenses/by-nc-nd/4.0/";
			$this->lang->terms->en  = "<b style=\"font-family:calibrib\">Terms of use:</b> ...";
		}
		
	}
}
?>
