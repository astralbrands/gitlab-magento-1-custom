<?php

class WeltPixel_Setup_Helper_Cms_Wysiwyg_Images
    extends Mage_Cms_Helper_Wysiwyg_Images
{

    public function isUsingStaticUrlsAllowed()
    {
        $result = parent::isUsingStaticUrlsAllowed();

        if (Mage::getSingleton('adminhtml/session')->getStaticUrlsAllowed()) {
            return true;
        }

        if (Mage::getSingleton('adminhtml/session')->getAllowStatic()) {
            return true;
        } else {
            return $result;
        }
    }

    /**
     *
     * From AM_Extension merged, and changes to work with Cleo
     *
     * Prepare Image insertion declaration for Wysiwyg or textarea(as_is mode)
     *
     * @param string $filename Filename transferred via Ajax
     * @param bool $renderAsTag Leave image HTML as is or transform it to controller directive
     * @return string
     */
    public function getImageHtmlDeclaration($filename, $renderAsTag = false)
    {
        $allowStatic = Mage::getSingleton('adminhtml/session')->getAllowStatic();
        $fileurl = $this->getCurrentUrl() . $filename;
        $mediaPath = str_replace(Mage::getBaseUrl('media'), '', $fileurl);
        $directive = sprintf('{{media url="%s"}}', $mediaPath);
        if ($renderAsTag) {
            $html = sprintf('<img src="%s" alt="" />',
                $this->isUsingStaticUrlsAllowed() ? $fileurl : $directive);
        } else {
            if ($allowStatic) {
                $html = $fileurl;
            } elseif ($this->isUsingStaticUrlsAllowed()) {
                $html = $mediaPath;
            } else {
                $directive = Mage::helper('core')->urlEncode($directive);
                $html = Mage::helper('adminhtml')->getUrl('*/cms_wysiwyg/directive',
                        array('___directive' => $directive));
            }
        }
        return $html;
    }

}

