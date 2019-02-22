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
class RocketWeb_ShoppingFeeds_Model_Locale
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'en-US', 'label' => Mage::helper('rocketshoppingfeeds')->__('English - US (en-US)')),
            array('value' => 'en-AU', 'label' => Mage::helper('rocketshoppingfeeds')->__('English - Australia (en-AU)')),
            array('value' => 'en-CA', 'label' => Mage::helper('rocketshoppingfeeds')->__('English - Canada (en-CA)')),
            array('value' => 'en-GB', 'label' => Mage::helper('rocketshoppingfeeds')->__('English - UK (en-GB)')),
            array('value' => 'cs-CZ', 'label' => Mage::helper('rocketshoppingfeeds')->__('Czech (cs-CZ)')),
            array('value' => 'da-DK', 'label' => Mage::helper('rocketshoppingfeeds')->__('Danish (da-DK)')),
            array('value' => 'nl-NL', 'label' => Mage::helper('rocketshoppingfeeds')->__('Dutch (nl-NL)')),
            array('value' => 'fr-FR', 'label' => Mage::helper('rocketshoppingfeeds')->__('French (fr-FR)')),
            array('value' => 'de-DE', 'label' => Mage::helper('rocketshoppingfeeds')->__('German - Germany (de-DE)')),
            array('value' => 'de-CH', 'label' => Mage::helper('rocketshoppingfeeds')->__('German - Switzerland (de-CH)')),
            array('value' => 'it-IT', 'label' => Mage::helper('rocketshoppingfeeds')->__('Italian (it-IT)')),
            array('value' => 'ja-JP', 'label' => Mage::helper('rocketshoppingfeeds')->__('Japanese (ja-JP)')),
            array('value' => 'pl-PL', 'label' => Mage::helper('rocketshoppingfeeds')->__('Polish (pl-PL)')),
            array('value' => 'pt-BR', 'label' => Mage::helper('rocketshoppingfeeds')->__('Portuguese - Brasil (pt-BR)')),
            array('value' => 'ru-RU', 'label' => Mage::helper('rocketshoppingfeeds')->__('Russian (ru-RU)')),
            array('value' => 'es-ES', 'label' => Mage::helper('rocketshoppingfeeds')->__('Spanish (es-ES)')),
            array('value' => 'sv-SE', 'label' => Mage::helper('rocketshoppingfeeds')->__('Swedish - Sweden (sv-SE)')),
            array('value' => 'no-NO', 'label' => Mage::helper('rocketshoppingfeeds')->__('Norwegian (no-NO)')),
            array('value' => 'tr-TR', 'label' => Mage::helper('rocketshoppingfeeds')->__('Turkish (tr-TR)')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $ret = array();
        foreach ($this->toOptionArray() as $a) {
            $ret[$a['value']] = $a['label'];
        }
        return $ret;
    }

}