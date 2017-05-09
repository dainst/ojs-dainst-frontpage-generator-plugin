<?php
namespace dfm\pdfWorkers {
	class efb extends \dfm\pdfWorker {
		function setMetadata($data) {
			$this->lang->terms->de 	= "<b style=\"font-family:calibrib\">Nutzungsbedingungen:</b> Die e-Forschungsberichte {$this->metadata['year']}-{$this->metadata['volume']} des Deutschen Archäologischen Instituts stehen unter der Creative-Commons-Lizenz Namensnennung – Nicht kommerziell – Keine Bearbeitungen 4.0 International.\n 
			Um eine Kopie dieser Lizenz zu sehen, besuchen Sie bitte http://creativecommons.org/licenses/by-nc-nd/4.0/";
			$this->lang->terms->en  = "<b style=\"font-family:calibrib\">Terms of use:</b> The e-Research Reports {$this->metadata['year']}-{$this->metadata['volume']} of the Deutsches Archäologisches Institut is published under the Creative-Commons-Licence BY – NC – ND 4.0 International. <br> 
			To see a copy of this licence visit http://creativecommons.org/licenses/by-nc-nd/4.0/";
			$this->lang->publisher->de = "";
			$this->lang->publisher->en = "";
		}
		
	}
}
?>
