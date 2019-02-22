<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Source_EnabledFor
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amseotoolkit');

        return array(

            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::TYPE_PRODUCT,
                'label' => $hlp->__('Product')
            ),
            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::TYPE_CATEGORY,
                'label' => $hlp->__('Category')
            ),
            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::TYPE_CMS,
                'label' => $hlp->__('CMS Pages')
            )
        );
    }
}
