<?php
class  Born_BornIntegration_Model_Catalog_Product_Api_V2 extends Mage_Catalog_Model_Product_Api_V2
{
    public function sageCreate($type, $set, $sku, $productData, $store = null){
            if (!$type || !$set || !$sku || !is_object($productData)) {
            $this->_fault('data_invalid');
            }
        $this->_checkProductTypeExists($type);
        $this->_checkProductAttributeSet($set);
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($this->_getStoreId($store))
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);
            if (!property_exists($productData, 'stock_data')) {
            $_stockData = array('use_config_manage_stock' => 0);
            $product->setStockData($_stockData);
            }
            foreach ($product->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $product->setData($mediaAttrCode, 'no_selection');
            }
        $this->_prepareDataForSave($product, $productData);
            try {
            /**
             * @todo implement full validation process with errors returning which are ignoring now
             * @todo see Mage_Catalog_Model_Product::validate()
             */
                if (is_array($errors = $product->validate())) {
                $strErrors = array();
                    foreach($errors as $code => $error) {
                        if ($error === true) {
                        $error = Mage::helper('catalog')->__('Attribute "%s" is invalid.', $code);
                        }
                    $strErrors[] = $error;
                    }
                $this->_fault('data_invalid', implode("\n", $strErrors));
                }
            $product->save();
            } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            }
        return $product->getId();
    }
    
    public function sageUpdate($productId, $productData, $store = null, $identifierType = null){
        $product = $this->_getProduct($productId, $store, $identifierType);
        $this->_prepareDataForSave($product, $productData);
            try {
            /**
             * @todo implement full validation process with errors returning which are ignoring now
             * @todo see Mage_Catalog_Model_Product::validate()
             */
                if (is_array($errors = $product->validate())) {
                $strErrors = array();
                    foreach($errors as $code => $error) {
                        if ($error === true) {
                        $error = Mage::helper('catalog')->__('Value for "%s" is invalid.', $code);
                        } else {
                        $error = Mage::helper('catalog')->__('Value for "%s" is invalid: %s', $code, $error);
                        }
                    $strErrors[] = $error;
                    }
                $this->_fault('data_invalid', implode("\n", $strErrors));
                }
            $product->save();
            } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            }
        return true;
    }
    
    protected function _prepareDataForSave ($product, $productData){
            if (!is_object($productData)) {
            $this->_fault('data_invalid');
            }
            if (property_exists($productData, 'website_ids') && is_array($productData->website_ids)) {
            $product->setWebsiteIds($productData->website_ids);
            }
            if (property_exists($productData, 'additional_attributes')) {
                if (property_exists($productData->additional_attributes, 'single_data')) {
                    foreach ($productData->additional_attributes->single_data as $_attribute) {
                    $_attrCode = $_attribute->key;
                    $productData->$_attrCode = $_attribute->value;
                    }
                }
                if (property_exists($productData->additional_attributes, 'multi_data')) {
                    foreach ($productData->additional_attributes->multi_data as $_attribute) {
                    $_attrCode = $_attribute->key;
                    $productData->$_attrCode = $_attribute->value;
                    }
                }
                if (gettype($productData->additional_attributes) == 'array') {
                    foreach ($productData->additional_attributes as $k => $v) {
                    $_attrCode = $k;
                    $productData->$_attrCode = $this->getAttributeIdString($_attrCode, $v);
                    }
                }
            unset($productData->additional_attributes);
            }
            if (property_exists($productData, 'size')) {
            $productData->size = $this->getAttributeIdString('size', $productData->size);
            }
            if (property_exists($productData, 'color')) {
            $productData->color = $this->getAttributeIdString('color', $productData->color);
            }
            if (property_exists($productData, 'coverage')) {
            $productData->coverage = $this->getAttributeIdString('coverage', $productData->coverage);
            }
            if (property_exists($productData, 'benefits')) {
            $productData->benefits = $this->getAttributeIdString('benefits', $productData->benefits);
            }
            if (property_exists($productData, 'purpose')) {
            $productData->purpose = $this->getAttributeIdString('purpose', $productData->purpose);
            }
            if (property_exists($productData, 'finish')) {
            $productData->finish = $this->getAttributeIdString('finish', $productData->finish);
            }
            if (property_exists($productData, 'color_family')) {
            $productData->color_family = $this->getAttributeIdString('color_family', $productData->color_family);
            }
            if (property_exists($productData, 'form')) {
            $productData->form = $this->getAttributeIdString('form', $productData->form);
            }
            if (property_exists($productData, 'usage')) {
            $productData->usage = $this->getAttributeIdString('usage', $productData->usage);
            }
            if (property_exists($productData, 'application_area')) {
            $productData->application_area = $this->getAttributeIdString('application_area', $productData->application_area);
            }
            if (property_exists($productData, 'product_type')) {
            $productData->product_type = $this->getAttributeIdString('product_type', $productData->product_type);
            }
            if (property_exists($productData, 'manufacturer')) {
            $productData->manufacturer = $this->getAttributeIdString('manufacturer', $productData->manufacturer);
            }
            if (property_exists($productData, 'shade')) {
            $productData->shade = $this->getAttributeIdString('shade', $productData->shade);
            }
            if (property_exists($productData, 'skin_tone')) {
            $productData->skin_tone = $this->getAttributeIdString('skin_tone', $productData->skin_tone);
            }
            if (property_exists($productData, 'skin_type')) {
            $productData->skin_type = $this->getAttributeIdString('skin_type', $productData->skin_type);
            }
            if (property_exists($productData, 'undertone')) {
            $productData->undertone = $this->getAttributeIdString('undertone', $productData->undertone);
            }
            if (property_exists($productData, 'upc')) {
            $productData->upc = $productData->upc;
            }
            if (property_exists($productData, 'directions')) {
            $productData->directions = $productData->directions;
            }
            if (property_exists($productData, 'ingredients')) {
            $productData->ingredients = $productData->ingredients;
            }
            foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            $_attrCode = $attribute->getAttributeCode();
                if (Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID !== (int) $product->getStoreId()
                && !$product->getExistsStoreValueFlag($_attrCode)
                && !$attribute->isScopeGlobal()
                ) {
                $product->setData($_attrCode, false);
                }
                if ($this->_isAllowedAttribute($attribute) && (isset($productData->$_attrCode))) {
                $product->setData(
                    $_attrCode,
                    $productData->$_attrCode
                    );
                }
            }
            if (property_exists($productData, 'categories') && is_array($productData->categories)) {
            $product->setCategoryIds($productData->categories);
            }
            if (property_exists($productData, 'websites') && is_array($productData->websites)) {
                foreach ($productData->websites as &$website) {
                    if (is_string($website)) {
                        try {
                        $website = Mage::app()->getWebsite($website)->getId();
                        } catch (Exception $e) { }
                    }
                }
            $product->setWebsiteIds($productData->websites);
            }
            if (Mage::app()->isSingleStoreMode()) {
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
            }
            if (property_exists($productData, 'stock_data')) {
            $_stockData = array();
                foreach ($productData->stock_data as $key => $value) {
                $_stockData[$key] = $value;
                }
            $product->setStockData($_stockData);
            }
            if (property_exists($productData, 'tier_price')) {
            $tierPrices = Mage::getModel('bornintegration/catalog_product_attribute_tierprice_api_V2')
                 ->prepareTierPrices($product, $productData->tier_price);
             
            $product->setData(Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2::ATTRIBUTE_CODE, $tierPrices);
            }
    }
    
    public function getAttributeIdString($attributeCode, $attributeLabelString = ''){
        $attributeObject = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
        $frontendInput = $attributeObject->getFrontendInput();
            if(in_array($frontendInput, array('select','multiselect'))){
            $options = Mage::getModel('eav/entity_attribute_option')->getCollection()->setAttributeFilter($attributeObject->getId())->setStoreFilter()->toOptionArray();
            $resultingString = '';
                if($frontendInput == 'select'){
                    foreach($options as $option){
                        if($option['label'] == $attributeLabelString){
                        $resultingString = $option['value'];
                        break;
                        }
                    }
                }elseif($frontendInput == 'multiselect'){
                $tmpArray = array();
                $exploded = explode(',',$attributeLabelString);
                $match = count($exploded);
                    foreach($options as $option){
                        if($match <= 0){
                        break;
                        }
                        if(in_array($option['label'], $exploded)){
                        $tmpArray[] = $option['value'];
                        $match -= 1;
                        }
                    }
                $resultingString = (is_array($tmpArray) && count($tmpArray)) ? implode(',',$tmpArray): '';
                }else{
                $resultingString = $attributeLabelString;
                }
            }
        return $resultingString;
    }
}