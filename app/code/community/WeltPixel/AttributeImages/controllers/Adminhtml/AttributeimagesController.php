<?php

class WeltPixel_AttributeImages_Adminhtml_AttributeimagesController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('weltpixel/attributeimages');
    }

    public function preDispatch() {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
    }

    public function indexAction() {

        $this->_title($this->__('WeltPixel'))->_title($this->__('AttributeImages'));
        $this->loadLayout();
        $this->_setActiveMenu('weltpixel');
        $this->_addContent($this->getLayout()->createBlock('weltpixel_attributeimages/adminhtml_attributeimages'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('weltpixel_attributeimages/adminhtml_attributeimages_grid')->toHtml()
        );
    }

    public function editAction() {
        $this->_title($this->__('WeltPixel'))->_title($this->__('Add Image to Attribute'));

        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('weltpixel_attributeimages/attributeimages');
        if ($id) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if ($modelObj = $model->loadByAttribute('attribute_id', $id)) {
                $model = $modelObj;
            }

            if ($model->getId()) {
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $attr = Mage::getModel('catalog/resource_eav_attribute')
                        ->setEntityTypeId($this->_entityTypeId)
                        ->load($id);
                if ($attr->getId()) {
                    $model->setData('attribute_id', $id);
                    $model->setData('attribute_code', $attr->getData('attribute_code'));
                    if ($data) {
                        $model->setData($data)->setId($id);
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('weltpixel_attributeimages')->__('Invalid attribute'));
                    $this->_redirect('*/*/');
                }
            }
        }
        Mage::register('attributesimages', $model);

        $this->loadLayout();
        $this->_setActiveMenu('weltpixel');
        $this->_addContent($this->getLayout()->createBlock('weltpixel_attributeimages/adminhtml_attributeimages_edit'));
        $this->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $redirectBack = $this->getRequest()->getParam('back', false);

            $model = Mage::getModel('weltpixel_attributeimages/attributeimages');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $modelObj = $model->loadByAttribute('attribute_id', $id);
                if ($modelObj) :
                    $model = $modelObj;
                endif;
            }

            
            if (isset($_FILES['attribute_image']['name']) && $_FILES['attribute_image']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('attribute_image');

                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    // Set media as the upload dir
                    $media_path = Mage::getBaseDir('media') . DS . 'layerednavigation' . DS . 'attributeimages' . DS;

                    // Upload the image
                    $uploader->save($media_path, $_FILES['attribute_image']['name']);

                    $data['attribute_image'] = 'layerednavigation' . DS . 'attributeimages' . DS . $_FILES['attribute_image']['name'];
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $id, '_current' => true));
                    return;
                }
            } elseif (isset($data['attribute_image']['delete']) && $data['attribute_image']['delete'] == 1) {
                $data['attribute_image'] = '';
            } else {
                if (isset($data['attribute_image']['value'])) {
                    $data['attribute_image'] = $data['attribute_image']['value'];   
                }                
            }


            if ($model->getId()) :
                $data['id'] = $model->getId();;
            endif;
            $model->setData($data);

            //Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('catalog')->__('The attribute image has been saved.'));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $model->getAttributeId(), '_current' => true));
                } else {
                    $this->_redirect('*/*/', array());
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id, '_current' => true));
                return;
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('weltpixel_attributeimages')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

}