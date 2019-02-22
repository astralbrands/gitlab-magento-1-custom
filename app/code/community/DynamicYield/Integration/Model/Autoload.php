<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Model_Autoload
 */
class DynamicYield_Integration_Model_Autoload extends Varien_Event_Observer
{
    /**
     * Observer function for the event 'controller_front_init_before'
     */
    public function autoload() {
        spl_autoload_register(array($this, 'load'), true, true);
    }

    /**
     * This function loads 3rd party classes from lib directory
     * - Symfony\Component\EventDispatcher
     * - Aws
     * - Guzzle
     *
     * @param string $class
     */
    public static function load($class) {
        if (preg_match('#^(Symfony\\\\Component\\\\EventDispatcher|Aws|Guzzle)\b#', $class)) {
            $file = Mage::getBaseDir('lib') . '/DynamicYield/' . str_replace('\\', '/', $class) . '.php';
            require_once($file);
        }
    }
}
