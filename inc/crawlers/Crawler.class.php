<?php

/**
 * Classe parente à tous les crawlers
 */
abstract class Crawler
{
    private $url;
    public $domXpath;
    public static $_PAGE_PATTERN = 'REPLACE_PAGE';

    /**
     * Set URL
     * Don't forget to add the word 'REPLACE_PAGE' pattern in order to replace it later
     */
    public function __construct($url) {
        $this->url = $url;
    }

    /**
     * Validate an URL
     * @return boolean
     */
    abstract protected function validateURL($url);
    /**
     * Return ad info from DOMElement (using xpath)
     * @return array
     */
    abstract protected function getAdInfo(DOMElement $domElement);
    /**
     * Return DOMElements of all ads based on a xpath
     * @return array(DOMElement, ...)
     */
    abstract protected function getAds();


    /**
     * Return content of <title> tag
     */
    public function fetchMainTitle() {
        if (!$this->domXpath) {
            return false;
        }
        return $this->domXpath->query(
            '//head/title/text()'
        )->item(0)->nodeValue;
    }

    /**
     * Return paginated URL depending on replace
     */
    private function getPaginatedUrl($page = 1) {
        return str_replace(self::$_PAGE_PATTERN, $page, $this->url);
    }

    /**
     * Return HTML content of a given URL
     */
    public function fetchURLContent($page = 1) {
        $url     = $this->getPaginatedUrl($page);
        $headers = array(
          'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
          'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
          'Accept-Language: en-us,en;q=0.5',
          //'Accept-Encoding: gzip,deflate',
          'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
          'Keep-Alive: 115',
          'Connection: keep-alive',
        );

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE,        false);
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $html = curl_exec($ch);

        // Conversion
        $html = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($html));

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code != 200) {
            return false;
        }

        $dom            = new DOMDocument();
        $dom->loadHTML($html);
        $this->domXpath = new DomXPath($dom);

        var_dump($this->domXpath);

        return true;
    }

}