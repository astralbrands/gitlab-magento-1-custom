<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Model_Source_Reviews_Totals
{
    const TOTALS_REVIEWS = 1;
    const TOTALS_VOTES = 2;
    const TOTALS_BOTH = 3;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amseorichdata');

        return array(
            array('value' => self::TOTALS_BOTH, 'label' => $hlp->__('Votes and Reviews')),
            array('value' => self::TOTALS_REVIEWS, 'label' => $hlp->__('Reviews')),
            array('value' => self::TOTALS_VOTES, 'label' => $hlp->__('Votes')),
        );
    }
}
