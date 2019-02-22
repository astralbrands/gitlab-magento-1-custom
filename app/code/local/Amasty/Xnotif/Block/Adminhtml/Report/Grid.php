<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */

/**
 * Adminhtml coupons report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Amasty_Xnotif_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    public function getResourceCollectionName()
    {
        return 'productalert/stock_collection';
    }

    protected function _prepareCollection()
    {
        //leave empty
    }

    public function getCollection()
    {
         if ($this->_collection === null) {
             $collection = Mage::getModel('productalert/stock')->getCollection();
             $select = $collection->getSelect();
             $productTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
             $select->joinLeft(
                 array('p'=> $productTable),
                 'main_table.product_id = p.entity_id'
             );

             $select->order('add_date desc');

             $this->setCollection($collection);
         }
         return $this->_collection;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    => Mage::helper('salesrule')->__('SKU'),
            'sortable'  => false,
            'index'     => 'sku'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('salesrule')->__('Email'),
            'sortable'  => false,
            'index'     => 'email',
            'renderer'  => 'amxnotif/adminhtml_report_renderer_email'
        ));

        $this->addColumn('add_date', array(
            'header'            => Mage::helper('salesrule')->__('Subscribtion Date'),
            'index'             => 'add_date',
            'width'             => 100,
            'sortable'          => false,
            'renderer'          => 'adminhtml/report_sales_grid_column_renderer_date'
        ));

        return parent::_prepareColumns();
    }

    public function getCountTotals()
    {
        if (!$this->getTotals()) {
            $totalsCollection = $this->getCollection();

            if (count($totalsCollection->getItems()) < 1) {
                $this->setTotals(new Varien_Object());
            } else {
                foreach ($totalsCollection->getItems() as $item) {
                    $this->setTotals($item);
                    break;
                }
            }
        }

        return $this->_countTotals;
    }
}
