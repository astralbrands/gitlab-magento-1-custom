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

/**
 * Class RocketWeb_ShoppingFeeds_Model_Feed_Taxonomy
 */
class RocketWeb_ShoppingFeeds_Model_Feed_Taxonomy extends Mage_Core_Model_Abstract
{
    /**
     * Load the taxonomy provider
     */
    public function getProvider($feed)
    {
        $key = strtolower($feed->getType());
        if (Mage::helper('rocketshoppingfeeds/map')->providerExists(ucwords($key). '_Taxonomy')) {
            return Mage::getSingleton('rocketshoppingfeeds/provider_' . $key . '_taxonomy', array('feed' => $feed));
        }
        return Mage::getSingleton('rocketshoppingfeeds/provider_generic_taxonomy', array('feed' => $feed));
    }
}
