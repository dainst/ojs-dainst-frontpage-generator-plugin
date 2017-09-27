<?php
namespace dfm;
class mm extends journalpreset {
	function setMetadata($data) {
		if ($data['volume'] < 20) {
			$data['publisher'] 	= "F.H. Kerle Verlag, Heidelberg";
		} else if (($data['volume'] > 20) and ($data['volume'] < 44)) {
			$data['publisher'] 	= "Verlag Philipp von Zabern GmbH, Mainz";
		} else {
			$data['publisher'] 	= "Reichert Verlag, Wiesbaden";
		}
		return $data;
	}


}

?>
