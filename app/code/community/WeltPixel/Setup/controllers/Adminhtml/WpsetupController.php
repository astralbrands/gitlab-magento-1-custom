<?php

class WeltPixel_Setup_Adminhtml_WpsetupController extends Mage_Adminhtml_Controller_Action {

    protected $_importInputFile = 'import_file';

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('weltpixel/importexport');
    }

    public function indexAction() {

        $this->loadLayout()
                ->_setActiveMenu('weltpixel')
                ->_title($this->__('WeltPixel Import / Export'));

        $leftBlock = $this->getLayout()
                ->createBlock('core/template', 'weltpixel-importexport-left')
                ->setTemplate('weltpixel/importexport/left.phtml');

        $this->_addLeft($leftBlock);

        $cmsblockBlock = $this->getLayout()
                ->createBlock('core/template', 'weltpixel-cmsblock-block')
                ->setTemplate('weltpixel/importexport/cmsblock.phtml');

        $this->_addContent($cmsblockBlock);

        $cmspageBlock = $this->getLayout()
                ->createBlock('core/template', 'weltpixel-cmspage-block')
                ->setTemplate('weltpixel/importexport/cmspage.phtml');

        $this->_addContent($cmspageBlock);

        $settingsBlock = $this->getLayout()
                ->createBlock('core/template', 'weltpixel-settings-block')
                ->setTemplate('weltpixel/importexport/settings.phtml');

        $this->_addContent($settingsBlock);

        $this->getLayout()->getBlock('head')->addJs('weltpixel/jquery-1.11.1.js');
        $this->getLayout()->getBlock('head')->addJs('weltpixel/jquery.noconflict.js');


        $this->renderLayout();
    }

    public function exportimportAction() {
        $hasError = false;
        $postData = $this->getRequest()->getPost();

        if (!$this->getRequest()->isPost() || !$postData) {
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please submit the form again for export.'));
            $this->_redirect('*/*');
        }

        $entity = $postData['exportimport_entity'];
        $storeFromId = $postData['exportimport_store_from'];
        $storeToId = $postData['exportimport_store_to'];
        $forced = $postData['exportimport_forced'];

        if (!$entity) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select an entity for exportimport.'));
        }

        if (!$storeFromId) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select a store for export.'));
        }

        if (!$storeToId) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select a store for export.'));
        }

        if ($hasError) {
            $this->_redirect('*/*', array('tab' => $entity));
        } else {

            try {
                $exportModel = Mage::getModel('weltpixel_setup/export_' . $entity, $storeFromId);
                $content = $exportModel->export();

                $options = array(
                    'storeId' => $storeToId,
                    'importFile' => $content['value']
                );

                $importModel = Mage::getModel('weltpixel_setup/import_' . $entity, $options);
                $importModel->import($forced);
                
            } catch (Exception $ex) {
                $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('An error occured during the export/import.'));
                $hasError = true;
            }
        }
        
        if ($hasError) {
            $this->_redirect('*/*', array('tab' => $entity));
        } else {
            $this->_getSession()->addSuccess(Mage::helper('weltpixel_setup')->__('Export / Import fininshed successfully.'));
            $this->_redirect('*/*', array('tab' => $entity));
        }
        
    }

    public function exportentityAction() {

        $hasError = false;
        $postData = $this->getRequest()->getPost();

        if (!$this->getRequest()->isPost() || !$postData) {
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please submit the form again for export.'));
            $this->_redirect('*/*');
        }

        $entity = $postData['export_entity'];
        $storeId = $postData['export_store'];


        if (!$storeId) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select a store for export.'));
        }

        if (!$entity) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select an entity for export.'));
        }


        if ($hasError) {
            $this->_redirect('*/*', array('tab' => $entity));
        } else {


            try {
                $exportModel = Mage::getModel('weltpixel_setup/export_' . $entity, $storeId);
                $fileName = 'export_' . $entity . 's.csv';
                $content = $exportModel->export();

                $this->_prepareDownloadResponse($fileName, $content);
            } catch (Exception $ex) {
                $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('An error occured during the export.'));
                $this->_redirect('*/*', array('tab' => $entity));
            }
        }
    }

    public function importentityAction() {
        $hasError = false;
        $postData = $this->getRequest()->getPost();
        
        if (!$this->getRequest()->isPost() || !$postData) {
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please submit the form again for import.'));
            $this->_redirect('*/*');
        }

        $entity = $postData['import_entity'];
        $storeId = $postData['import_store'];
        $forced = isset($postData['import_forced']) ? $postData['import_forced'] : false;
        
        if (!$storeId) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select a store for import.'));
        }

        if (!$entity) {
            $hasError = true;
            $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please select an entity for import.'));
        }

        if (!$hasError) {
            try {

                $postData = $this->_handleFile($postData, $entity);

                if (isset($postData[$this->_importInputFile])) {

                    $options = array(
                        'storeId' => $storeId,
                        'importFile' => $postData[$this->_importInputFile]
                    );

                    $importModel = Mage::getModel('weltpixel_setup/import_' . $entity, $options);
                    $importModel->import($forced);
                } else {
                    $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('Please upload a valid csv file.'));
                    $hasError = true;
                }
            } catch (Exception $ex) {
                $this->_getSession()->addError(Mage::helper('weltpixel_setup')->__('An error occured during the export.'));
                $hasError = true;
            }
        }


        if ($hasError) {
            $this->_redirect('*/*', array('tab' => $entity));
        } else {
            $this->_getSession()->addSuccess(Mage::helper('weltpixel_setup')->__('Import fininshed successfully.'));
            $this->_redirect('*/*', array('tab' => $entity));
        }
    }

    protected function _handleFile($postData, $entity) {

        $inputFile = $this->_importInputFile;

        if (isset($_FILES[$inputFile]['name']) and (file_exists($_FILES[$inputFile]['tmp_name']))) {
            try {

                $uploader = new Varien_File_Uploader($inputFile);
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('var') . DS . 'import' . DS . $entity;
                $result = $uploader->save($path, $_FILES[$inputFile]['name']);
                $postData[$inputFile] = $path . DS . $result['file'];
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        return $postData;
    }

}