<?php

class WeltPixel_LayeredNavigation_Helper_Image extends Mage_Core_Helper_Data {

    protected $optionObj = null;
    protected $originalImageUrl = null;
    protected $width = null;
    protected $height = null;
    protected $originalFilePath = null;
    protected $newFilePath = null;
    protected $newFileUrl = null;
    protected $cssFilePath = null;
    protected $originalDestination = 'wysiwyg';

    public function __toString() {
        try {
            if (!$this->originalImageUrl) :
                return '';
            endif;
            if (!Mage::helper('weltpixel_layerednavigation')->getSwatchResize()) :
                return $this->originalImageUrl;
            endif;
            $this->setBaseFile();

            if ($this->isCached()) {
                return $this->newFileUrl;
            } else {
                $this->saveImage();
                return $this->newFileUrl;
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            return '';
        }
    }


    public function getCssBackground($storeId = 0) {
        try {
            if (!$this->originalImageUrl) :
                return '';
            endif;
            if (!Mage::helper('weltpixel_layerednavigation')->getSwatchResize()) :
                return str_replace(Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL), '..'. DS .'..'. DS .'..'. DS .'..'. DS .'..'. DS .'..'. DS , $this->originalImageUrl);
            endif;
            $this->setBaseFile();

            if ($this->isCached()) {
                return $this->cssFilePath;
            } else {
                $this->saveImage();
                return $this->cssFilePath;
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            return '';
        }
    }

    protected function _reset() {
        $this->optionObj = null;
        $this->originalImageUrl = null;
        $this->width = null;
        $this->height = null;
        $this->originalFilePath = null;
        $this->newFilePath = null;
        $this->newFileUrl = '';
        $this->cssFilePath = null;
    }

    public function init($optionId) {

        $this->_reset();
        $optionObj = Mage::getModel('eav/entity_attribute_option')->load($optionId);
        $this->optionObj = $optionObj;
        $this->originalImageUrl = $optionObj->getData('navigation_image');

        return $this;
    }

    public function resize($width, $height) {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    protected function isCached() {
        return file_exists($this->newFilePath);
    }

    protected function setBaseFile() {
        $imagePath = parse_url($this->originalImageUrl, PHP_URL_PATH);

        $pos = strpos($imagePath, $this->originalDestination);
        if ($pos === false) {
            return;
        }

        $this->originalFilePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . substr($imagePath, $pos);
        $newFilePath = DS . 'layerednavigation' . DS . 'layered_swatches' . DS .
                $this->optionObj->getData('attribute_id') . DS . $this->optionObj->getData('option_id')
                . DS . $this->width . 'x' . $this->height . DS . substr($imagePath, $pos + strlen($this->originalDestination) + 1);
        $this->newFilePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $newFilePath;
        $this->newFileUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $newFilePath;
        $this->cssFilePath =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true) . $newFilePath;// for relative path // '..'. DS .'..'. DS .'..'. DS .'..'. DS .'..'. DS .'..'. DS . 'media' . $newFilePath;
    }

    protected function saveImage() {
        $imageObj = new Varien_Image($this->originalFilePath);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(FALSE);
        $imageObj->keepFrame(FALSE);
        $imageObj->resize($this->width, $this->height);
        $imageObj->save($this->newFilePath);
    }

    public function clearCache($attrId, $optionId) {
        if (Mage::helper('weltpixel_layerednavigation')->getSwatchResize()) :
            $newFilePath = DS . 'layerednavigation' . DS . 'layered_swatches' . DS;
            $directory = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $newFilePath . DS . $attrId . DS . $optionId;
            $io = new Varien_Io_File();
            $io->rmdir($directory, true);
        endif;
    }

}
