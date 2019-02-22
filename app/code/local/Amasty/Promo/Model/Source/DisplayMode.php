<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Source_DisplayMode
{
    const MODE_POPUP = 0;
    const MODE_INLINE = 1;

    public function toOptionArray()
    {
        return array(
            array('value' => self::MODE_POPUP, 'label' => Mage::helper('ampromo')->__('Popup')),
            array('value' => self::MODE_INLINE, 'label' => Mage::helper('ampromo')->__('Inside Page')),
        );
    }
}
