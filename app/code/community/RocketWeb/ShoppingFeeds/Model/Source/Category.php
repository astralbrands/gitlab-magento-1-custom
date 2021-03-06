<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_ShoppingFeeds_Model_Source_Category extends Varien_Object
{

    /**
     * @param $value
     * @return string
     */
    public function getOptionText($value)
    {
        $ret = array();

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $options = $this->getAllOptions();

        if (is_array($value)) {
            foreach ($value as $key) {
                $ret[] = @$options[$key];
            }
        }

        return implode(",", $ret);
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        // TODO: not ideal to have this passed in registry as this is a backend model. It should have been set in block or controller.
        $feed = Mage::registry('rocketshoppingfeeds/feed');
        $_categories = Mage::helper('rocketshoppingfeeds')->getAllCategories($feed);

        $options = array(array('value' => '', 'label' => '', 'style' => ''));
        foreach ($_categories as $id => $categ) {
            if (isset($categ['name']) && isset($categ['level'])) {
                if ($categ['level'] < 1) {
                    $categ['level'] = 1;
                }

                $options[] = array(
                    'value' => $id,
                    'label' => $categ['name'],
                    'style' => 'padding-left:' . (($categ['level'] - 1) * 7) . 'px;',
                );
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}