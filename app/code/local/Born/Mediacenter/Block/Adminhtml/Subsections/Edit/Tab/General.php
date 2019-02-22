<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tab_General
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('subsection_form', array('legend' => Mage::helper('mediacenter')->__('General')));

        $fieldset->addField('subsection_name', 'text', array(
            'label' => Mage::helper('mediacenter')->__('Subsection Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'subsection_name',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('mediacenter')->__('Status'),
            'name' => 'is_active',
            'values' => array(
                array(
                    'value' => '1',
                    'label' => Mage::helper('mediacenter')->__('Yes'),
                ),
                array(
                    'value' => '0',
                    'label' => Mage::helper('mediacenter')->__('No'),
                ),
            ),
        ));
		$groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionArray();
 
        $fieldset->addField('customer_group', 'multiselect',array(
            'label'    =>  Mage::helper('mediacenter')->__('Customer Group'),
            'width'     =>  '100',
            'index'     =>  'customer_group',
            'type'      =>  'options',
            'values'   =>  $groups,
			'name'		=> 'customer_group'
        ));
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('subsection_data')) {
            $form->setValues(Mage::registry('subsection_data')->getData());
        }
        return parent::_prepareForm();
    }

}

?>