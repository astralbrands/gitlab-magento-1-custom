<?php 

class Born_Package_Helper_Catalog_Product_List extends Mage_Core_Helper_Abstract
{
	public function isNewProduct($_product) {
		$currentTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		return $currentTime >= $_product->getNewsFromDate() && $currentTime <= $_product->getNewsToDate();
	}

    public function getSmallImageWidth()
    {
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_width = Mage::getStoreConfig('catalog/product_image/small_width', $_storeId);

        if (!$_width) {
            $_width = 210;
        }

        return $_width;
    }
	public function getProductImages($product, $imageSize=null, $imageCodes=null, $filterColor = null) {

        $mediaImages = array();

        if ($filterColor) {
            $mediaImages['small_image'] = $filterColor['product_image'];
            $mediaImages['alt_image'] = $filterColor['alt_image'];
            $mediaImages['label'] = $filterColor['label'];

            return $mediaImages;
        }

        if (!$imageSize) {
            $imageSize = $this->getSmallImageWidth();
        }

		if(!$imageCodes)
		{
			$imageCodes = array('small_image', 'alt_image');
		}elseif(!is_array($imageCodes)){
            $imageCodes = array($imageCodes);
        }

		$product = Mage::getModel('catalog/product')->load($product->getEntityId());

        $mediaImages['label'] = $this->getImageLabel($product, 'small_image');

		foreach($imageCodes as $code)
		{
			try{
				$mediaImages[$code] = (string)Mage::helper('catalog/image')->init($product, $code)->resize($imageSize);
			}catch(Exception $e){
				//Mage::log($e->getMessage());
			}
		}

		return $mediaImages;
	}

    public function getImageLabel($product = null, $mediaAttributeCode = 'image')
    {
        if (is_null($product)) {
            return;
        }

        $label = $product->getData($mediaAttributeCode . '_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
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

        $_allProductImages = $this->getAllProductImages($product);

        $result = array();
        foreach($options as $option) {
            if($option['label'] == 'Shade') {
                foreach($option['values'] as $color) {
                    $_optionSwatch = $optionSwatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($color['value_index'], null, $product->getSku());
                    $_imageUrl = $_optionSwatch->getData('image_file');
                    $_imageUrl = $_imageUrl ? $baseUrl . $_imageUrl : $this->getDefaultSwatchImage($product);
                    $_temp = array();
                    $_temp['image'] = $_imageUrl;
                        
                    if (array_key_exists('label',$color) && array_key_exists($color['label'],$_allProductImages) && array_key_exists('base', $_allProductImages[$color['label']]) && isset($_allProductImages[$color['label']]['base'])) {
                        $_temp['product_image'] = $_allProductImages[$color['label']]['base'];
                    }
                    elseif(isset($_allProductImages[$color['label']][0])){
                        $_temp['product_image'] = $_allProductImages[$color['label']][0];
                    }
                    if(isset($color) && array_key_exists('label',$color)){
			if(array_key_exists('swatch',$color)){
			    if(isset($_allProductImages)){
			    $_temp['alt_image'] = $_allProductImages[$color['label']]['swatch'];
			    }
			}
		    }
                    $result[] = array_merge($color, $_temp);
                    // $result[]['product_images'] = $this->getProductImagesByLabel($mediaImages, $color['label']);
                }
            }
        } 
        return $result;
    }

    protected function getAllProductImages($_product){

        if($_product->getId()){
            $_product = Mage::getModel('catalog/product')->load($_product->getId());
        }
        if($_product){
            $_size = $this->getSmallImageWidth();
            $_mediaImages = Mage::helper('optionswatch/data')->getConfigMediaImages($_product, $_size);
            return $_mediaImages;
        }
    }

    protected function getDefaultSwatchImage($_product){
        $_storeId = Mage::app()->getStore()->getStoreId();
        $enable = Mage::getStoreConfig('configswatches/swatch_placeholder/enable', $_storeId);

        if($enable){
            return $_product->getSmallImageUrl();
        }

        return null;
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

    public function getSubText($_product)
    {
        $entityId = $_product->getEntityId();

        if($entityId)
        {
            $subText = $this->getSubTextByEntityId($entityId);

            return $subText;
        }
        return;
    }

    public function getSubTextByEntityId($entityId)
    {
        if ($this->getShowSubtext() && $entityId) {

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
}


?>