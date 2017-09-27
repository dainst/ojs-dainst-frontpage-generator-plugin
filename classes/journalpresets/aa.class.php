<?php
namespace dfm;
class aa extends journalpreset {
	function setMetadata($data) {
		if ($data['year'] >= 2013) {
			$data['publisher'] 	= "Ernst Wasmuth Verlag GmbH & Co. Tübingen";
		} else if (($data['year'] < 2013) && ($data['year'] > 2007)) {
			$data['publisher'] 	= "Hirmer Verlag GmbH, München";
		} else {
			$data['publisher'] 	= "Verlag Philipp von Zabern GmbH, München";
		}
		return $data;

	}


}

?>
