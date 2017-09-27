<?php
namespace dfm;

class chiron extends journalpreset {
	function setMetadata($data) {
		if ($data['volume'] > 35) {
			$data['publisher'] 	= "Walter de Gruyter GmbH, Berlin";
		} else {
			$data['publisher'] 	= "Verlag C. H. Beck, München";
		}
		return $data;
	}


}

?>
