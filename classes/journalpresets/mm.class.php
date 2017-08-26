<?php
namespace dfm;
class mm extends journalpreset {
	function setMetadata($data) {
		if ($this->metadata['volume'] < 20) {

			$this->metadata['publisher'] 	= "F.H. Kerle Verlag, Heidelberg";

		} else if (($this->metadata['volume'] > 20) and ($this->metadata['volume'] < 44)) {

			$this->metadata['publisher'] 	= "Verlag Philipp von Zabern GmbH, Mainz";

		} else {

			$this->metadata['publisher'] 	= "Reichert Verlag, Wiesbaden";

		}
	}


}

?>
