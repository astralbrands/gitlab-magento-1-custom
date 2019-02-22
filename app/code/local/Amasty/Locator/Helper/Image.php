<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Helper_Image extends Mage_Core_Helper_Abstract
{
    private $ext = array('jpg', 'jpeg', 'gif', 'png');

    private $imgDir;

    public function __construct()
    {
        $this->imgDir =  "amasty".DS."locator".DS;
    }


    public function saveImage($fileName, $id)
    {
        $uploader = new Varien_File_Uploader($fileName);
        $uploader->setAllowedExtensions($this->ext);//Allowed extension for file
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $uploader->save(
            $this->getImagePath(),
            $id . "." . pathinfo($_FILES[$fileName]['name'], PATHINFO_EXTENSION)
        );
        // Upload the image
        return $id . "." . pathinfo(
            $_FILES[$fileName]['name'], PATHINFO_EXTENSION
        );
    }

    private function getImageByLink($link)
    {
        $end = strrchr($link, "/");
        $end = substr($end, 1, strlen($end));
        return $end;
    }

    public function getImagePath($storeName = "")
    {
        if ($storeName) {
            return Mage::getBaseDir('media') . DS . $this->imgDir . DS
            . $storeName;
        } else {
            return Mage::getBaseDir('media') . DS . $this->imgDir;
        }
    }

    public function getStoreUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
        . $this->imgDir;
    }

    public function getImageUrl($image)
    {
        if ($image && !is_array($image)) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->imgDir .  $image;
        } elseif (is_array($image)) {
            return $image['value'];
        }
        return false;
    }

    public function saveAction($fileName, $id, $data)
    {
        if (isset($_FILES[$fileName]['name'])
            && $_FILES[$fileName]['name'] != ''
        ) {
            return $this->saveImage($fileName, $id);
        } else {
            if (isset($data['delete']) && $data['delete'] == 1) {
                //just unlink file
                @unlink(
                    $this->getImagePath($this->getImageByLink($data['value']))
                );
                return "";
            }
        }

        return $data;//$this->getImageByLink($data['value']);
    }

    public function deleteImage($image)
    {
        @unlink($this->getImagePath().$image);
    }
}
