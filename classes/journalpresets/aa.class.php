<?php
namespace dfm;
class aa extends journalpreset {
	function setMetadata($data) {
		if ($this->metadata['year'] >= 2013) {
			$this->metadata['publisher'] 	= "Ernst Wasmuth Verlag GmbH & Co. Tübingen";
		} else if (($this->metadata['year'] < 2013) && ($this->metadata['year'] > 2007)) {
			$this->metadata['publisher'] 	= "Hirmer Verlag GmbH, München";
		} else {
			$this->metadata['publisher'] 	= "Verlag Philipp von Zabern GmbH, München";
		}

	}


}

?>
