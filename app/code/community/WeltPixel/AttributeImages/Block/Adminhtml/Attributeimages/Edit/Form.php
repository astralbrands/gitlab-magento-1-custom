<?php

class WeltPixel_AttributeImages_Block_Adminhtml_Attributeimages_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        if (Mage::getSingleton('adminhtml/session')->getAttributesImages()) {
            $data = Mage::getSingleton('adminhtml/session')->getAttributesImages();
            Mage::getSingleton('adminhtml/session')->getAttributesImages(null);
        } elseif (Mage::registry('attributesimages')) {
            $data = Mage::registry('attributesimages')->getData();
        } else {
            $data = array();
        }

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('attributesimage_form', array(
            'legend' => Mage::helper('weltpixel_attributeimages')->__('Edit Image')
                ));

        $fieldset->addField('attribute_id', 'text', array(
            'label' => Mage::helper('weltpixel_attributeimages')->__('Attribute Id'),
            'class' => 'required-entry',
            'readonly' => true,
            'required' => true,
            'name' => 'attribute_id',
            'note' => Mage::helper('weltpixel_attributeimages')->__('Attribute Id.'),
        ));

        $fieldset->addField('attribute_code', 'text', array(
            'label' => Mage::helper('weltpixel_attributeimages')->__('Attribute Code'),
            'class' => 'required-entry',
            'readonly' => true,
            'required' => true,
            'name' => 'attribute_code',
            'note' => Mage::helper('weltpixel_attributeimages')->__('Attribute Code.'),
        ));
              
        $fieldset->addField('attribute_image', 'image', array(
            'label' => Mage::helper('weltpixel_attributeimages')->__('Attribute Image'),
            'required' => false,
            'name' => 'attribute_image',
            'note' => Mage::helper('weltpixel_attributeimages')->__('Attribute Image.'),
        ));


        $form->setValues($data);

        return parent::_prepareForm();
    }

}