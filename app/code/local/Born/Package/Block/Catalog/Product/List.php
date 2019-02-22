<?php 

class Born_Package_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{

    private $appliedShadeFilterValues;

    /**
     * @deprecated
     */
	public function isNewProduct($_product) {
        $currentTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        return $currentTime >= $_product->getNewsFromDate() && $currentTime <= $_product->getNewsToDate();
    }

    /**
     * @deprecated
     */
    public function getProductImages($product, $imageSize=240, $imageCodes=null) {

        if(!$imageCodes)
        {
            $imageCodes = array('small_image', 'alt_image');
        }

        $product = Mage::getModel('catalog/product')->load($product->getEntityId());

        $mediaImages = array();

        foreach($imageCodes as $code)
        {
            try{
                $mediaImages[$code] = (string)$this->helper('catalog/image')->init($product, $code)->resize($imageSize);
            }catch(Exception $e){
                //Mage::log($e->getMessage());
            }
        }
        return $mediaImages;
    }

    protected function getMediaImages($product)
    {
        return Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages();
    }

    protected function getProductImagesByLabel($imageCollection, $labelVal)
    {
        $result = array();
        $images = $imageCollection->getItemsByColumnValue('label', $labelVal);

        foreach ($images as $image) {
            $result[$image->getId()] = $image->getUrl();
        }

        return $result;
    }

    public function hasColors($product) {
        if($product->getTypeId() != "configurable") {
            return false;
        }
        $hasColors = false;
        
        $options = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        foreach($options as $option) {
            if($option['label'] == 'Shade') {
                $hasColors = true;
            }
        }
        return $hasColors;
    }

    public function getColors($product) {
        $options = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $mediaImages = $this->getMediaImages($product);

        $result = array();
        foreach($options as $option) {
            if($option['label'] == 'Shade') {
                foreach($option['values'] as $color) {
                    $result[] = array_merge($color, array('image' => $baseUrl.Mage::getModel('optionswatch/swatch')->loadByOptionId($color['value_index'])->getData('image_file')));
                    $result['product_images'] = $this->getProductImagesByLabel($mediaImages, $color['label']);
                }
            }
        } 
        return $result;
    }

    public function inWishlist($product) {
        #TODO: may need to hole punch
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if($customerId) {
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
            foreach ($wishlist->getItemCollection() as $item) {
                if($item->getProductId() === $product->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasChildCategories($_category)
    {
        $_childrenCat = $_category->getChildren();

        if($_childrenCat){
            return true;
        }
    }

    public function isLevel3Category($_category) {
        $_categoryLevel = $_category->getLevel();

        if($_categoryLevel == 3) {
            return true;
        }

        return false;
    }

    protected function isFilterApplied()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();

        $_ignoreKeys = $arrayName = array('m-ajax','no_cache');

        parse_str(parse_url($currentUrl, PHP_URL_QUERY), $urlQuery);

        foreach ($urlQuery as $key => $value) {
            if (!in_array($key, $_ignoreKeys)) {
                return true;
            }
        }

        return false; 
    }

    protected function getAppliedShadeFilterValues()
    {
        if (!$this->appliedShadeFilterValues) {
            $this->appliedShadeFilterValues = $this->getAppliedFilterValues($_attributeCode = 'shade');
        }

        return $this->appliedShadeFilterValues;
    }

    /**
     * @return boolean
     */
    public function isSwatchActive($optionId)
    {
        $appliedShadeFilterValues = $this->getAppliedShadeFilterValues();

        if (!$appliedShadeFilterValues || !$optionId) {
            return false;
        }

        foreach ($appliedShadeFilterValues as $_id) {
            if ($_id == $optionId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array of configurable option values applied
     */
    protected function getAppliedFilterValues($_attributeCode = null)
    {
        if (!$this->isFilterApplied()) {
            return;
        }

        if ($currentUrl = Mage::helper('core/url')->getCurrentUrl()) {
            parse_str(parse_url($currentUrl, PHP_URL_QUERY), $urlQuery);

            if (!$_attributeCode && $urlQuery) {
                return $urlQuery;
            }

            $_appliedShadeFilterValues = array();

            foreach ($urlQuery as $key => $optionId) {

                if ($key == $_attributeCode) {
                    if (!strpos($optionId, '_') === false) {
                        $optionId = explode('_', $optionId);
                    }
                    if (is_array($optionId)) {
                        foreach ($optionId as $idValue) {
                            $_appliedShadeFilterValues[] = $idValue;
                        }
                    }
                    else{
                        $_appliedShadeFilterValues[] = $optionId;
                    }
                }
            }
            return $_appliedShadeFilterValues;
        }
    }

    public function displayCustomList()
    {      
        $_currentCategory = $this->getLayer()->getCurrentCategory();

        if($this->isLevel3Category($_currentCategory) && $this->hasChildCategories($_currentCategory) && !$this->isFilterApplied()){
            return true;
        }

        return false;

    }

    public function getSubText($_product)
    {
        if ($this->getShowSubtext()) {
            $entityId = $_product->getEntityId();

            $_model = Mage::getModel('born_package/catalog_attribute_data');
            $subText = $_model->getSubtitleByEntityId($entityId);

            return $subText;
        }
        return;

    }

    public function getShowSubtext()
    {
        $path = 'catalog/category_product_subtext/enable';

        $enable = $this->getConfig($path);

        if($enable){
            return true;
        }
        return false;
    }

    public function getConfig($path)
    {
        if (!$path) {
            return;
        }

        $_storeId = Mage::app()->getStore()->getStoreId();
        $config = Mage::getStoreConfig($path, $_storeId);

        return $config;
    }

    public function getProductBadge($product)
    {
        if (!$product) {
            return;
        }

        $productId = $product->getId();

        $attributeId = $this->getProductBadgeAttributeId();

        if (!$attributeId) {
            return;
        }

        $optionId = Mage::getModel('born_package/catalog_attribute_data')->getProductAttributeInt($productId, $attributeId);

        if (!$optionId) {
            return;
        }

        $optionText = Mage::getModel('optionswatch/swatch')->getOptionText($optionId, $attributeId);

        return $optionText;
    }
}
?>
