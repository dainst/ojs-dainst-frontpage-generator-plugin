<?php
namespace das {
    class article_selector {

        public $path;
        public $url;

        function __construct($path, $url) {
            $this->path = $path;
            $this->url = $url;
        }

        function setTemplateEnvironment() {
            $templateMgr =& \TemplateManager::getManager();
            $templateMgr->assign('additionalHeadData', $this->_js_strings() . "\n<link rel='stylesheet' href='{$this->url}/article_picker/article_picker.css' type='text/css' />\n<script src='{$this->url}/article_picker/article_picker.js' ></script>");

        }

        function handleApiCall() {
            define('OJS_PRESENT', true);
            $includePath = $this->path ;
            include("api/index.php");
            die();
        }

        private function _js_strings() {
            $strings = ['selectObjectType','goBack', 'objectType'];
            $translations = array();
            foreach ($strings as $string) {
                $translations[$string] = \AppLocale::Translate('plugins.generic.as.' . $string);
            }
            return '<script>var as_js_strings = ' . json_encode($translations) . '</script>';
        }
    }
}
?>