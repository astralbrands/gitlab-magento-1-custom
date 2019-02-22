<?php
class Astral_Optionswatch_Block_Catalog_Product_View_Type_Configurable extends Amasty_Xnotif_Block_Product_View_Type_Configurable
{
    public function __construct(){
        
    }

    public function getJsonConfig()
    {
        $attributes = array();
        $options    = array();
        $optionsSkus = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        $currentProduct = $this->getProduct();
        $currentProductSku = $currentProduct->getSku();
        $currentProduct = Mage::getModel('catalog/product')->load($currentProduct->getId());
        $productTypeTexts = array();
        $imageSize = Mage::getStoreConfig(Mage_Catalog_Helper_Image::XML_NODE_PRODUCT_BASE_IMAGE_WIDTH);
        $currentProductMedia = Mage::helper('optionswatch/data')->getConfigMediaImages($currentProduct, $imageSize);
        $attributes['productStocks'] = array();
        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();        
            if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
            }
        $productImageArray = array();    
            foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $productSku =  $product->getSku();
            $storeId    = Mage::app()->getStore()->getId();
            $loadpro    = Mage::getModel('catalog/product')->load($productId);
            $mediaApi      =  Mage::getModel("catalog/product_attribute_media_api");
            $mediaApiItems = $mediaApi->items($loadpro->getId());
                foreach ($mediaApiItems as $item) {
                    foreach($item['types'] AS $item_type){
                        if($item_type == 'image'){
                        $productImageArray[$productSku] = (string)$item['url'];    
                        }
                    }
                } 
        $productStock = $product->isSaleable();
        $productStockLink = Mage::helper('productalert')->setProduct($product)->getSaveUrl('stock');
            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());

                $productTypeTexts[$attributeValue] = $this->getProductTypeText($product);

                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                    $optionsSkus[$productAttributeId] = array();
                    $optionsStock[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                    $optionsSkus[$productAttributeId][$attributeValue] = array();
                    $optionsStock[$productAttributeId][$attributeValue] = array();
                }
                
                $options[$productAttributeId][$attributeValue][] = $productId;
                $optionsSkus[$productAttributeId][$attributeValue][] = $productSku;
                $optionsStock[$productAttributeId][$attributeValue][$productId] = array();
                $optionsStock[$productAttributeId][$attributeValue][$productId]['in_stock'] = $productStock;
                $optionsStock[$productAttributeId][$attributeValue][$productId]['sku'] = $productSku;
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
               'id'        => $productAttribute->getId(),
               'code'      => $productAttribute->getAttributeCode(),
               'label'     => $attribute->getLabel(),
               'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    $productTypeText = null;

                    if (isset($productTypeTexts[$value['value_index']])) {
                        $productTypeText = $productTypeTexts[$value['value_index']];
                    }

                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    $tierPriceInfo = $this->getTierPriceInfo(null, $configurablePrice);

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }
                    //add product sku
                   if (isset($optionsSkus[$attributeId][$value['value_index']])) {
                        $productSkus = $optionsSkus[$attributeId][$value['value_index']];
                    } else {
                        $productSkus = array();
                    }     
                    //add product stocks
                   if (isset($optionsStock[$attributeId][$value['value_index']])) {
                        $productStocks = $optionsStock[$attributeId][$value['value_index']];
                    } else {
                        $productStocks = array();
                    }                                                  
                    // add video embed url
                    $videoUrl = $currentProduct->getData('video_link');
                    $_productImage = '';
                foreach($productImageArray AS $key => $val){
                    if($key == $productSkus[0]){
                    $_productImage = $val;    
                    }                    
                }

                    $eavAttribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attribute->getAttributeId());
                    if( $eavAttribute->getAttributeCode() == "color" || $eavAttribute->getAttributeCode() == "finish" || $eavAttribute->getAttributeCode() == "shade"){ //Hard coded for color swatch
                        $optionSwatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($value['value_index'], null, $currentProductSku);
                        $optionImage = array();
			$swatchImage = '';
                        $swatchDescription = '';
                        if(array_key_exists( $value['label'],$currentProductMedia )){
                        $optionImage = $currentProductMedia[$value['label']];
                        }else{
                        $_baseImage = (string)Mage::helper('catalog/image')->init($product, 'image');
                        $optionImage = array();
                        $optionImage[] = $_baseImage;
                        }	
                            if(!!$optionSwatch && !!$optionSwatch->getId() && !!$optionSwatch->getData('image_file')){
                                $swatchImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). $optionSwatch->getData('image_file');
                                if($optionSwatch->getDescription()){
                                $swatchDescription = $optionSwatch->getDescription();
                                }
                            }
                            if(!$swatchImage){
                            $swatchImage = $product->getSmallImageUrl();
                            }

                            $info['options'][] = array(
	                        'id'                        => $value['value_index'],
	                        'label'                     => $value['label'],
	                        'price'                     => $configurablePrice,
	                        'oldPrice'                  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
	                        'products'                  => $productsIndex,
	                    	'productSkus'               => $productSkus, //add skus
	                   	'productStocks'             => $productStocks, //add stocks
                                'video'                     => $videoUrl,
	                    	'optionSwatch'              => $swatchImage,//add option swatch
                                'optionSwatchDescription'   => $swatchDescription, //add option swatch description
	                    	'mediaImage'                => array($_productImage), //add media image
                                'tierPrice'                 => $tierPriceInfo,
                                'productTypeText'           => $productTypeText,
                                'productType'               => $currentProduct->getTypeID()
	                    );
                    }else{
                        $optionImage = array();
                        if(array_key_exists( $value['label'],$currentProductMedia )){
                            $optionImage = $currentProductMedia[$value['label']];
                        }   

	                    $info['options'][] = array(
	                        'id'                => $value['value_index'],
	                        'label'             => $value['label'],
	                        'price'             => $configurablePrice,
	                        'oldPrice'          => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                                'video'             => $videoUrl, //add skus
	                        'products'          => $productsIndex,
                                'productSkus'       => $productSkus, //add skus
                                'productStocks'     => $productStocks, //add stocks
                                'mediaImage'        => array($_productImage), //add media image
                                'tierPrice'         => $tierPriceInfo,
                                'video'             => $videoUrl,
                                'productTypeText'   => $productTypeText,
                                'productType'       => $currentProduct->getTypeID()
	                    );
                    }
                    $tmp = $attributes['productStocks'];
                    $attributes['productStocks'] = $tmp + $productStocks; //                 
                    $optionPrices[] = $configurablePrice;
                }
            }

            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if($this->_validateAttributeInfo($info)) {
               $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getRateRequest(false, false, false);
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
        );

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'taxConfig'         => $taxConfig
        );

        if($attributeId != 183)
        {
            $config['chooseText'] = Mage::helper('catalog')->__('Choose an Option...');
        }

        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }
        
        // Added option swatch attributes to access on spConfig object
        $swatchAttribute = Mage::getModel('optionswatch/swatch')->toAttributeArray();
        //array_unshift($swatchAttribute);
        $config['optionswatch_attributes'] = $swatchAttribute;

        $config = array_merge($config, $this->_getAdditionalConfig());
        return Mage::helper('core')->jsonEncode($config);
    }
    
    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $skipSaleableCheck = true; //get all products
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }    
    
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        return $html;
    }

    protected function getTierPriceInfo($product = null, $configurablePrice = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }
        $tierPriceInfo = array();
        try {
            $prices = $product->getFormatedTierPrice();
            if (!$prices || count($prices) <= 0) {
                return;
            }
            foreach ($prices as $key => $price) {
                if ($configurablePrice && is_numeric($configurablePrice)) {

                    $price['price'] = $price['price'] + $configurablePrice;
                }
                $tierPriceInfo[$key]['price'] = number_format($price['price'],2,'.','');
                $tierPriceInfo[$key]['price_qty'] = number_format($price['price_qty'],0,'.','');                
            }
            if ($tierPriceInfo && count($tierPriceInfo) > 0) {
                return $tierPriceInfo;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }

    public function getProductTypeText($product)
    {   
        $helper = Mage::Helper('optionswatch/catalog_product_data');
        $productType = $helper->getProductTypeText($product->getId());
        return $productType;
    }
}