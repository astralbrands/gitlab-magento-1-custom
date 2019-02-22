<?php 

class Born_Package_Block_Bundle_Catalog_Product_View_Type_Bundle extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle
{

    protected function getSwatchImage($_attributeId, $_configurableSku)
    { 
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_optionSwatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($_attributeId, null, $_configurableSku);
        if($_optionSwatch && $_optionSwatch->getData('image_file')){
            $_swatchImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). $_optionSwatch->getData('image_file');
            return $_swatchImage;
        }
        return;
    }

    protected function getAttributeLabelById($_attributeId)
    {
        $attributeDetails = Mage::getSingleton('eav/config');
        $attributeDetails = $attributeDetails->getAttribute('catalog_product', $_attributeId);

        if ($attributeDetails) {
            $optionValue = $attributeDetails->getSource()->getOptionText($_attributeId);
            return $optionValue;
        }

        return;
    }

    protected function getAttributeLabel($_product, $_attributeCode)
    { 
        if($_product){
            $_shade = $_product->getAttributeText($_attributeCode);
            return $_shade;
        }

        return;
    }

    protected function getAttributeId($_product, $attributeCode)
    {
        $_storeId = Mage::app()->getStore()->getStoreId();
        $_resource = Mage::getSingleton('catalog/product')->getResource();

        $attributeId = $_resource->getAttributeRawValue($_product->getId(), $attributeCode, $_storeId);

        return $attributeId;
    }

    protected function getAltImage($_product){ 
        $_bornProductListHelper = Mage::helper('born_package/catalog_product_list');
        $_imageSize = 100;
        $_productAltImage = $_bornProductListHelper->getProductImages($_product, $_imageSize, 'alt_image');

        if(is_array($_productAltImage)){
            $_productAltImage = $_productAltImage['alt_image'];
        }
        return $_productAltImage;
    }

    protected function getCustomConfig($_selection, $configurableData)
    {
        $_attributeCode = 'shade';
        $_attributeLabel = '';
        $_productSku = '';
        $_productSmallImage = '';
        $_swatchImage = '';


        $_productSku = $_selection->getSku();
        $_attributeId = $this->getAttributeId($_selection, $_attributeCode);

        $_productSmallImage = $_selection->getSmallImageUrl();

        if(isset($_attributeId) && !empty($_attributeId)) {
            $_product = Mage::getModel('catalog/product')->load($_selection->getId()); //load required to get custom attributes
            $_attributeLabel = $this->getAttributeLabel($_product, $_attributeCode);
            $_swatchImage = $this->getSwatchImage($_attributeId, $configurableData['sku']) ? $this->getSwatchImage($_attributeId, $configurableData['sku']) : $this->getAltImage($_product);
        }
			if(!isset($_swatchImage)||empty($_swatchImage)){
				$_swatchImage = '';
			}
        $config = array(
            'in_stock' => $this->getStockStatus($_selection),
            'product_id' => $_selection->getId(),
            'optionLabel'     => $_attributeLabel,
            'productSkus' => $_productSku,
            'productSmallImage' => $_productSmallImage,
            'swatchImage' => $_swatchImage
            );

        return $config;
    }

    protected function getStockStatus($_product)
    {
        if($_product->getStatus()){
            return true;
        }

        return false;
    }

    protected function getConfigurableBySku($_productSku)
    {
        $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $_productSku);

        if($_product){
            if($_product->getTypeId() == 'configurable'){
                return $_product;
            }
        }
        return;
    }

    protected function getConfigurableData($_productSku)
    {
        if ($_productSku) {
            $_product = $this->getConfigurableBySku($_productSku);

            if($_product){
                $configurableData = array();
                $configurableData['title'] = $_product->getName();
                $configurableData['sku'] = $_product->getSku();

                return $configurableData;
            }
        }
        return;
    }

     /**
     * Returns JSON encoded config to be used in JS scripts
     *
     * @return string
     */
    public function getJsonConfig()
    {
        Mage::app()->getLocale()->getJsPriceFormat();
        $optionsArray = $this->getOptions();
        $options      = array();
        $selected     = array();
        $currentProduct = $this->getProduct();
        /* @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper   = Mage::helper('core');
        /* @var $bundlePriceModel Mage_Bundle_Model_Product_Price */
        $bundlePriceModel = Mage::getModel('bundle/product_price');

        $preConfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preConfiguredFlag) {
            $preConfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }

        $position = 0;

        foreach ($optionsArray as $_option) {
            /* @var $_option Mage_Bundle_Model_Option */
            if (!$_option->getSelections()) {
                continue;
            }

            $optionId = $_option->getId();
            $configurableData = $this->getConfigurableData($_option->getTitle());
            $customConfigurableName = $configurableData['title'] ? $configurableData['title'] : $_option->getTitle();
            $customConfigurableSku = $configurableData['sku'];

            $option = array (
                'selections' => array(),
                // 'title'      => $_option->getTitle(),
                'title'      => $customConfigurableName,
                'isMulti'    => in_array($_option->getType(), array('multi', 'checkbox')),
                'position'   => $position++
            );

            $selectionCount = count($_option->getSelections());
            /** @var $taxHelper Mage_Tax_Helper_Data */
            $taxHelper = Mage::helper('tax');

            $firstOptionSelectionId = null;
            $index = 0;

            foreach ($_option->getSelections() as $_selection) {

                $_customConfig = $this->getCustomConfig($_selection, $configurableData);

                /* @var $_selection Mage_Catalog_Model_Product */

                $selectionId = $_selection->getSelectionId();

                if (!$firstOptionSelectionId && $selectionId) {
                    $firstOptionSelectionId = $selectionId;
                }

                $_qty = !($_selection->getSelectionQty() * 1) ? '1' : $_selection->getSelectionQty() * 1;
                // recalculate currency
                $tierPrices = $_selection->getTierPrice();
                foreach ($tierPrices as &$tierPriceInfo) {
                    $tierPriceInfo['price'] =
                        $bundlePriceModel->getLowestPrice($currentProduct, $tierPriceInfo['price']);
                    $tierPriceInfo['website_price'] =
                        $bundlePriceModel->getLowestPrice($currentProduct, $tierPriceInfo['website_price']);
                    $tierPriceInfo['price'] = $coreHelper->currency($tierPriceInfo['price'], false, false);
                    $tierPriceInfo['priceInclTax'] = $taxHelper->getPrice($_selection, $tierPriceInfo['price'], true,
                        null, null, null, null, null, false);
                    $tierPriceInfo['priceExclTax'] = $taxHelper->getPrice($_selection, $tierPriceInfo['price'], false,
                        null, null, null, null, null, false);
                }
                unset($tierPriceInfo); // break the reference with the last element

                $itemPrice = $bundlePriceModel->getSelectionFinalTotalPrice($currentProduct, $_selection,
                    $currentProduct->getQty(), $_selection->getQty(), false, false
                );

                $canApplyMAP = false;

                /* @var $taxHelper Mage_Tax_Helper_Data */
                $taxHelper = Mage::helper('tax');

                $_priceInclTax = $taxHelper->getPrice($_selection, $itemPrice, true,
                    null, null, null, null, null, false);
                $_priceExclTax = $taxHelper->getPrice($_selection, $itemPrice, false,
                    null, null, null, null, null, false);

                if ($currentProduct->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                    $_priceInclTax = $taxHelper->getPrice($currentProduct, $itemPrice, true,
                        null, null, null, null, null, false);
                    $_priceExclTax = $taxHelper->getPrice($currentProduct, $itemPrice, false,
                        null, null, null, null, null, false);
                }

                $selection = array (
                    'qty'              => $_qty,
                    'customQty'        => $_selection->getSelectionCanChangeQty(),
                    'price'            => $coreHelper->currency($_selection->getFinalPrice(), false, false),
                    'priceInclTax'     => $coreHelper->currency($_priceInclTax, false, false),
                    'priceExclTax'     => $coreHelper->currency($_priceExclTax, false, false),
                    'priceValue'       => $coreHelper->currency($_selection->getSelectionPriceValue(), false, false),
                    'priceType'        => $_selection->getSelectionPriceType(),
                    'tierPrice'        => $tierPrices,
                    'name'             => $_selection->getName(),
                    'plusDisposition'  => 0,
                    'minusDisposition' => 0,
                    'canApplyMAP'      => $canApplyMAP,
                    'tierPriceHtml'    => $this->getTierPriceHtml($_selection, $currentProduct),
                );

                $selection = array_merge($selection, $_customConfig);

                $responseObject = new Varien_Object();
                $args = array('response_object' => $responseObject, 'selection' => $_selection);
                Mage::dispatchEvent('bundle_product_view_config', $args);
                if (is_array($responseObject->getAdditionalOptions())) {
                    foreach ($responseObject->getAdditionalOptions() as $o => $v) {
                        $selection[$o] = $v;
                    }
                }
                $option['selections'][$selectionId] = $selection;


                if (($_selection->getIsDefault() || ($selectionCount == 1 && $_option->getRequired()))
                    && $_selection->isSalable()
                ) {
                    $selected[$optionId][] = $selectionId;
                }elseif($index++ == ($selectionCount - 1) && $_option->getRequired() && $_selection->isSalable())
                {
                    if (count($selected[$optionId]) < 1 ) {
                        $selected[$optionId][] = $firstOptionSelectionId;
                    }
                }
            }

            $options[$optionId] = $option;

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('bundle_option/' . $optionId);
                if ($configValue) {
                    $defaultValues[$optionId] = $configValue;
                }
            }
        }

        $config = array(
            'options'       => $options,
            'selected'      => $selected,
            'bundleId'      => $currentProduct->getId(),
            'priceFormat'   => Mage::app()->getLocale()->getJsPriceFormat(),
            'basePrice'     => $coreHelper->currency($currentProduct->getPrice(), false, false),
            'priceType'     => $currentProduct->getPriceType(),
            'specialPrice'  => $currentProduct->getSpecialPrice(),
            'includeTax'    => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'isFixedPrice'  => $this->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED,
            'isMAPAppliedDirectly' => Mage::helper('catalog')->canApplyMsrp($this->getProduct(), null, false)
        );

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $coreHelper->jsonEncode($config);
    }
}