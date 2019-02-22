<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Block_Html_Pager extends Mage_Page_Block_Html_Pager
{
    /**
     * Remove p=1 from url
     * e.g. "http://example.net/category.html?p=1" => "http://example.net/category.html"
     *
     * @param $page
     *
     * @return string
     */
    public function getPageUrl($page)
    {
        return $this->getPagerUrl(array($this->getPageVarName() => $page == 1 ? null : $page));
    }
}
