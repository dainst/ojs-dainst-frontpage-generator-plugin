<?php
namespace dfm\pdfWorkers {
	class ejb extends \dfm\pdfWorker {
		function setMetadata($data) {
			$this->lang->terms->de 	= "<b style=\"font-family:calibrib\">Nutzungsbedingungen:</b> Der e-Jahresbericht {$this->metadata['year']} des Deutschen Archäologischen Instituts steht unter der Creative-Commons-Lizenz Namensnennung – Nicht kommerziell – Keine Bearbeitungen 4.0 International.<br> 
			Um eine Kopie dieser Lizenz zu sehen, besuchen Sie bitte http://creativecommons.org/licenses/by-nc-nd/4.0/";
			$this->lang->terms->en  = "<b style=\"font-family:calibrib\">Terms of use:</b> The e-Annual Report {$this->metadata['year']} of the Deutsches Archäologisches Institut is published under the Creative-Commons-Licence BY – NC – ND 4.0 International. <br> 
			To see a copy of this licence visit http://creativecommons.org/licenses/by-nc-nd/4.0/";
			$this->lang->publisher->de = "Redaktion und Satz";
			$this->lang->publisher->en = "";
			$this->metadata['publisher'] = "Annika Busching (<a style=\"color: black; text-decoration: none;\" href=\"mailto:jahresbericht@dainst.de\">jahresbericht@dainst.de</a>)<br /> Gestalterisches Konzept: Hawemann &amp; Mosch<br /> Länderkarten: © 2017 <a style=\"color: black; text-decoration: none;\" href=\"http://www.mapbox.com\">www.mapbox.com</a>";

		}
		
	}
}
?>
