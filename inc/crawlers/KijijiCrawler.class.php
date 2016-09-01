<?php

require_once __DIR__.'/Crawler.class.php';

class KijijiCrawler extends Crawler {

    private static $_baseDomain = 'http://www.kijiji.ca';

    public function __construct($url) {
        // Extract page parameter
        if (!$this->validateURL($url)) {
            return false;
        }
        $url = replace_get_parameter($url, 'gpTopAds', 'y');
        $url = $this->replacePageParameterWithPattern($url);
        return parent::__construct($url);
    }

    private function replacePageParameterWithPattern($url) {
        // http://www.kijiji.ca/b-cars-vehicles/fredericton/page-2/c27l1700018?gpTopAds=y
        if (preg_match('#/page-\d+/#i', $url)) {
            return preg_replace('#/page-\d+/#i', '/page-'.self::$_PAGE_PATTERN.'/', $url);
        }
        // http://www.kijiji.ca/b-cars-vehicles/fredericton/c27l1700018?gpTopAds=y
        $pos = strrpos($url, '/');
        $url = substr($url, 0, $pos)
            . '/page-'.self::$_PAGE_PATTERN.'/'
            . substr($url, $pos +1);
        return $url;
    }

    protected function validateURL($url) {
        if (preg_match('#^https?://www\.kijiji\.ca/.+#i', $url)) {
            return true;
        }
        return false;
    }

    /**
     * Return ad info from DOMElement (using xpath)
     * @return array
     */
    public function getAdInfo(DOMElement $domElement) {
        $return = [
            'url'           => null,
            'id'            => null,
            'title'         => null,
            'picture'       => null,
            'picture_count' => null,
            'location'      => null,
            'price'         => null,
            'date'          => null,
            'pro'           => null,
            // Added later, see webroot/get-ads.php
            'latlng'        => null,
        ];
        // url
        $tmp = $this->domXpath->query(
            './/div[@class="title"]/a/@href',
            $domElement
        );
        $return['url'] = self::$_baseDomain.$tmp->item(0)->nodeValue;
        
        // id
        $return['id']  = md5($return['url']);

        // title
        $tmp = $this->domXpath->query(
            './/div[@class="title"]/a/text()',
            $domElement
        );
        $return['title'] = trim($tmp->item(0)->nodeValue);

        // picture
        $tmp = $this->domXpath->query(
            './/div[@class="left-col"]/div[@class="image"]/img/@src',
            $domElement
        );
        $tmp               = $tmp->item(0)->nodeValue;
        $return['picture'] = str_replace('http://', 'https://', $tmp);

        // picture_count
        $return['picture_count'] = null;

        // // pro
        // $tmp = $this->domXpath->query(
        //     './/span[@class="ispro"]/text()',
        //     $domElement
        // );
        // $tmp = trim(@$tmp->item(0)->nodeValue ?? null);
        // $return['pro'] = preg_replace('#\s+#i', ' ', $tmp);

        // // location
        $tmp = $this->domXpath->query(
            './/div[@class="location"]/text()',
            $domElement
        );
        $tmp = trim($tmp->item(0)->nodeValue);
        $return['location'] = $tmp;

        // price
        $tmp = $this->domXpath->query(
            './/div[@class="price"]/text()',
            $domElement
        );
        $tmp             = trim(@$tmp->item(0)->nodeValue ?? '');
        $tmp             = preg_replace('#\s+#i', ' ', $tmp);
        $return['price'] = $tmp != ' ' ? $tmp : '';

        // date
        $tmp = $this->domXpath->query(
            './/span[@class="date-posted"]/text()',
            $domElement
        );
        // var_dump($tmp->item(0));
        // die;
        $return['date']      = trim($tmp->item(0)->nodeValue);
        $return['timestamp'] = $this->convertDateToTimestamp($return['date']);
        if ($return['date'] == '') {
            $return['date'] = 'Today';
        }

        return $return;
    }

    /**
     * Return DOMElements of all ads based on a xpath
     * @return array(DOMElement, ...)
     */
    public function getAds() {
        return $this->domXpath->query(
            '//div[@class="search-item regular-ad"]'
        );
    }

    private function convertDateToTimestamp($date) {
        $tmp = explode('/', $date);
        if ($date == '' || strstr($date, ' ago') !== false || !isset($tmp[2])) {
            return time();
        }
        return mktime(1, 1, 1, $tmp[1], $tmp[0], $tmp[2]);
    }

}