<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Source_Scope
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amseotoolkit');

        return array(
            array(
                'value' => Amasty_SeoToolKit_Helper_Hrefurl::SCOPE_GLOBAL,
                'label' => $hlp->__('Global')
            ),
            array(
                'value'=> Amasty_SeoToolKit_Helper_Hrefurl::SCOPE_WEBSITE,
                'label' => $hlp->__('Website')
            )
        );
    }
}
