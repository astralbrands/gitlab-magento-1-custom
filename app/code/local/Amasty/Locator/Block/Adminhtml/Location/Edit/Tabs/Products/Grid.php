<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location_Edit_Tabs_Products_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(false);
        $this->setId('amlocatorGridPr');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('visibility', array('neq' => 1));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_category', array(
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'in_category',
                'values'           => $this->_getSelectedProducts(),
                'align'            => 'center',
                'index'            => 'entity_id',
                'filter'           => false
            )
        );

        $this->addColumn(
            'entity_id', array(
                'header'   => Mage::helper('catalog')->__('ID'),
                'sortable' => true,
                'width'    => '60',
                'index'    => 'entity_id'
            )
        );

        $this->addColumn(
            'name1', array(
                'header' => Mage::helper('catalog')->__('Name'),
                'index'  => 'name'
            )
        );

        $this->addColumn(
            'sku', array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width'  => '80',
                'index'  => 'sku'
            )
        );

        $this->addColumn(
            'price', array(
                'header'        => Mage::helper('catalog')->__('Price'),
                'type'          => 'currency',
                'width'         => '1',
                'currency_code' => (string)Mage::getStoreConfig(
                    Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE
                ),
                'index'         => 'price'
            )
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', array('_current' => true));
    }

    protected function _getSelectedProducts()
    {

        $products = $this->getSelectedProducts();
        if (!is_array($products)) {
            $products = $this->getSavedProducts();
        }
        return $products;
    }

    public function getSavedProducts()
    {
        if (Mage::registry('current_location')) {
            return Mage::registry('current_location')->getProductId();
        }
    }

    protected function _getRuleId()
    {
        return $this->getRequest()->getParam('id', 0);
    }

    public function getRowUrl($item)
    {
        return 'javascript:void(0)';
    }
}
