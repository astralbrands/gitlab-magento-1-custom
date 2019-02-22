<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @copyright  Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */


/**
 * Adminhtml system config attributes array field renderer
 *
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_System_Config_Form_Field_Mapcolumns
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_addDelete = true;

    /**
    * Get the current columns in map
    *
    * @return array
    */
    public function getMapColumns()
    {
        $config = Mage::registry('rocketshoppingfeeds_feed')->getConfig();
        $columns = $config['columns_map_product_columns'];

        $return = array(array('label' => Mage::helper('rocketshoppingfeeds')->__('- All Columns -'), 'value' => ''));

        if (is_array($columns)) {
            foreach ($columns as $column) {
                array_push($return, array('label' => $column['column'], 'value' => $column['column']));
            }
        }

        return $return;
    }
}