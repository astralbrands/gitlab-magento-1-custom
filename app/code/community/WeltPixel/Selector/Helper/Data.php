<?php

class WeltPixel_Selector_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    protected $_googlefontUrl = 'https://fonts.googleapis.com/css?family=';

    public function getGeneralColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/general', $store);
    }

    public function getButtonsColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/buttons', $store);
    }

    public function getNewAndSaleLabelsColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/new_and_sale_labels',
            $store);
    }

    public function getMiscElementsColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/misc_elements',
            $store);
    }

    public function getHeaderVersion1ColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/header_version_1',
            $store);
    }

    public function getHeaderVersion2ColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/header_version_2',
            $store);
    }

    public function getHeaderVersion3ColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/header_version_3',
            $store);
    }

    public function getHeaderMenuColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/header_menu',
            $store);
    }
    
    public function getHeaderGlobalPromoColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/header_global_promo',
            $store);
    }

    public function getBreadcrumbColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/breadcrumb', $store);
    }

    public function getSearchBoxColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/search_box', $store);
    }

    public function getDropdownBoxColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/dropdown_boxes_header',
            $store);
    }

    public function getFooterColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/footer', $store);
    }

    public function getPreFooterColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/pre_footer', $store);
    }

    public function getSubFooterColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/sub_footer', $store);
    }

    public function getSocialMediaColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/social_media_icons',
            $store);
    }

    public function getLayeredNavigationColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/layered_navigation',
            $store);
    }

    public function getAjaxsearchColorOptions($store)
    {
        return Mage::getStoreConfig('weltpixel_colorsettings/ajaxsearch', $store);
    }

    public function getFontSettings($storeId)
    {
        return Mage::getStoreConfig('weltpixel_fontsettings/font_family',
            $storeId);
    }

    public function getFontSizeSettings($storeId)
    {
        return Mage::getStoreConfig('weltpixel_fontsettings/font_size', $storeId);
    }

    public function getDynamicCssContent($storeId)
    {
        $cssBlock = Mage::app()->getLayout()
            ->createBlock('core/template')
            ->setTemplate('weltpixel/page/html/css.phtml')
            ->setStoreId($storeId);

        return $cssBlock->toHtml();
    }

    public function getBasicGoogleFonts($storeId)
    {
        $basicFontSettings = $this->_googlefontUrl;
        $fontSettings = Mage::getStoreConfig('weltpixel_fontsettings/font_family',
                $storeId);

        if ($fontSettings['basic_font']):
            $basicFontSettings .= str_replace(' ', '+',
                $fontSettings['basic_font']);

            if ($fontSettings['basic_weight']):
                $basicFontSettings .= ':' . $fontSettings['basic_weight'];
            endif;
            if ($fontSettings['basic_characterset']):
                $basicFontSettings .= '&subset=' . $fontSettings['basic_characterset'];
            endif;

            return $basicFontSettings;
        endif;

        return false;
    }

    public function getHeadingGoogleFonts($storeId)
    {
        $headingFontSettings = $this->_googlefontUrl;
        $fontSettings = Mage::getStoreConfig('weltpixel_fontsettings/font_family',
                $storeId);

        if ($fontSettings['headings_font']):
            $headingFontSettings .= str_replace(' ', '+',
                $fontSettings['headings_font']);

            if ($fontSettings['headings_weight']):
                $headingFontSettings .= ':' . $fontSettings['headings_weight'];
            endif;
            if ($fontSettings['headings_characterset']):
                $headingFontSettings .= '&subset=' . $fontSettings['headings_characterset'];
            endif;

            return $headingFontSettings;
        endif;

        return false;
    }

    public function getAjaxAddtoCart()
    {
        $requestUri = Mage::app()->getRequest()->getModuleName() . '-'
            . Mage::app()->getRequest()->getControllerName() .'-'
            . Mage::app()->getRequest()->getActionName();

        if (in_array($requestUri, array('wishlist-index-configure'))) {
            return false;
        }
        return Mage::getStoreConfig('weltpixel_selector/productpageoptions/ajax_add_to_cart');
    }

    public function getAjaxAddtoCartConfirmationHide()
    {
        return Mage::getStoreConfig('weltpixel_selector/productpageoptions/ajax_add_to_cart_confirmation_hide');
    }

    public function getAjaxAddtoCompare()
    {
        return Mage::getStoreConfig('weltpixel_selector/productpageoptions/ajax_add_to_compare');
    }

    public function applyStickyHeader()
    {
        $stickyHeader = Mage::getStoreConfig('weltpixel_selector/headeroptions/sticky_header');
        $stickyHeaderOnMobile = Mage::getStoreConfig('weltpixel_selector/headeroptions/sticky_header_mobile');
        $_helper = Mage::helper('weltpixel_mobiledetect');
        $mobileDeviceWasDetected = $_helper->isMobile() || $_helper->isTablet();
        if ($stickyHeader && !$stickyHeaderOnMobile && $mobileDeviceWasDetected) :
            return false;
        endif;

        return $stickyHeader;
    }

    public function getLoginBgImage()
    {
        $uploadedFile = Mage::getStoreConfig('weltpixel_selector/customization_settings/login_backgroundimage');
        if (strlen(trim($uploadedFile))) :
            return Mage::getBaseUrl('media') . WeltPixel_Selector_Model_Adminhtml_System_Config_Loginbgimage::UPLOAD_DIR . '/' . $uploadedFile;
        else:
            return false;
        endif;
    }

    public function getGoogleApiKey()
    {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/google_api_key');
    }

    public function getContactLongitude()
    {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/contact_form_longitude');
    }

    public function getInstagramClientId()
    {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/instagram_client_id');
    }

    public function getInstagramAccessToken()
    {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/instagram_access_token');
    }

    public function getContactLatitude()
    {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/contact_form_latitude');
    }

    public function equalGridImageSize()
    {
        return Mage::getStoreConfig('weltpixel_selector/categoryoptions/equal_grid_images');
    }

    public function getGridmageSize()
    {
        return Mage::getStoreConfig('weltpixel_selector/categoryoptions/grid_image_size');
    }

    public function getProductTemplate()
    {
        $template = 'page/1column.phtml';
        $xmlPath = 'global/page/layouts';

        if (Mage::getConfig()->getNode($xmlPath)) {
            foreach (Mage::getConfig()->getNode($xmlPath)->children() as $layoutCode => $layoutConfig) {
                if ($layoutCode == Mage::getStoreConfig('weltpixel_selector/productpageoptions/default_page_layout')) {
                    $template = (string) $layoutConfig->template;
                }
            }
        }

        return $template;
    }

     public function getCategoryTemplate()
    {
        $template = 'page/3columns.phtml';
        $xmlPath = 'global/page/layouts';

        if (Mage::getConfig()->getNode($xmlPath)) {
            foreach (Mage::getConfig()->getNode($xmlPath)->children() as $layoutCode => $layoutConfig) {
                if ($layoutCode == Mage::getStoreConfig('weltpixel_selector/categoryoptions/default_page_layout')) {
                    $template = (string) $layoutConfig->template;
                }
            }
        }

        return $template;
    }

    public function getLoginPageTemplate()
    {
        $template = 'page/1column.phtml';
        $xmlPath = 'global/page/layouts';

        if (Mage::getConfig()->getNode($xmlPath)) {
            foreach (Mage::getConfig()->getNode($xmlPath)->children() as $layoutCode => $layoutConfig) {
                if ($layoutCode == Mage::getStoreConfig('weltpixel_selector/customization_settings/login_page_layout')) {
                    $template = (string) $layoutConfig->template;
                }
            }
        }

        return $template;
    }

    public function getContactTemplate() {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/contact_form_version');
    }

    public function hideCategoryTitle()
    {
        return Mage::getStoreConfig('weltpixel_selector/categoryoptions/hide_title');
    }
    
    public function getLoginClass() {
       if (Mage::getSingleton('customer/session')->isLoggedIn()) {
           return 'logged-in';
       }
       
       return 'not-logged-in';
    }

}
