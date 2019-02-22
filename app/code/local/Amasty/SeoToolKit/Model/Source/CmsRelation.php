<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Source_CmsRelation
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amseotoolkit');

        return array(
            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::CMS_ID,
                'label' => $hlp->__('By ID')
            ),
            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::CMS_UUID,
                'label' => $hlp->__('By Hreflang UUID')
            ),
            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::CMS_URLKEY,
                'label' => $hlp->__('By URL Key')
            )
        );
    }
}
