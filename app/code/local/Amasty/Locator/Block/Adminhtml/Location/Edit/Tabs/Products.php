<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */


class Amasty_Locator_Block_Adminhtml_Location_Edit_Tabs_Products
    extends Mage_Adminhtml_Block_Widget_Form
{
    const MODE_ANY = 0;
    const MODE_SELECTED = 1;


    public function _beforeToHtml()
    {
        $this->_initForm();
        return parent::_beforeToHtml();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();

        $model = Mage::registry('current_location');

        if ( $model && $model->getProductId()!='' ) {
            $modeValue = self::MODE_SELECTED;
        } else {
            $modeValue = self::MODE_ANY;
        }

        $fieldset = $form->addFieldset(
            'products_mode_fieldset',
            array('legend' => $this->__('Product Access'),
                  'class'  => 'fieldset-wide')
        );

        $grid = $this->getLayout()->createBlock(
            'amlocator/adminhtml_location_edit_tabs_products_grid'
        );


        $serializer = $this->getLayout()->createBlock(
            'adminhtml/widget_grid_serializer'
        );
        $serializer->initSerializerBlock(
            $grid, 'getSavedProducts', 'selected_products',
            'selected_products'
        );

        $mode = $fieldset->addField(
            'products_access_mode', 'select',
            array(
                'label'  => $this->__('Products, Available in Store'),
                'id'     => 'product_access_mode',
                'name'   => 'product_access_mode',
                'values' => array(
                    self::MODE_ANY      => $this->__('All Products'),
                    self::MODE_SELECTED => $this->__('Selected Products')
                ),
                'value'  => $modeValue
            )
        );

        $fieldset->addField(
            'location_grid', 'hidden',
            array(
                'after_element_html' => $grid->toHtml() . $serializer->toHtml() ,
            )
        );

        $this->setForm($form);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'adminhtml/widget_form_element_dependence'
            )
                ->addFieldMap($mode->getHtmlId(), $mode->getName())
                ->addFieldMap('location_grid', 'location_grid')
                ->addFieldDependence(
                    'location_grid',
                    $mode->getName(),
                    self::MODE_SELECTED
                )
        );
    }
}