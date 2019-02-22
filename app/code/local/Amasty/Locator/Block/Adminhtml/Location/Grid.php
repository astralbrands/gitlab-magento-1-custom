<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amlocator/location')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    protected function _prepareColumns()
    {

        $helper = Mage::helper('amlocator');

        $this->addColumn(
            'name', array(
                'header' => $helper->__('Location'),
                'index'  => 'name'
            )
        );


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id', array(
                    'header'                    => $helper->__('Store View'),
                    'index'                     => 'store_id',
                    'type'                      => 'store',
                    'store_all'                 => true,
                    'store_view'                => true,
                    'sortable'                  => false,
                    'filter_condition_callback' => array($this,
                                                         '_filterStoreCondition'),
                )
            );
        }

        $this->addColumn(
            'country', array(
                'header' => $helper->__('Country'),
                'index'  => 'country',
                'type'   => 'country',
            )
        );

        $this->addColumn(
            'city', array(
                'header' => $helper->__('City'),
                'index'  => 'city',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'zip', array(
                'header' => $helper->__('Zip'),
                'index'  => 'zip',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'address', array(
                'header' => $helper->__('Address'),
                'index'  => 'address',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'position', array(
                'header' => $helper->__('Position'),
                'index'  => 'position',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'status', array(
                'header'  => $helper->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => $helper->getVisibilities(),

            )
        );
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete', array(
                'label' => $this->__('Delete'),
                'url'   => $this->getUrl('*/*/massDelete'),
            )
        );
        return $this;
    }

    public function getRowUrl($model)
    {
        return $this->getUrl(
            '*/*/edit', array(
                'id' => $model->getId(),
            )
        );
    }

}