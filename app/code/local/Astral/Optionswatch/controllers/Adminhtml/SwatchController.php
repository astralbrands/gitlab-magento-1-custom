<?php
class Astral_Optionswatch_Adminhtml_SwatchController extends Mage_Adminhtml_Controller_Action{

    public function indexAction(){
        $this->loadLayout()
            ->_setActiveMenu('catalog')
            ->_addContent($this->getLayout()->createBlock('optionswatch/adminhtml_swatch_index'))
            ->renderLayout();
    }

    public function gridAction(){
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('optionswatch/adminhtml_swatch_index_grid', 'optionswatch_swatch_index_grid')
                ->toHtml()
        );
    }

    public function newAction(){
        $this->_forward('edit');
    }

    public function editAction(){
        $swatchId = $this->getRequest()->getParam('id');
        if(!!$swatchId){
            Mage::register('swatch_id', $swatchId);
        }
        $this->loadLayout()
            ->_setActiveMenu('astral')
            ->_addContent($this->getLayout()->createBlock('optionswatch/adminhtml_swatch_edit', 'optionswatch_swatch_edit'))
            ->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $swatchId = $this->getRequest()->getParam('id');
            if(isset($swatchId)&&!empty($swatchId)){
                $old_swatch = Mage::getModel('optionswatch/swatch')->load($swatchId);
                $old_data = $old_swatch->getData();
            }
            $new_swatch = Mage::getModel('optionswatch/swatch');
            $new_swatch -> setData($data);
            $new_data = $new_swatch->getData();
            $delete = 0;
            $swatch = $new_swatch;

            if(isset($data['image_file']) && !empty($data['image_file'])){
                $swatch->setData('image_file',$data['image_file']['value']);
            }

            if(isset($_FILES['image_file']['name'])&&!empty($_FILES['image_file']['name'])){
                $this->imageUploader($_FILES['image_file']['name'], $swatch, 'image_file', $delete);
            }

            if( isset($_FILES['filter_image_file']['name'])&&!empty($_FILES['filter_image_file']['name'])){
                $this->imageUploader($_FILES['filter_image_file']['name'], $swatch, 'filter_image_file', $delete);
            }

            if( isset($data['option_id']) ){
                $option = $swatch -> getOptionText($data['option_id'],$data['attribute_id']);
                $swatch->setData('option_value',$option);
            }

            if( isset($data['attribute_id']) ){
                $attributeCode = Mage::getModel('eav/entity_attribute')->load($data['attribute_id'])->getAttributeCode();;
                $swatch->setData('attribute_code',$attributeCode);
            }

            if(!!$swatchId && Mage::getModel('optionswatch/swatch')->load($swatchId)){
                $swatch -> setId ($this->getRequest ()->getParam ( 'id' ));
            }
            try{
                $swatch->save();
                Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'optionswatch' )->__ ( 'Saved successfully.' ) );
                Mage::getSingleton ( 'adminhtml/session' )->setFormData ( false );
                if ($this->getRequest ()->getParam ( 'back' )) {
                    $this->_redirect ( '*/*/', array ('id' => $swatch->getId () ) );
                    return;
                }
                $this->_redirect ( '*/*/' );
            }catch(Exception $e){
                Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
                Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
                $this->_redirect ( '*/*/', array ('id' => $this->getRequest ()->getParam ( 'id' ) ) );
                return;
            }

        }
    }

    public function deleteAction(){
        $swatchId = $this->getRequest()->getParam('id');
        if(!!$swatchId){
            $swatch = Mage::getModel('optionswatch/swatch')->load($swatchId);
            try{
                $swatch->delete();
            }catch (Exception $e) {
                Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }



    public function imageUploader($image, $model, $fieldName,  $deleteFlag=null){
        if(isset($image) && $image != '') {
            try {
                $uploader = new Varien_File_Uploader($fieldName);
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','svg'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                // Set media as the upload dir
                $file_path = Mage::getBaseDir('media') . DS . Astral_Optionswatch_Helper_Data::MEDIA_PATH . DS ;
                $media_path = Astral_Optionswatch_Helper_Data::MEDIA_PATH . '/'; // OS 
                // Upload the image
                $result = $uploader->save($file_path, $image);
                $data[$fieldName] = $media_path . $result['file'];
            }catch (Exception $e) {
                throw new Exception( "Unable to upload image file.", $e );
            }
        }else{
            if(isset($deleteFlag) && $deleteFlag == 1) {
                $data[$fieldName] = '';
            }
        }
        if(isset($data[$fieldName])) {
            $model->setData($fieldName, $data[$fieldName]);
        }else{
            $model->unsetData($fieldName);
        }
    }

}