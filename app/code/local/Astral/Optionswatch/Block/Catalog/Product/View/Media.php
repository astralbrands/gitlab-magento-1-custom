<?php
class Astral_Optionswatch_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function getAllSimpleGalleryImages($product)
    {
        $images = array();
        $mediaApi = Mage::getModel('catalog/product_attribute_media_api');
        $simpleProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product); 
            foreach($simpleProducts as $_product) {
            $images[$_product->getSku()] = $mediaApi->items($_product->getId()); 
            }        
        return $images;    
    }
    public function getAllSimpleIds($product)
    {
        $ids = array();
        $simpleProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
        foreach($simpleProducts as $_product) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            $si = $stockItem->getData();
            if($si['qty'] > 5){
                $ids[] = $_product->getId();
            }
        }
        return $ids;
    }
    public function getBaseImageLabel()
    {
        $_product = $this->getProduct();
        if ($_product->isConfigurable()) {
            $_label = $_product->getData('image_label');
            return $_label;
        }
        return;
    }

    public function isMediaClass($image, $optionLabels)
    {
        $product = $this->getProduct();
        $_defaultImageLabel = $this->getBaseImageLabel();

        if ($product->isConfigurable()) {
            if (is_array($optionLabels) && count($optionLabels) && $image->getLabel()) {
                if (in_array($image->getLabel(), $optionLabels) && $image->getLabel() != $_defaultImageLabel) {
                    return false;
                }
            }
        }
        return $this->isGalleryImageVisible($image);
    }

    public function getSimpleSku($optionId,$ids)
    {
        $sku = false;
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $table = $resource->getTableName('catalog_product_entity_int');
        $query = "SELECT entity_id FROM ".$table." WHERE value='".$optionId."'";
        $results = $read->fetchAll($query);
        foreach($results AS $result) {
            foreach ($ids As $id) {
                if ($id == $result['entity_id']) {
                    $tableName = $resource->getTableName('catalog_product_entity');
                    $querySku = "SELECT sku FROM ".$tableName." WHERE entity_id='".$id."'";
                    $resultsSku = $read->fetchAll($querySku);
                    $sku = $resultsSku[0]['sku'];
                }
            }
        }
        return $sku;
    }

    public function getSwatches($_product){
        $ids = $this->getAllSimpleIds($_product);
		$sku = $_product->getSku();	
        $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $data = array();
        $swatches = Mage::getModel('optionswatch/swatch')->getCollection()
            ->addFieldToFilter('product_sku', array('in'=> $sku))
            ->setOrder('sort_order','ASC');

        foreach($swatches AS $swatch){
            $item = array();
            $swatch_array = $swatch->getData();
			$stop = false;
            foreach($swatch_array AS $key => $val) {
                if ($key == 'option_id') {
                    $id = $val;
                    $item[$key] = $val;
                    $sku = $this->getSimpleSku($val,$ids);
					if(!$sku){
						$stop = true;
					}
                    $item['sku'] = $sku;
                }
                if ($key == 'option_value') {
                    $item[$key] = $val;
                }
                if ($key == 'option_label') {
                    $item[$key] = $val;
                }
                if ($key == 'product_sku') {
                    $item[$key] = $val;
                }
                if ($key == 'image_file') {
                    $item[$key] = $mediaUrl.$val;
                }
                if ($key == 'option_value') {
                    $item[$key] = $val;
                }
                if ($key == 'sort_order') {
                    $item[$key] = $val;
                }
                if ($key == 'default_option') {
                    $item[$key] = $val;
                }
            }
			if(!$stop){
            $data[$id] = $item;
			}
        }
        return $data;
    }

}