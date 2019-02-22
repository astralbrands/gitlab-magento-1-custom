<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amlocator/tab/general.phtml');
    }

    public function getRegionsUrl()
    {
        return $this->getUrl('adminhtml/json/countryRegion');
    }

    protected function _prepareForm()
    {

        $helper = Mage::helper('amlocator');
        $model = Mage::registry('current_location');
        $helperImage = Mage::helper('amlocator/image');

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'general_form', array(
                'legend' => $helper->__('General Information')
            )
        );

        $fieldset->addField(
            'name', 'text', array(
                'label'    => $helper->__('Location name'),
                'required' => true,
                'name'     => 'name',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id', 'multiselect', array(
                    'name'     => 'store_id[]',
                    'label'    => $helper->__('Store View'),
                    'title'    => $helper->__('Store View'),
                    'required' => true,
                    'values'   => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreValuesForForm(false, true),
                )
            );
        } else {
            $fieldset->addField(
                'store_id', 'hidden', array(
                    'name'  => 'store_id[]',
                    'value' => Mage::app()->getStore(true)->getId()
                )
            );
        }


        $fieldset->addField('country', 'select', array(
                'name'  => 'country',
                'required' => true,
                'class'    => 'countries',
                'label'     => 'Country',
                'values'    => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
            )
        );




        $fieldset->addField('state_id', 'select', array(
                'name'  => 'state_id',
                'label'     => 'State/Province',
            )
        );

        $fieldset->addField('state', 'text', array(
                'name'  => 'state',
                'label'     => 'State/Province',

            )
        );


        $fieldset->addField(
            'city', 'text', array(
                'label'    => $helper->__('City'),
                'required' => true,
                'name'     => 'city',
            )
        );

        $fieldset->addField(
            'description', 'editor', array(
                'label'    => $helper->__('Description'),
                'required' => true,
                'config'    => Mage::getSingleton('amlocator/wysiwygConfig')->getConfig(),
                'name'     => 'description',
            )
        );


        $fieldset->addField(
            'zip', 'text', array(
                'label'    => $helper->__('Zip'),
                'required' => true,
                'name'     => 'zip',
            )
        );

        $fieldset->addField(
            'address', 'text', array(
                'label'    => $helper->__('Address'),
                'required' => true,
                'name'     => 'address',
            )
        );

        $fieldset->addField(
            'phone', 'text', array(
                'label'    => $helper->__('Phone Number'),
                'name'     => 'phone',
            )
        );

        $fieldset->addField(
            'email', 'text', array(
                'label'    => $helper->__('E-mail Address'),
                'name'     => 'email',
            )
        );

        $fieldset->addField(
            'website', 'text', array(
                'label'    => $helper->__('Website URL'),
                'name'     => 'website',
            )
        );

        $fieldset->addField(
            'status', 'select', array(
                'label'    => $helper->__('Status'),
                'required' => true,
                'name'     => 'status',
                'values'   => array('1' => 'Enabled', '0' => 'Disabled'),
            )
        );

        $fieldset->addField(
            'position', 'text', array(
                'class'    => 'validate-number',
                'label'    => $helper->__('Position'),
                'required' => false,
                'name'     => 'position',
            )
        );

        $fieldset->addField(
            'photo', 'image', array(
                'label' => $helper->__('Location photo'),
                'name'  => 'photo',

            )
        );

        $data = array_merge(
			$model->getData(),
			array("photo" => $helperImage->getImageUrl($model->getPhoto()))
		);
		
		if (array_key_exists('store_id', $data) && is_array($data['store_id'])) {
			$data['store_id'] = implode(',', $data['store_id']);
		}
			
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }


}