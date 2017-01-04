<?php
/**
 * extension of the TCPDF class to create front matters
 * 
 * 
 * 
 * @author Philipp Franck
 *
 */
class daiPDF extends TCPDF {

	public $metadata = array();
	
	public $logger;

	public $settings; // paths!
	
	public $smallMode;
	
	public function daiInit($lang, $metadata) {
		$this->importMissingFonts();
		
		$this->lang = $lang;
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
	
	public function daiFrontpage() {
	
		// get unitScale
		$k = $this->unitScale;
		
		$this->ImageSVG(
			"{$this->settings['files_path']}/dailogo.svg" /*$file*/, 
			$this->smallMode ? 96.2 : 114 /*$x*/, 
			15 * $k /*$y*/, 
			'' /*$w*/, 
			23 * $k /*$h*/, 
			'' /*$link*/, 
			'B' /*$align*/
		);
	
		// url right to image
		$this->daiFont('xs');
		$this->SetXY(0, 0.1 * $k);
		$this->Cell(0 * $k, 38 * $k,'https://publications.dainst.org', 0, 1,'R', false, 'https://publications.dainst.org ', 0, false, 'T', 'B'); //, 'T'
	
		// first grey line
		$this->SetLineStyle(array('width' => 0.1 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(128, 130, 133)));
		$this->SetXY($this->smallMode ? 97 : 115.5, $this->GetY() + 2.7 * $k, true);
		$this->Cell($this->smallMode ? 41 : 75, 0, '', 'T', 0, 'L');
	
		// iDAI.publications below
		//$this->SetXY(107, $this->GetY() + 2.7);
		$this->daiPrint('<span style="color: rgb(0, 68, 148)">i</span>DAI.publications', 'h1', array(
			'y' => $this->GetY() + 1 * $k,
			'align' => 'R'
		));
	
		// second grey line
		$this->SetLineStyle(array('width' => 0.1 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(128, 130, 133)));
		$this->SetXY($this->smallMode ? 97 : 115.5, $this->GetY() - 1 * $k);
		$this->Cell($this->smallMode ? 41 : 75, 0, '', 'T', 0, 'C');
	
		// underline text
		$this->daiPrint($this->lang->electronic_publication->de, "h2", array(
			'x'	=> 93.3,
			'y' => $this->GetY() + 2.7 * $k,
			'align' => 'R'
		));
	
		// Sonderdruck
		$this->SetXY(20 * $k, $this->GetY() + 19 * $k);
		$this->daiPrintInfo('digital_offprint', 1.5 * $k);
	
	
		// author(s), title
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->daiPrint($this->metadata['article_author'], '2');
		$this->daiPrint($this->metadata['article_title'], '3');
		$longtitle = strlen($this->metadata['article_title']) > 190;
		
		// aus
		$this->SetXY(20 * $k, $this->GetY() + (($this->smallMode or $longtitle) ? 10 : 26));
		$this->daiPrintInfo('from', 1.5);
	
		// journal
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->daiPrint($this->metadata['journal_title'], '3');
		$this->daiPrint($this->metadata['journal_sub'], '2');
	
		// page
		$this->SetXY(20 * $k, $this->GetY() + 6.6 * $k);
		$this->daiPrintInfo('issue_tag', 1.5);
		$this->daiPrintInfo('pages', 1.5);
		$this->daiPrint('<a style="color:black;text-decoration:none" href="' . $this->metadata['url'] . '">' . $this->metadata['url'] . '</a><a style="color:black;text-decoration:none" href="http://nbn-resolving.de/' . $this->metadata['urn'] . '"> • ' . $this->metadata['urn'] . '</a>', 1.5);

		// aus
		$this->SetXY(20 * $k, $this->GetY() + (($this->smallMode or $longtitle) ? 11 : 28));
		$this->daiPrintInfo('editor');
		$this->daiPrintInfo('journal_url');
		$this->daiPrintInfo('issn_online');
		$this->daiPrintInfo('issn_printed');
		$this->daiPrintInfo('publisher');
	
		// (c)
		$this->SetXY(20 * $k, $this->GetY() + 6.6 * $k);
		$this->daiPrint("<b style=\"font-family:calibrib\">©" . date('Y') . '</span> ' . $this->lang->copyright->de);
	
		// terms
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->daiPrint($this->lang->terms->de);
		$this->SetXY(20 * $k, $this->GetY() + 3.3 * $k);
		$this->daiPrint('<span style="color:rgb(128,130,133)">' . $this->lang->terms->en . '</span>');

	}
	
	// some helper functions
	
	/**
	 * creates font file if font mssing
	 * @param unknown $file
	 */
	public function importMissingFonts() {
	
		$fonts = array(
			"calibri",
			"calibril",
			"calibrib"
		);
	
		$success = true;
		
		foreach ($fonts as $font) {
			if (!file_exists("{$this->settings['tcpdf_path']}/fonts/$font.php")) {
				if (!is_writable("{$this->settings['tcpdf_path']}/fonts")) {
					throw new \Exception("TCPDF fonts directory not writable");
				}
				if (TCPDF_FONTS::addTTFfont("{$this->settings['files_path']}/$font.ttf", 'TrueTypeUnicode', 32) === false) {
					$this->logger->warning("font $font not installed");
					$success = false;
				} else {
					$this->logger->debug("font $font successfull installed");
				}
			} else {
				//$this->logger->debug("font $font allready installed");
			}
		}
	
		if (!$success) {
			throw new \Exception("TCPDF fonts missing & could not be installed");
		}
	
	}
	
	/**
	 * wrapper fro some writeHTML functions
	 * 
	 * @param unknown $html
	 * @param string $font
	 * @param unknown $cell
	 */
	public function daiPrint($html, $font = '1', $cell = array()) {
	
		$this->daiFont($font);
	
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
	public function daiPrintInfo($field, $font = '1') {
		if (isset($this->metadata[$field]) and ($this->metadata[$field] == '')) {
			return;
		}
		$this->daiPrint($this->lang->$field->de . '<span style="color:rgb(128,130,133)">' . ' / ' . $this->lang->$field->en . (isset($this->metadata[$field]) ? '</span> <span style="font-family:calibrib">' . $this->metadata[$field] . '</span>' : ''), $font);
	}
	
	
	/**
	 * selects one of the rpedefiend font configurations
	 * @param unknown $font
	 */
	public function daiFont($font) {
	
		// reset
		$this->setFontSpacing(0);
		
		$f = $this->unitScale;
	
		switch($font) {
				
			case '1':
				$this->SetFont('calibril', '', 7, '', true);
				break;
					
			case '1.5':
				$this->SetFont('calibril', '', 8, '', true);
				break;
	
			case '2':
				$this->SetFont('calibri', '', 13, '', true);
				break;
	
			case '3':
				$this->SetFont('calibrib', '', 13, '', true);
				break;
	
			case 'xs':
				$this->SetFont('calibril', '', 6, '', true);
				break;
					
			case 'h1':
				$this->setFontSpacing(0.5);
				$this->SetFont('calibril', '', 30 * $f, '', true);
				break;
	
			case 'h2':
				$this->setFontSpacing(0.3);
				$this->SetFont('calibril', '', 9 * $f, '', true);
				break;
		}
	
	}
	

	public function __construct($smallmode = false) {
	
		$this->unitScale = 1;
		$format = 'A4';
		if ($smallmode == true) {
			$this->unitScale = 0.5;
			$format = 'A5';
			$this->smallMode = $smallmode;
		}
		

		parent::__construct('P', 'mm', $format, true, 'UTF-8', false, false);
	}

	
	public function Header() {}
	public function Footer() {}
}
