<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Model_Distance
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'km',
                'label' => 'Kilometers',
            ),
            array(
                'value' => 'mi',
                'label' => 'Miles',
            ),
            array(
                'value' => 'choose',
                'label' => 'Allow User To Choose',
            ),
        );
    }
}
