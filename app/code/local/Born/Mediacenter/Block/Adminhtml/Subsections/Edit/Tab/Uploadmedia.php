<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tab_Uploadmedia
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tab_Uploadmedia
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     * @throws Exception
     */
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'enctype' => 'multipart/form-data'
        ));
        $this->setForm($form);
        $fieldset = $form->addFieldset('media_form', array('legend' => Mage::helper('mediacenter')->__('Upload Media')));

        $fieldset->addField('id', 'hidden', array(
            'label' => Mage::helper('mediacenter')->__('id'),
            'value' => $this->getRequest()->getParam('id'),
            'name' => 'id',
        ));

        /*        $fieldset->addField('file', 'file', array(
                    'label'     => Mage::helper('mediacenter')->__('Media Upload'),
                    'required'  => true,
                    'name'      => 'file',
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
                )); */
        $uploaderForm = $fieldset->addField('uploader_form', 'editor', array(
            'name' => 'uploader_form',
            'label' => Mage::helper('mediacenter')->__('Upload form'),
            'required' => false,
        ));
        $uploaderForm = $form->getElement('uploader_form');
        $uploaderFormBlock = $this->getLayout()
            ->createBlock('mediacenter/adminhtml_subsections_edit_renderer_uploadform');

        $uploaderForm->setRenderer($uploaderFormBlock);


        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('media_data')) {
            $form->setValues(Mage::registry('media_data')->getData());
        }
        return parent::_prepareForm();
    }

}
