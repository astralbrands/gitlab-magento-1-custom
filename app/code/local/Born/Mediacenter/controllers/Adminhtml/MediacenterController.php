<?php

/**
 * Class Born_Mediacenter_Adminhtml_MediacenterController
 */
class Born_Mediacenter_Adminhtml_MediacenterController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        //$this->_title($this->__("Mediacenter"));
        $this->_setActiveMenu('mediacenter/mediacenterbackend');
        $this->_addContent($this->getLayout()->createBlock('mediacenter/adminhtml_mediacenter', 'mediacenter'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mediacenter/mediacenter')->load($id);
        $model->getData();
        if ($model->getEntityId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('mediacenter_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('mediacenter/mediacenterbackend');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Mediacenter Manager'), Mage::helper('adminhtml')->__('Item Manager'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('mediacenter/adminhtml_mediacenter_edit'))
                ->_addLeft($this->getLayout()->createBlock('mediacenter/adminhtml_mediacenter_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mediacenter')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $subsectionId = $data['subsection_id'];
			$data['customer_group'] = implode(',',$data['customer_group']);
            if (is_array($subsectionId)) {
                $data['subsection_id'] = implode(',', $subsectionId);
            }

            $model = Mage::getModel('mediacenter/mediacenter');

            $model->setData($data)
                ->setEntityId($this->getRequest()->getParam('id'));
            try {
                if (is_array($data)) {
                    if (is_array($data['subsection_id'])) {
                        $data['subsection_id'] = implode(',', $data['subsection_id']);
                    }
                    $model->save();
                    $id = $model->getEntityId();


                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mediacenter')->__('Mediacenter saved successfully'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);

                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $id));
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mediacenter')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
}