<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.5
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2013 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes
    extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Initializes the block
     */
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_productattributes';
        $this->_removeButton('add');
    }

    /**
     * Retrieve brands block
     *
     * @return string
     */
    public function getInitBrandsHtml()
    {
        return $this->getChildHtml('remarketing_attributes_init');
    }

    /**
     * Retrieve the grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('remarketing_attributes_grid');
    }

    /**
     * Sets that do not have the brand attribute
     *
     * @return array
     */
    public function setsWithoutBrandAttribute()
    {
        $sets = array();

        $allSets = Mage::registry('productattribute_sets');

        /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $set */
        foreach ($allSets as $set) {
            if ($set->getBrandAttributeCode() == null) {
                array_push($sets, $set);
            }
        }

        return $sets;
    }
}

