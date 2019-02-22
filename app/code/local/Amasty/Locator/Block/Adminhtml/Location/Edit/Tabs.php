<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        $helper = Mage::helper('amlocator');

        parent::__construct();
        $this->setId('location_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($helper->__('Location Information'));
    }

    protected function _prepareLayout()
    {
        $helper = Mage::helper('amlocator');

        $this->addTab(
            'general_section', array(
                'label'   => $helper->__('General Information'),
                'title'   => $helper->__('General Information'),
                'content' => $this->getLayout()->createBlock(
                    'amlocator/adminhtml_location_edit_tabs_general'
                )->toHtml(),
            )
        );
        $this->addTab(
            'custom_section', array(
                'label'   => $helper->__('Location on Map'),
                'title'   => $helper->__('Location on Map'),
                'onClick' => 'alert("asd")',
                'content' => $this->getLayout()->createBlock(
                    'amlocator/adminhtml_location_edit_tabs_map'
                )->toHtml(),
            )
        );

        $productsBlock = $this->getLayout()->createBlock(
            'amlocator/adminhtml_location_edit_tabs_products'
        );


        $this->addTab(
            'products', array(
                'label' => $helper->__('Available Products'),
                'title' => $helper->__('Available Products'),
                'content' => $productsBlock->toHtml(),
            )
        );

        $this->addTab('categoryaccess', array(
            'label'     => Mage::helper('amlocator')->__('Available Categories'),
            'content'   => $this->getLayout()->createBlock('amlocator/adminhtml_location_edit_tabs_category')
                ->setTitle('Available Categories')
                ->toHtml(),
        ));

        return parent::_prepareLayout();
    }

}