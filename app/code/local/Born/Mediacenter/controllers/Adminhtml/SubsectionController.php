<?php

/**
 * Class Born_Mediacenter_Adminhtml_SubsectionController
 */
class Born_Mediacenter_Adminhtml_SubsectionController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        //$this->_title($this->__("Mediacenter"));
        $this->_setActiveMenu('mediacenter/subsections');
        $this->_addContent($this->getLayout()->createBlock('mediacenter/adminhtml_subsections', 'subsections'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mediacenter/subsections')->load($id);
        $model->getData();
        if ($model->getEntityId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('subsection_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('mediacenter/subsections');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Mediacenter Manager'), Mage::helper('adminhtml')->__('Item Manager'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('mediacenter/adminhtml_subsections_edit'))
                ->_addLeft($this->getLayout()->createBlock('mediacenter/adminhtml_subsections_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mediacenter')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /* old save action with uploader */
    public function saveAction1()
    {
        if ($data = $this->getRequest()->getPost()) {
            $subsectionModel = Mage::getModel('mediacenter/subsections')->load($this->getRequest()->getParam('id'));
            if ($subsectionModel->getId()) {
                $subsectionModel->setData($data)
                    ->setEntityId($this->getRequest()->getParam('id'));
            } else {
                $subsectionModel->setData($data);
            }

            try {
                if (is_array($data)) {
                    $subsectionModel->save();

                    foreach ($_FILES['mediaupload']['name'] as $key => $image) {
                        if ((isset($key) && $key != '' || $key == 0) && $data['medianame'][$key]) {
                            try {
                                $uploader = new Varien_File_Uploader(array(
                                    'name' => $_FILES['mediaupload']['name'][$key],
                                    'type' => $_FILES['mediaupload']['type'][$key],
                                    'tmp_name' => $_FILES['mediaupload']['tmp_name'][$key],
                                    'error' => $_FILES['mediaupload']['error'][$key],
                                    'size' => $_FILES['mediaupload']['size'][$key]
                                ));
                                // Any extention would work
                                //$uploader->setAllowedExtensions(array('csv','jpeg','gif','png'));
                                $uploader->setAllowRenameFiles(true);

                                $uploader->setFilesDispersion(false);
                                $path = Mage::getBaseDir('media') . DS . 'mediacenter' . DS;
                                $ext = pathinfo($_FILES['mediaupload']['name'][$key], PATHINFO_EXTENSION);
                                $uploader->save($path, $_FILES['mediaupload']['name'][$key]);
                            } catch (Exception $e) {
                                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            }

                            //this way the name is saved in DB
                            $imagedata['name'] = $data['medianame'][$key];
                            $imagedata['file_name'] = $_FILES['mediaupload']['name'][$key];
                            $imagedata['parent_id'] = $subsectionModel->getEntityId();
                            $imagedata['description'] = $data['media_description'][$key];
                            $imagedata['external_url'] = $data['external_down_link'][$key];
                            $imagedata['type'] = $ext;


                            $imagemodel = Mage::getModel('mediacenter/images');
                            $imagemodel->setData($imagedata);
                            try {
                                $imagemodel->save();
                            } catch (Exception $e) {
                                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            }
                        }
                    }
                    $id = $subsectionModel->getEntityId();


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

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $data['customer_group'] = implode(',', $data['customer_group']);
            $subsectionModel = Mage::getModel('mediacenter/subsections')->load($this->getRequest()->getParam('id'));
            if ($subsectionModel->getId()) {
                $subsectionModel->setData($data)
                    ->setEntityId($this->getRequest()->getParam('id'));
            } else {
                $subsectionModel->setData($data);
            }

            try {
                if (is_array($data)) {
                    $subsectionModel->save();
                    for ($i = 0; $i < count($data['file_name']); $i++) {
                        //this way the name is saved in DB
                        $imagedata['name'] = $data['name'][$i];
                        $imagedata['file_name'] = $data['file_name'][$i];
                        $imagedata['parent_id'] = $subsectionModel->getEntityId();
                        $imagedata['description'] = $data['description'][$i];
                        $imagedata['media_customer_group'] = implode(',', $data['media_customer_group'][$i]);
                        $ext = pathinfo($data['file_name'][$i], PATHINFO_EXTENSION);
                        $imagedata['type'] = $ext;

                        $imagemodel = Mage::getModel('mediacenter/images');
                        $imagemodel->setData($imagedata);
                        try {
                            $imagemodel->save();
                        } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        }

                    }
                    $id = $subsectionModel->getEntityId();


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

    public function galleryGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('gallery.grid')
            ->setGallery($this->getRequest()->getPost('gallery', null));
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('grid_id');

        $collection = Mage::getModel('mediacenter/images')->getCollection()->addFieldToFilter('id', array('in' => $ids));
        foreach ($collection as $image) {
            $image->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mediacenter')->__('Images deleted successfully'));
        $this->_redirectReferer();
    }

    public function massAssignAction()
    {
        $ids = $this->getRequest()->getParam('grid_id');
        $collection = Mage::getModel('mediacenter/images')->getCollection()->addFieldToFilter('id', array('in' => $ids));
        foreach ($collection as $image) {
            $image->setAssign(1)->save();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mediacenter')->__('Images Assigned to subsection'));
        $this->_redirectReferer();
    }

    public function massUnassignAction()
    {
        $ids = $this->getRequest()->getParam('grid_id');

        $collection = Mage::getModel('mediacenter/images')->getCollection()->addFieldToFilter('id', array('in' => $ids));
        foreach ($collection as $image) {
            $image->setAssign(0)->save();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mediacenter')->__('Images Assigned to subsection'));
        $this->_redirectReferer();
    }

    public function galleryGrid1Action()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('gallery.grid')
            ->setGallery($this->getRequest()->getPost('gallery', null));
        $this->renderLayout();
    }

    public function uploadAction()
    {
        if (!empty($_FILES)) {
            $result = array();
            try {
                $uploader = new Varien_File_Uploader("Filedata");
                $uploader->setAllowRenameFiles(true);

                $uploader->setFilesDispersion(false);
                $uploader->setAllowCreateFolders(true);

                $path = Mage::getBaseDir('media') . DS . 'mediacenter' . DS;//ex. Mage::getBaseDir('base') . DS ."my_uploads" . DS

                //$uploader->setAllowedExtensions(array('pdf')); //server-side validation of extension
                $uploadSaveResult = $uploader->save($path, $_FILES['Filedata']['name']);

                $result = $uploadSaveResult['file'];
            } catch (Exception $e) {
                $result = array(
                    "error" => $e->getMessage(),
                    "errorCode" => $e->getCode(),
                    "status" => "error"
                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function updateNameAction()
    {
        $fieldId = (int)$this->getRequest()->getParam('id');
        $name = $this->getRequest()->getParam('update_name');
        if ($fieldId) {
            $model = Mage::getModel('mediacenter/images')->load($fieldId);
            $model->setName($name);
            $model->save();
        }
    }

    public function updateCustomerGroupAction()
    {
        $fieldId = (int)$this->getRequest()->getParam('id');
        $customerGroup = $this->getRequest()->getParam('update_customer_group');
        if ($fieldId) {
            $model = Mage::getModel('mediacenter/images')->load($fieldId);
            $model->setMediaCustomerGroup($customerGroup);
            $model->save();
        }
    }
}