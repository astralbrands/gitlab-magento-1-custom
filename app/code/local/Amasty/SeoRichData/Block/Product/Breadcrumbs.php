<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Breadcrumbs extends Mage_Catalog_Block_Product_Abstract
{
    protected $_result;

    public function _toHtml()
    {
        if (!Mage::getStoreConfig('amseorichdata/breadcrumbs/enabled')
        ) {
            return '';
        }

        $path  = Mage::helper('catalog')->getBreadcrumbPath();

        $itemListElement = array();
        $position = 1;

        foreach ($path as $category) {
            if (empty($category['link']) || empty($category['label']))
                continue;

            $itemListElement[] = array(
                '@type' => 'ListItem',
                'position' => $position,
                'item' => array(
                    '@id' => $category['link'],
                    'name' => $category['label']
                )
            );
            $position++;
        }

        $data['breadcrumbs'] = array(
            '@context' => 'http://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement
        );

        foreach ($data as $section) {
            $json = json_encode($section);
            $this->_result .= "<script type=\"application/ld+json\">{$json}</script>";
        }

        return parent::_toHtml() . $this->_result;
    }
}
