<?php
namespace dfm {
	class test extends \dfm\journalpreset {
		function setMetadata($data) {
            $data['journal_sub'] 		= 'Unglaubliche Testdatei';
            $data['journal_title'] 		= 'Supergeil';
            return $data;
		}

		function applyToTheme($pdf) {
            $pdf->lang->from->de = '->';
            $pdf->lang->from->en = '->';
        }

	}
}

?>