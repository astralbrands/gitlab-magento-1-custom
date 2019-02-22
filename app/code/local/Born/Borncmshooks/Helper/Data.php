<?php

class Born_Borncmshooks_Helper_Data extends Mage_Core_Helper_Abstract
{

    const IMAGE_BASE_PATH = 'borncmshooks';
    const IMAGE_MOBILE_MAX_WIDTH = 600;
    const IMAGE_MOBILE_MAX_HEIGHT = 500;
	
  public function array_msort(&$arr, $cols)
    {
        $sort_by = null;
        $sort_dir = null;
        
        foreach($cols as $col => $dir){
            $sort_by = $col;
            $sort_dir = $dir;
        }
        
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$sort_by];
        }

        array_multisort($sort_col, $sort_dir, $arr);
        return $arr;
    }
    
    public function arrayToVarienCollection($array){
        $collection = new Varien_Data_Collection();
        if(count($array)){
            foreach ($array as $item) {
            $varienObject = new Varien_Object();
            $varienObject->setData($item);
            $collection->addItem($varienObject);
        }
        return $collection;
        }
    }
    
    public function getImageUrl($rawImage, $mode = 'full') {
        if(!is_null($rawImage)) {
            if ($mode == 'full') {
                if(Mage::app()->getStore()->isCurrentlySecure()){
                    $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true) .  self::IMAGE_BASE_PATH   . $rawImage;
                } else
                    $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .  self::IMAGE_BASE_PATH  . $rawImage;

                return $imageUrl;
            } else
                return $this->resizeImg($rawImage);
        }
        return '';
    }


    public function resizeImg($fileName, $width = self::IMAGE_MOBILE_MAX_WIDTH, $height = self::IMAGE_MOBILE_MAX_HEIGHT)
    {
        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $imageURL = $folderURL . $fileName;

        $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS .self::IMAGE_BASE_PATH . $fileName;
        $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS .self::IMAGE_BASE_PATH  . DS . "mobile" . $fileName;


        //if width empty then return original size image's URL
        if ($width != '') {
            //if image has already resized then just return URL
            if (file_exists($basePath) && is_file($basePath) ) {

                $imageObj = new Varien_Image($basePath);

                if ($imageObj->getOriginalWidth() > $width || $imageObj->getOriginalHeight() > $height) {
                    $maxRatio = $width / $height;
                    $imageRatio = $imageObj->getOriginalWidth() / $imageObj->getOriginalHeight();

                    if (!file_exists($newPath))

                        $imageObj->constrainOnly(TRUE);
                    $imageObj->keepAspectRatio(TRUE);
                    $imageObj->keepFrame(FALSE);

                    if ($imageRatio >= $maxRatio) //resize based on given width
                        $imageObj->resize($width, null);
                    else
                        $imageObj->resize(null, $height);

                    $imageObj->save($newPath);
                }


            }
            if(Mage::app()->getStore()->isCurrentlySecure()){
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true) .  self::IMAGE_BASE_PATH  . DS ."mobile"  . $fileName;
            } else
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .  self::IMAGE_BASE_PATH  . DS ."mobile"  . $fileName;
        } else {
            $resizedURL = $imageURL;
        }
        return $resizedURL;
    }

    /*
     * get cms hooks max count for slots/rows
     * @return int
     */
    public function getHighlightsMaxCount() {
        return (int) Mage::getStoreConfig('borncmshooks_config/borncmshooks_slots_for_highlights/max_count');
    }

    /*
     * get cms hooks max count for slots/rows
     * @return int
     */
    public function getTilesMaxCount() {
        return (int) Mage::getStoreConfig('borncmshooks_config/borncmshooks_slots_for_tiles/max_count');
    }

    /*
     * $field = object or array
     */
    public function getCategoryBlockCms($fields, Mage_Catalog_Model_Category $category, $blockName) {

        if (is_array($fields)) {
            $fields = reset($fields);
            $fields = $this->arrayToVarienCollection($fields);
        }



        foreach ($fields as $object) {

            if (
                $object->getData('categories')
                && $object->getData('placeholder') == $blockName
                && in_array($category->getId(), explode(",",$object->getData('categories')))
            ) {

               return $object;

            }
        }

        return;
    }

    public function getProductBySku($productSku)
    {
        $productid= Mage::getModel('catalog/product')->getIdBySku(trim($productSku));

        if($productid) //if SKU is found
        {
            $_products = Mage::getModel('catalog/product');
            $_products->load($productid);
            return $_products;
        }
        else
        {
            //Mage::log("Homepage: SKU: ". $productSku . " not found.");
        }
    }

    public function getSectionTitle($objectCollection)
    {
        foreach($objectCollection as $key => $object)
        {
            if($sectionTitle = $object->getSectionTitle())
            {
                $objectCollection->removeItemByKey($key);
                return $sectionTitle;
            }
        }
    }
    public function wysiwygProcessor($value)
    {   
        $_cmsHelper = Mage::helper('cms');
        $_processor = $_cmsHelper->getPageTemplateProcessor();
        $_html = $_processor->filter($value);

        return $_html;
    }
}