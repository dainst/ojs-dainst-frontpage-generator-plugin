<?php
namespace dfm;
class generator extends abstraction {

    const dependencies = "tmppath";

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
        $tclass = '\dfm\\' . $this->settings->theme;
        $this->log->log("selected theme: $tclass");
        $theme = new $tclass($this->log, $this->settings);
        $this->theme = $theme;
    }


}

