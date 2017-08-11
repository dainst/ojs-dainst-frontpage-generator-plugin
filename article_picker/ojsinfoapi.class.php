<?php

class ojsinfoapi extends server {
    function start() {
        // where am I?
        preg_match('#((.+)\/plugins\/(.*)\/article)\_picker#', dirname(__file__), $m);
        $ojs_path = $m[2];
        $plugin_path = $m[3];

        // load OJS
        require_once($ojs_path . '/tools/bootstrap.inc.php');

        // get session
        $sessionManager =& SessionManager::getManager();
        $session =& $sessionManager->getUserSession();

        // is logged in
        if (!$session->user) {
            throw new Exception("no user logged in");
        }

        var_dump($this->data);

    }

    function journals() {
        $journalDao =& DAORegistry::getDAO('JournalDAO');
        $journals =& $journalDao->getJournals();

        $this->return['journals'] = array();

        foreach ($journals->records as $record) {
            $journal =& $journalDao->getJournal($record['journal_id']);
            $this->return['journals'][$record['journal_id']] = $journal->getLocalizedTitle();
        }
    }

    function articles() {

        $issue_id = $this->data['issue'];

        $PublishedArticleDAO =& DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticles = $PublishedArticleDAO->getPublishedArticles($issue_id);
        foreach ($publishedArticles as $record) {
            $this->return['articles'][$record->getId()] = $record->getLocalizedTitle();
        }


    }

    function finish() {

    }
}


?>