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
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @copyright  Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */
class Rocketweb_ShoppingFeeds_Model_Core_Config extends Varien_Simplexml_Config
{
    /**
     * Class construct
     *
     * @param mixed $sourceData
     */
    public function __construct($sourceData = null)
    {
        $this->_elementClass = 'RocketWeb_ShoppingFeeds_Model_Core_Config_Element';
        parent::__construct($sourceData);
    }
}
