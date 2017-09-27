<?php
namespace das;
class article_selector {

    public $path;
    public $url;

    function __construct($path, $url) {
        $this->path = $path;
        $this->url = $url;
    }

    function setTemplateEnvironment() {
        $templateMgr =& \TemplateManager::getManager();
        $templateMgr->assign('additionalHeadData', $templateMgr->get_template_vars('additionalHeadData') .
            $this->_js_strings() . "\n<link rel='stylesheet' href='{$this->url}/article_picker.css' type='text/css' />\n<script src='{$this->url}/article_picker.js' ></script>");
        $templateMgr->register_function('article_picker', array($this, "article_picker"));
    }

    private function _js_strings() {
        $strings = ['selectObjectType','goBack', 'objectType'];
        $translations = array();
        foreach ($strings as $string) {
            $translations[$string] = \AppLocale::Translate('plugins.generic.as.' . $string);
        }
        return '<script>var as_js_strings = ' . json_encode($translations) . '</script>';
    }

    public function article_picker() {
        $templateMgr =& \TemplateManager::getManager();
        echo $templateMgr->fetch($this->path .'/article_picker.tpl');
    }

}
