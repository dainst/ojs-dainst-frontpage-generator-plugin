<?php
namespace dfm\pdfWorkers {
	class efb extends \dfm\pdfWorker {
		function setMetadata($data) {
			$this->lang->terms->de 	= "<b style=\"font-family:calibrib\">Nutzungsbedingungen:</b> Die e-Forschungsberichte {$this->metadata['year']}-{$this->metadata['volume']} des Deutschen Archäologischen Instituts stehen unter der Creative-Commons-Lizenz Namensnennung – Nicht kommerziell – Keine Bearbeitungen 4.0 International.
			Um eine Kopie dieser Lizenz zu sehen, besuchen Sie bitte <a style=\"color:black; text-decoration: none;\" href=\"http://creativecommons.org/licenses/by-nc-nd/4.0/\">http://creativecommons.org/licenses/by-nc-nd/4.0/</a>";
			$this->lang->terms->en  = "<b style=\"font-family:calibrib\">Terms of use:</b> The e-Annual Report {$this->metadata['year']} of the Deutsches Archäologisches Institut is published under the Creative-Commons-Licence BY – NC – ND 4.0 International. <br> 
			To see a copy of this licence visit <a style=\"color:rgb(128,130,133); text-decoration: none;\" href=\"http://creativecommons.org/licenses/by-nc-nd/4.0/\">http://creativecommons.org/licenses/by-nc-nd/4.0/</a>";
			$this->lang->publisher->de = "Redaktion und Satz";
			$this->lang->publisher->en = "";
			$this->metadata['publisher'] = "Annika Busching (<a style=\"color: black; text-decoration: none;\" href=\"mailto:jahresbericht@dainst.de\">jahresbericht@dainst.de</a>)<br /> Gestalterisches Konzept: Hawemann &amp; Mosch<br /> Länderkarten: © 2017 <a style=\"color: black; text-decoration: none;\" href=\"http://www.mapbox.com\">www.mapbox.com</a>";

		}
		
	}
}
?>
