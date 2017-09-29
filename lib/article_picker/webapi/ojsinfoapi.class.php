<?php

class ojsinfoapi extends server {

    private $_ojsUser;
    private $_locale;

    function __construct($data, $logger, array $settings = array()) {

        parent::__construct($data, $logger, $settings);
        //$this->log->log('>>>' . print_r($this->data,1));
        //$this->log->log('>>>' . print_r($_POST,1));
    }

    function start() {
        // where am I?
        preg_match('#((.+)\/plugins\/(.*)\/article)\_picker#', dirname(__file__), $m);
        $ojs_path = $m[2];
        $plugin_path = $m[3];

        // load OJS
        if (defined(OJS_PRESENT) and OJS_PRESENT) {
            require_once($ojs_path . '/tools/bootstrap.inc.php');
        }

        // get session
        $sessionManager =& SessionManager::getManager();
        $session =& $sessionManager->getUserSession();

        // is logged in
        if (!$session->user) {
            throw new Exception("no user logged in");
        }

        $this->_ojsUser = $session->user;
        $this->log->debug('access allowed for user ' . $this->_ojsUser->getUsername());

    }

    function journals() {
        $journalDao =& DAORegistry::getDAO('JournalDAO');
        $journals =& $journalDao->getJournals();

        $this->return['journals'] = array();

        foreach ($journals->records as $record) {
            if ($this->_isAllowed($record['journal_id'])) {
                $journal =& $journalDao->getJournal($record['journal_id']);
                $this->return['journals'][$record['journal_id']] = $journal->getLocalizedTitle();
            }
        }
    }

    function issues() {

        $journalId = $this->data['journal'];

        $this->log->log('a' . print_r($this->data,1));

        $this->return['issues'] = array();

        if (!$this->_isAllowed($journalId)) {
            return;
        }

        $issueDAO =& DAORegistry::getDAO('IssueDAO');
        $result = $issueDAO->getIssues($journalId);

        while ($record = $result->next()) {
            $title = implode(' | ',
                array_filter(
                    array(
                        $record->getNumber(),
                        $record->getVolume(),
                        $record->getYear(),
                        $record->getLocalizedTitle()
                    ),
                    function($item) {return $item !== null; }
                )
            );

            $this->return['issues'][$record->getId()] = $title;
        }
    }

    function articles() {

        $issueId = $this->data['issue'];

        $publishedArticleDAO =& DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticles = $publishedArticleDAO->getPublishedArticles($issueId);
        foreach ($publishedArticles as $record) {
            if ($this->_isAllowed($record->getJournalId())) {
                $this->return['articles'][$record->getId()] = $record->getFirstAuthor() . ': ' . $record->getLocalizedTitle();
            }
        }
    }


    function galleys() {

        $articleId = $this->data['article'];

        $articleDAO =& DAORegistry::getDAO('ArticleDAO');
        $article = $articleDAO->getArticle($articleId);

        $journalId = $article->getJournalId();

        if (!$this->_isAllowed($journalId)) {
            return;
        }

        $galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = $galleyDao->getGalleysByArticle($articleId);


        foreach ($galleys as $record) {
            $this->return['galleys'][$record->getId()] = $record->getId() . ' | ' . $record->getGalleyLabel();
        }
    }

    private function _isAllowed($journalId) {
        $roleDAO = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDAO->getRolesByUserId($this->_ojsUser->getId(), $journalId);
        foreach ($roles as $role) {
            if (in_array($role->getRolePath(), $this->settings['roleWhitelist'])) {
                $this->log->debug("journal #$journalId may be accessed by you as " . $role->getRolePath());
                return true;
            }
        }
        $this->log->debug("no suitable role to edit journal #$journalId");
        return false;
    }


    function finish() {

    }
}


?>