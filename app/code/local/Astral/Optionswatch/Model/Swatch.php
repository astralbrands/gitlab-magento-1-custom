<?php
class Astral_Optionswatch_Model_Swatch extends Mage_Core_Model_Abstract
{
	
    protected $_attributeCodeArray = array("color", "benefits", "shade", "form", "application_icon1", "application_icon2", "filter_form");
	
    protected function _construct()
    {
        $this->_init('optionswatch/swatch');
    }
    
    public function loadByOptionId($optionId, $storeId = null, $productSku=null)
    {

		if($productSku && is_array($productSku)){
			$productSku = array_pop($productSku);
		}
    	$data = $this->getResource()->loadByOptionId($optionId,$storeId, $productSku);
    	$this->setData($data);
    	return $this;
    }
        
    public function getOptionText($value, $attributeId)
    {
    	$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeId);
    	$label = $attribute->getSource()->getOptionText($value);
    	return $label;  	
    }    

    public function getOptionValue()
    {
    	$optionValue = '';
    	$optionId = $this->getOptionId();
    	$attributeId = $this->getAttributeId();
    	if($optionId && $attributeId){
    		$optionValue = $this->getOptionText($optionId, $attributeId);
    	}
    	return $optionValue;
    }
    
    
    public function getSwatchImageUrl()
    {
    	$imageUrl = '';
    	if(!!$this->getData('image_file')){
			$imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getData('image_file');
    	}
    	return $imageUrl;
    }       
    
    public function getFilterImageUrl()
    {
     	$imageUrl = '';
    	if(!!$this->getData('filter_image_file')){
			$imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getData('filter_image_file');
        }else{
            $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'filter/placeholder/'.Mage::getStoreConfig('configswatches/swatch_placeholder/filter_image_placeholder');
        }
    	return $imageUrl;   	
    }

	//This is for updating 'created_at', 'updated_at' and 'store_id'
    protected function _beforeSave()
    {
    	//Timezone manipulation ignored. Use Magento default timezone (UTC)
		$timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		$datetime = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $datetime);
    	}
    	$this->setData('updated_at', $datetime);
    	if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();
    }
    
    /*
     * Get available attributes 
     */
    public function toAttributeArray()
    {
    	$attributeArray = array("" => "");
       	foreach ($this->_attributeCodeArray as $attributeCode){
	    	$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
	    	$attributeArray[ $attribute->getId() ] = $attributeCode;
    	}
    	return $attributeArray;		
    }
    
    /*
     * Get All available attributes and their options
     */
    public function getAttributeOptionArray()
    {
    	foreach ($this->_attributeCodeArray as $attributeCode){
	    	$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
	    	$attributeOptionArray[ $attribute->getId() ] = $this->toOptionArray($attributeCode);
    	}
    	return $attributeOptionArray;		
    }        
    
	/*
	 * Option array for certain attribute
	 */
    public function toOptionArray($attributeCode)
    {
    	$optionArray = array("" => "");
    	if(!!$options = $this->getAttributeOptions($attributeCode)){
    		foreach ($options as $option){
    			$optionArray[ $option['value'] ] = $option['label'];
    		}
    	}
    	return $optionArray;
    }
    
    /*
     * original option collection for certain attribute
     */
    public function getAttributeOptions($attributeCode)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
		if ($attribute->usesSource()) {
		    $options = $attribute->getSource()->getAllOptions(false);
		}else{
            $options = array();
        }
		return $options;
    } 

	/*
	 * Get All Available Options
	 */    
    public function toAllOptionsArray()
    {
        $optionArray = array("" => "");
    	foreach ($this->_attributeCodeArray as $attributeCode){
	    	if(!!$options = $this->getAttributeOptions($attributeCode)){
	    		foreach ($options as $option){
	    			$optionArray[ $option['value'] ] = $option['label'];
	    		}
	    	}
    	}
    	return $optionArray;
    }    
    
    public function getColorOptionText($value)
    {
    	$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'color');
    	$label = $attribute->getSource()->getOptionText($value);
    	return $label;  	
    }
}