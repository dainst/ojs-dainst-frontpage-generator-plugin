<?php
namespace dfm;
class generator extends abstraction {

    const dependencies = "tmppath";

    public $theme;
    public $journalpreset;

    /**
     * a set of data needed for the frontpage
     * it's easier to work from this point on with this, not with OJS-Objects (also, this was created indiependend of the OJS first)
     *
     */
    public $metadata = array(
        'article_author'	=> '###',
        'article_title'		=> '###',
        'editor'			=> '###',
        'issn_online'		=> '',
        'issn_printed'		=> '',
        'issue_tag'			=> '###',
        'journal_title'		=> '###',
        'journal_sub'		=> '',
        'journal_url'		=> '###',
        'pages'				=> '###',
        'pub_id'			=> '###',
        'publisher'			=> '###',
        'url'				=> '###',
        'urn'				=> '###',
        'volume'			=> '###',
        'year'				=> '###',
        'zenon_id'			=> ''
    );
    // '###' -> means missing, will be printed and warning, '' means unset, will not be printed

    function __construct($logger, $settings) {
        parent::__construct($logger, $settings);
        $this->setTheme();
    }

    function setTheme() {
        if (!($this->settings->theme)) {
            throw new \Exception("Theme not set!");
        }
        $tclass = '\dfm\\' . $this->settings->theme;
        $this->log->log("selected theme: $tclass");
        if (!class_exists($tclass)) {
            throw new \Exception("Theme $tclass does not exist");
        }
        $theme = new $tclass($this->log, $this->settings);
        $this->theme = $theme;
    }

    function setJournalPreset($jps) {
        if (!in_array($jps, $this->settings->registry['journalpresets'])) {
            return;
        }
        $tclass = '\dfm\\' . $jps;
        $this->log->log("selected JournalPreset: $tclass");
        if (!class_exists($tclass)) {
            throw new \Exception("JournalPreset $tclass does not exist");
        }
        $preset = new $tclass($this->log, $this->settings);
        $this->journalpreset = $preset;
    }

    function setMetadata($data) {
        foreach ($this->metadata as $key => $value) {
            if (isset($data[$key])) {
                $this->metadata[$key] = $data[$key];
            }
        }

        if ($this->journalpreset and method_exists($this->journalpreset, "setMetadata")) {
            $this->metadata = $this->journalpreset->setMetadata($data);
        }

        if (($this->metadata['issue_tag'] == '###') and isset($this->metadata['volume']) and isset($this->metadata['year'])) {
            $this->metadata['issue_tag'] = "{$this->metadata['volume']} • {$this->metadata['year']}";
        }
        $this->checkMetadata();
    }


    /**
     * checks if metdata is set, else raises an error
     */
    function checkMetadata() {
        foreach($this->metadata as $key => $value) {
            if ($value == '###') {
                $this->log->warning('Metadata ' . $key . ' not set');
            }
        }
    }

}

