<?php

require_once __DIR__.'/Crawler.class.php';

class GumtreeASCrawler extends Crawler {

    public function __construct($url) {
        // Extract page parameter


        if (!$this->validateURL($url)) {
            return false;
        }
        // $url = replace_get_parameter($url, 'o', self::$_PAGE_PATTERN);
        return parent::__construct($url);
    }

    protected function validateURL($url) {
         if (preg_match('#^https?://www\.gumtree\.com.au/.+#i', $url)) {
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
            'title'         => null,
            'picture'       => null,
            'picture_count' => null,
            'location'      => null,
            'price'         => null,
            'date'          => null,
            'pro'           => null,
        ];

        // url
        $tmp = $this->domXpath->query(
            './/h6[@class="rs-ad-title"]//a[@itemprop="url"]/@href',
            $domElement
        );
        $return['url'] = 'https:'.$tmp->item(0)->nodeValue;

        // title
        $tmp = $this->domXpath->query(
            './/h6[@class="rs-ad-title"]/a/span/text()',
            $domElement
        );
        $return['title'] = trim($tmp->item(0)->nodeValue);

        // picture
        $tmp = $this->domXpath->query(
            './/img/@src',
            $domElement
        );
        $return['picture'] = trim(@$tmp->item(0)->nodeValue ?? '//static.leboncoin.fr/img/no-picture.png');

        // picture_count : Not included on Gumtree AUS
        $tmp = $this->domXpath->query(
            './/span[@class="item_imageNumber"]/span/text()',
            $domElement
        );
        $return['picture_count'] = trim(@$tmp->item(0)->nodeValue ?? 1);

        // pro
        $tmp = $this->domXpath->query(
            './/span[@class="ispro"]/text()',
            $domElement
        );
        $tmp = trim(@$tmp->item(0)->nodeValue ?? null);
        $return['pro'] = preg_replace('#\s+#i', ' ', $tmp);

        // location
        $tmp = $this->domXpath->query(
            './/p[@class="rs-ad-location-suburb"]/text()',
            $domElement
        );
        $tmp = trim($tmp->item(0)->nodeValue);
        $return['location'] =  $tmp; //preg_replace('#\s+#i', ' ', $tmp);

        // price
        $tmp = $this->domXpath->query(
            './/span[@class="j-original-price"]/text()',
            $domElement
        );
        $return['price'] = trim(@$tmp->item(0)->nodeValue ?? '');

        // date
        $tmp = $this->domXpath->query(
            './/div[@class="rs-ad-date"]/text()',
            $domElement
        );
        $return['date'] = trim($tmp->item(0)->nodeValue);

       /*
        $return['timestamp'] = convert_date_to_timestamp($return['date']); */

        return $return;
    }

    /**
     * Return DOMElements of all ads based on a xpath
     * @return array(DOMElement, ...)
     */
    public function getAds() {
        return $this->domXpath->query(
            '//ul[@id="srchrslt-adtable"]/li[@class="js-click-block no-job-selling-points"]'
        );
    }

}