<pre>
<?php

/**
 * this shows how dfm can be used outdie the ojs envoronment
 */

try {

    $path = dirname(dirname(__FILE__));

    require_once($path . '/classes/loader.class.php');

    $loader = new \dfm\loader();

    $logger = null;



    $settings = (object) array(
        'tmp_path'		=> $path . '/test',
        'lib_path'		=> $path . '/lib',
        'dfm_path'		=> $path,
        'theme'         => 'dai_tcpdf_theme'
    );

    if (!$loader->load($logger, $settings)) {
        echo 'error loading dfm'; die();
    }

    $tcpdf_fm_creator = new \dfm\tcpdf_fm_creator($logger, $settings);

    $meta = array(
        'article_author'	=> 'The Author',
        'article_title'		=> 'The Title',
        'editor'			=> 'The Editor',
        'journal_title'		=> 'The Journal',
        'journal_url'		=> 'www.thejournal.org',
        'pages'				=> '1 to 6',
        'pub_id'			=> 'urn:666:666:666',
        'publisher'			=> 'the publisher',
        'url'				=> 'www.thejournal.org',
        'urn'				=> 'urn:666:666:666',
        'volume'			=> '6',
        'year'				=> '2107',
        'zenon_id'			=> '666666666'
    );

    if (in_array('tcpdf_fm_creator', $settings->registry['generators'])) {
        $logger->log('Start Frontpage Update');
        $tcpdf_fm_creator->createMetadata($meta);

        $newFrontmatterFile = $tcpdf_fm_creator->createFrontPage();
        $tmpFile = $tcpdf_fm_creator->updateFrontpage("$path/test/test.pdf", $newFrontmatterFile, false);
        $tmpFile = $tcpdf_fm_creator->updatePDFMetadata($tmpFile);
        unlink($newFrontmatterFile);
        unlink($tmpFile . '_original');

    } else {
        $logger->warning('TCPDF FM Creator not ready');
    }

    $logger->dumpLog(false, true);



} catch (Exception $e) {
    echo "\n ERROR: " . $e->getMessage();
}


?>
</pre>
