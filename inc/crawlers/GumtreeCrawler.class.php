<?php

require_once __DIR__.'/Crawler.class.php';

class GumtreeCrawler extends Crawler {

    public function __construct($url) {
        // Extract page parameter
        if (!$this->validateURL($url)) {
            return false;
        }
        // $url = replace_get_parameter($url, 'o', self::$_PAGE_PATTERN);
        return parent::__construct($url);
    }

    protected function validateURL($url) {
        // if (preg_match('#^https?://www\.leboncoin\.fr/.+#i', $url)) {
        //     return true;
        // }
        return false;
    }

    /**
     * Return ad info from DOMElement (using xpath)
     * @return array
     */
    public function getAdInfo(DOMElement $domElement) {
        $return = [];
        return $return;
    }

    /**
     * Return DOMElements of all ads based on a xpath
     * @return array(DOMElement, ...)
     */
    public function getAds() {
        return false;
    }

}