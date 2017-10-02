<?php
/**
 * extension of the TCPDF class to create front matters
 * 
 * 
 * 
 * @author Philipp Franck
 *
 */

namespace tcpdf;

class generic_tcpdf_theme extends \TCPDF {

	public $metadata = array();

	public $lang;
	
	public $log;

	public $settings; // paths!

    public function __construct($log, $settings) {
        $this->log = $log;
        $this->settings = $settings;
        $this->unitScale = 1;
        $format = 'A4';
        parent::__construct('P', 'mm', $format, true, 'UTF-8', false, false);
        $this->lang = json_decode(file_get_contents($this->settings->theme_path . '/common.json'));
        if ($this->lang  === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON Error in common.json");
        }
    }
	
	public function init($metadata) {

		$this->metadata = $metadata;
		
		// set monosprace font
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins // $left, $top, $right
		$this->SetMargins(20 * $this->unitScale, 15 * $this->unitScale, 19 * $this->unitScale);

		// set auto page breaks
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set default font subsetting mode
		$this->setFontSubsetting(true);
		
		// kill paddings
		$this->setCellPaddings(0, 0, 0, 0);

		// Add a page
		$this->AddPage();
	}
	
	public function frontpage() {
	
		// get unitScale
		$k = $this->unitScale;

		// author(s), title
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->myPrint($this->metadata['article_author'], '2');
		$this->myPrint($this->metadata['article_title'], '3');
		$longtitle = strlen($this->metadata['article_title']) > 95;
		
		// aus
		$this->SetXY(20 * $k, $this->GetY() + ($longtitle ? 10 : 26));
		$this->printInfo('from', 1.5);
	
		// journal
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->myPrint($this->metadata['journal_title'], '3');
		$this->myPrint($this->metadata['journal_sub'], '2');
	
		// page
		$this->SetXY(20 * $k, $this->GetY() + 6.6 * $k);
		$this->printInfo('issue_tag', 1.5);
		$this->printInfo('pages', 1.5);
		$this->myPrint('<a style="color:black;text-decoration:none" href="' . $this->metadata['url'] . '">' . $this->metadata['url'] . '</a><a style="color:black;text-decoration:none" href="http://nbn-resolving.de/' . $this->metadata['urn'] . '"> • ' . $this->metadata['urn'] . '</a>', 1.5);

		// aus
		$this->SetXY(20 * $k, $this->GetY() + ($longtitle ? 11 : 28));
		$this->printInfo('editor');
		$this->printInfo('journal_url');
		$this->printInfo('issn_online');
		$this->printInfo('issn_printed');
		$this->printInfo('publisher');
	
		// (c)
		$this->SetXY(20 * $k, $this->GetY() + 6.6 * $k);
		$this->myPrint("© " . date('Y') . '' . $this->lang->copyright->en);
	
		// terms;
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->myPrint($this->lang->terms->en);

	}
	

	
	/**
	 * wrapper fro some writeHTML functions
	 * 
	 * @param unknown $html
	 * @param string $font
	 * @param unknown $cell
	 */
	public function myPrint($html, $font = '1', $cell = array()) {
	

		$debug = 0;
	
		if (!count($cell)) {
			$this->writeHTML($html, true, 0, true, true);
		} else {
			$this->writeHTMLCell(
				isset($cell['w']) ? $cell['w'] : '',
				isset($cell['h']) ? $cell['h'] : '',
				isset($cell['x']) ? $cell['x'] : '',
				isset($cell['y']) ? $cell['y'] : '',
				$html,
				$debug, /* border */
				1, /* ln */
				false, /* fill */
				true, /* reseth */
				isset($cell['align']) ? $cell['align'] : '',
				true /* autopadding */
			);
		}
	
	}
	
	/**
	 * prints a line with an information triple like publisher etc.
	 *
	 * @param unknown $field
	 * @param unknown $value
	 * @param unknown $font
	 */
	public function printInfo($field, $font = '1') {
		if (isset($this->metadata[$field]) and ($this->metadata[$field] == '')) {
			return;
		}
		$this->myPrint($this->lang->$field->en . ': ' . (isset($this->metadata[$field]) ? $this->metadata[$field] : ''), $font);
	}
	
	

	



	
	public function Header() {}
	public function Footer() {}
}
