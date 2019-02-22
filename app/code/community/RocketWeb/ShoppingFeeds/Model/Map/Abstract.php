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
 * Class RocketWeb_ShoppingFeeds_Model_Map_Abstract
 */
class RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    protected static $_adapter = array();

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Adapter_Abstract
     */
    public static function getAdapter()
    {
        return self::$_adapter[count(self::$_adapter) - 1];
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Map_Abstract
     */
    public function addAdapter($adapter)
    {
        array_push($this::$_adapter, $adapter);
        return $this;
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Adapter_Abstract
     */
    public function popAdapter()
    {
        return array_pop($this::$_adapter);
    }
}