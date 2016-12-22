<?php
namespace dfm\journals {
	class test extends \dfm\journal {
		function setMetadata($data) {	
			$this->metadata['journal_sub'] 		= 'Unglaubliche Testdatei';
			$this->metadata['publisher'] 		= "Überschrieben! Gumpg Verlag, berlin";
		}
	
		function checkFile($file) {
			return true;
		}
	}
}

?>