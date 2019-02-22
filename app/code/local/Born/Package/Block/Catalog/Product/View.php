<?php
class Born_Package_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View {

    public function orderRoutines() {
        $product = $this->getProduct();
        $routineSkus = explode(',', str_replace(' ', '', $product->getRoutineSkus()));

        $postion = $product->getAttributeText('current_product_position');

        switch($postion) {
            case "1": array_unshift($routineSkus, $product->getSku()); break;
            case "2": array_splice($routineSkus, 1, 0, $product->getSku()); break;
            case "3": array_push($routineSkus, $product->getSku());
        }

        return $routineSkus;
    }

    public function getBenefits() {
        $product = $this->getProduct();
        $productSku = $product->getSku();
        $result = $this->getSwatches($product->getBenefits(), $product->getSku());
        return $result;
    }

    protected function getSwatches($optionId, $productSku = null)
    {  
        if ($optionId) {

            $result = array();
            $_model = Mage::getModel('born_package/catalog_attribute_sortorder');
            $_attributes = $_model->getAttributeByOptionId($optionId);

            foreach($_attributes as $index) {
                $_swatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($index['option_id'], null, $productSku);

                if($_swatch->getData('image_file') && $_swatch->getDescription()) {
                    $result[] = array(
                        'image' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). $_swatch->getData('image_file'),
                        'option_value' => $_swatch->getOptionValue(),
                        'description' => $_swatch->getDescription()
                        );
                }
            }  
            return $result; 
        }

        return;
    }

    public function isInWishlist() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if($customerId) {
            $product = $this->getProduct();
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
            foreach ($wishlist->getItemCollection() as $item) {
                if($item->getProductId() === $product->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getShowCmsContents()
    {
        $product = $this->getProduct();

        if($product->getShowCmsContents() == '282'){

            if ($product->getTypeId() == 'grouped') {
                return $this->getDisplayCmsOnGroup();
            }
            return true;
        }
        elseif($product->getShowCmsContents() == '281'){
            return false;
        }
        return true;
    }

    protected function getDisplayCmsOnGroup()
    {
        $_storeId = Mage::app()->getStore()->getStoreId();
        $path= 'catalog/display_cms_content_group_page/enable';
        $config = Mage::getStoreConfig($path, $_storeId);

        if ($config) {
            return true;
        }
        return false;
    }



    public function getSizeValue()
    {
        $_product = $this->getProduct();
        $_attributeCode = 'size';

        if(!$this->isConfigurableAttribute($_product, $_attributeCode)){
            return $_product->getAttributeText($_attributeCode);
        }
    }

    protected function isConfigurableAttribute($_product, $_attributeCode)
    {
        if ($_product->getTypeId() == 'configurable') {
            $productAttributes = $this->getConfigurableAttributesAsArray($_product);

            if($productAttributes && $_attributeCode){
                foreach ($productAttributes as $productAttribute) {
                    if ($productAttribute['attribute_code'] == $_attributeCode) {
                        return true;
                    }
                }
            }
        }

        return false;

    }
    public function getConfigurableAttributesAsArray($product)
    {
	return $product->getConfigurableAttributesAsArray($product);
    }


    public function getShadeGuideHtml()
    { 
        $_storeId = Mage::app()->getStore();
        $_staticBlockId = Mage::getStoreConfig('born_package/shade_guide_rules/static_block_id', $_storeId);

        if ($this->showShadeGuide() && $_staticBlockId) {
           try {
                $_html = $this->getLayout()->createBlock('cms/block')->setBlockId($_staticBlockId)->toHtml();
                return $_html;
            } catch (Exception $e) {
                //Mage::log($e);
            }
        }
        return;
    }

    public function showShadeGuide($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
	$_storeId = Mage::app()->getStore();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
        {
            $_configPath = 'born_package/shade_guide_rules/links';
            $_configCategoryIds = Mage::getStoreConfig($_configPath, $_storeId);
            $_configCategoryIds = unserialize($_configCategoryIds);

            $_categoryIds = $product->getCategoryIds();

            if ($_configCategoryIds) {
                foreach ($_configCategoryIds as $_id) {
                    if (in_array($_id['category_id'], $_categoryIds)) {
                        if ($this->isConfigurableAttribute($product, 'shade')) {
                            return true;
                        }
                    }
                }
            }
        }
        elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) 
        {
            return true;
        }

        return false;
    }

    public function processCmsHtml($value)
    {  
        $_cmsHelper = Mage::helper('cms');
        $_processor = $_cmsHelper->getPageTemplateProcessor();
        $_html = $_processor->filter($value);

        return $_html;
    }

    public function getDisplayHowTo($_product)
    {
        if (($_product->getData('how_to_image1') && $_product->getData('how_to_image1') != 'no_selection') || ($_product->getData('how_to_image2') && $_product->getData('how_to_image2') != 'no_selection')) {
            return true;
        }

        return false;
    }

    public function getAlertHtml()
    {
        $_product = $this->getProduct();

        if ($_product->getTypeId() == 'configurable') {
            $alertBlock = Mage::app()->getLayout()->createBlock('productalert/product_view', 'configurable.alert');
            $alertBlock->setTemplate('amasty/amxnotif/product/view_email.phtml');
            return $alertBlock->toHtml();
        }
    }
    
    public function getProductInfoHtml(){
        $product = $this->getProduct();
        $productInfo .= '<div class="productInfoDisplay">';
        $productInfo .= $this->getSkuHtml();
        $productInfo .= $this->getUpcHtml();        
        $productInfo .= '</div>';
        return $productInfo;
    }

    public function getSkuHtml(){
        $sku = '<span class="productInfoDisplaySku">';
        $sku .= '<span class="productInfoDisplaySkuLabel">';
        $sku .= 'SKU: ';
        $sku .= '</span>';
        $sku .= '<span class="productInfoDisplaySku">';
        $sku .= $product->getSku();
        $sku .= '</span>';
        $sku .= '</span>';
        return $sku;
    }
    
    public function getUpcHtml(){
        $upc = '<span class="productInfoDisplayUpc">';
        $upc .= '<span class="productInfoDisplayUpcLabel">';
        $upc .= 'UPC: ';
        $upc .= '</span>';
        $upc .= '<span class="productInfoDisplayUpc">';
        $upc .= $product->getUpc();        
        $upc .= '</span>';
        $upc .= '</span>';        
    }    
    
    public function getShadeInfoHtml()
    {
        $product = $this->getProduct();
            if (!$product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return;
            }
        $_shadeText = $product->getAttributeText('shade');
        $_shadeInfoHtml = '';
            if ($_shadeText) {
            $_shadeInfoHtml .= '<div class="viewclass shade">';
            $_shadeInfoHtml .= '<div class="shade-top">';
            $_shadeInfoHtml .= '<div class="shade-checked-text">';
            $_shadeInfoHtml .= 'Shade: ';
            $_shadeInfoHtml .= '<span class="shade-id">';
            $_shadeInfoHtml .= $product->getSku();
            $_shadeInfoHtml .= '</span>';
            $_shadeInfoHtml .= ' ';
            $_shadeInfoHtml .= '<span class="shade-label">';
            $_shadeInfoHtml .= $_shadeText;
            $_shadeInfoHtml .= '</span>';
            $_shadeInfoHtml .= '</div>';
            $_shadeInfoHtml .= '</div>';
            $_shadeInfoHtml .= '</div>';
            }
        return $_shadeInfoHtml;
    }
    
    public function getProduct2()
    {
        return $this->_productInstance ? $this->_productInstance : $this->_getData('product');
    }    

    public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }  
    
    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    public function getJsonConfig()
    {
        $config = array();
        /*if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }*/

        $_request = Mage::getSingleton('tax/calculation')->getDefaultRateRequest();
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true,
                null, null, null, null, null, false);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, false,
                null, null, null, null, null, false);
        } else {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        }
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (float)$tierPrice['website_price'], false) - $_priceExclTax
                , false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (float)$tierPrice['website_price'], true) - $_priceInclTax
                , false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    public function getProductTierPriceJson()
    {
        $websiteIdRange = array("0", Mage::app()->getWebsite()->getId());
        $groupRange = array(Mage_Customer_Model_Group::CUST_GROUP_ALL, Mage::getSingleton('customer/session')->getCustomerGroupId());
        $config = array();
        $product = $this->getProduct();
        $_finalPrice = $product->getFinalPrice();
        $_tierPrices = array();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        foreach ($product->getTierPrice() as $tierPrice) {
            if(in_array($tierPrice['website_id'], $websiteIdRange) && ($tierPrice['all_groups'] == 1 || in_array($tierPrice['cust_group'], $groupRange))){
                $_tierPrices[] = $tierPrice;
            }
        }
        $config['price_incl_tax'] = $_priceInclTax;
        $config['price_excl_tax'] = $_priceExclTax;
        $config['tierPrices'] = $_tierPrices;
        return Mage::helper('core')->jsonEncode($config);
    }

    public function getRoutineProductCollection($skus)
    {
        if ($skus && is_array($skus)) {

            $routineProductCollection = new Varien_Data_Collection();
            foreach ($skus as $sku) {
                $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                if ($_product) {
                    if (!$routineProductCollection->getItemById($_product->getId())) {
                        $routineProductCollection->addItem($_product);
                    }
                }
                else
                {
                    $_item = new Varien_Object();
                    $_item->setSku($sku);
                    $routineProductCollection->addItem($_item);
                }
            }

            return $routineProductCollection;
        }

        return;
    }

    public function getApplicationIcon($index) {

        $attribute = 'application_icon' . $index;
        $product = $this->getProduct();
        $optionId = $product->getData($attribute);
        $result = null;

        if ($optionId) {
            $result = $this->getSwatchImage($optionId, $product->getSku());
            if ($result && is_array($result)) {
                $result = array_shift($result);
                return $result['image'];
            }
        }
        return;
    }

    protected function getSwatchImage($optionId, $productSku = null)
    {
        if ($optionId) {

            $result = array();
            $_model = Mage::getModel('born_package/catalog_attribute_sortorder');
            $_attributes = $_model->getAttributeByOptionId($optionId);

            foreach($_attributes as $index) {
                $_swatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($index['option_id'], null, $productSku);

                if($_swatch->getData('image_file')) {
                    $result[] = array(
                        'image' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). $_swatch->getData('image_file'),
                        'option_value' => $_swatch->getOptionValue()
                    );
                }
            }
            return $result;
        }

        return;
    }

    public function getCmsDescriptionVideoLabel()
    {
        $_product = $this->getProduct();

        if($_product->getDescriptionVideoTitle()){
            return $_product->getDescriptionVideoTitle();
        }

        else{
            return $_product->getName();
        }
    }

    public function getCmsStudyResultsColor()
    {
        $_product = $this->getProduct();

        $_backgroundColor = $_product->getStudyResultsBackgroundHex();

        $_defaultColor = '7d8084'; //FE: Please check default color

        if($_backgroundColor && ctype_xdigit($_backgroundColor)){
            return $_backgroundColor;
        }

        return $_defaultColor;
    }

    public function log($msg)
    {
        //Mage::log($msg,1,'yu');
    }

    public function getApplicationBackgroundImage($index)
    {
        $_commonLabel = 'application_image';

        $_image = $this->getAttributeValueByLabel($_commonLabel, $index, $isImage = true);

        return $_image;
    }

    public function getAttributeValueByLabel($_commonLabel, $index, $isImage = null)
    {
        if (is_numeric($index) && $index < 3) {
            $_product = $this->getProduct();

            $_attributeIndex = $index;

            $_attribute = $_commonLabel . $_attributeIndex;

            $_value = null;

            if ($isImage) {
                try {
                    if($_product->getData($_attribute) && $_product->getData($_attribute) != 'no_selection')
                    {
                        $_value = $this->helper('catalog/image')->init($_product, $_attribute);
                    }

                } catch (Exception $e) {
                    //Mage::log($e);
                }
            }else{
                $_value = $_product->getData($_attribute);
            }
        }

        return $_value;

    }

    public function getApplicationTitle($index)
    {
        $_commonLabel = 'application_title';
        return $this->getAttributeValueByLabel($_commonLabel, $index);
    }

    public function getApplicationDescription($index)
    {
        $_commonLabel = 'application_description';
        return $this->getAttributeValueByLabel($_commonLabel, $index);
    }

    public function getApplicationColors($index)
    {
        if (is_numeric($index) && $index < 2) {
            $_product = $this->getProduct();

            $_attributeIndex = $index;

            $_attributes = array(
                'background_color' => 'application_background_hex'.$_attributeIndex,
                'text_color' => 'application_text_hex'.$_attributeIndex
            );

            $_applicationColor = array();

            $_defaultColor = $this->getApplicationDefaultColor();

            $_backgroundColor = $_product->getData($_attributes['background_color']);
            $_textColor = $_product->getData($_attributes['text_color']);

            if ($_backgroundColor && ctype_xdigit($_backgroundColor)) {
                $_applicationColor['background_color'] = $_backgroundColor;
            }else{
                $_applicationColor['background_color'] = $_defaultColor[$index]['background_color'];
            }

            if ($_textColor && ctype_xdigit($_textColor)) {
                $_applicationColor['text_color'] = $_textColor;
            }else{
                $_applicationColor['text_color'] = $_defaultColor[$index]['text_color'];
            }

            return $_applicationColor;
        }

        return;
    }

    protected function getApplicationDefaultColor()
    {
        $_defaultColor = array(
            '0' => array(
                'background_color' => '25272b',
                'text_color' => 'ffffff'
            ),
            '1' => array(
                'background_color' => 'ffffff',
                'text_color' => '000000'
            )
        );

        return $_defaultColor;
    }

    public function getStudyResultsArray()
    {

        $_resultArray = array();

        for($i = 1; $i < 4; $i++){
            $_resultArray[] = array(
                'title' => 'study_results_title' . $i,
                'description' => 'study_results_description' . $i
            );
        }
        return $_resultArray;
    }

    public function getStudyResultsCollection()
    {
        if (!$this->_studyResultsCollection) {
            $_studyResultsCollection = new Varien_Data_Collection();

            $_product = $this->getProduct();

            if (!$_product->getId()) {
                return;
            }

            $_studyResultArray = $this->getStudyResultsArray();

            foreach ($_studyResultArray as $_studyResult) {
                $_item = new Varien_Object();

                if ($_title = $_product->getData($_studyResult['title'])) {
                    $_item->setTitle($_title);
                }
                if ($_description = $_product->getData($_studyResult['description'])) {
                    $_item->setDescription($_description);
                }

                if ($_item->getData()) {
                    $_studyResultsCollection->addItem($_item);
                }

                $_item = null;
            }

            $this->_studyResultsCollection = $_studyResultsCollection;
        }

        return $this->_studyResultsCollection;
    }

    public function getVisualResultsArray()
    {
        return array(
            array(
                'image' => 'visual_results_image1',
                'description' => 'visual_results_description1'
            ),
            array(
                'image' => 'visual_results_image2',
                'description' => 'visual_results_description2'
            )
        );
    }

    public function getWishlistText()
    {
        return $this->__('Add to Wishlist');
    }

    /**
     * @deprecated
     * Please see Born_Package_Helper_Catalog_Product_Data::getUsageIconClass
     */

    public function getUsageIconClass()
    {
        $_product = $this->getProduct();

        $_usage = $_product->getUsage();
        if ($_usage) {

            //Get the first value if it contains multiple values
            if (strpos($_usage, ',')) {
                $_usage = explode(',', $_usage);
                $_usage = array_shift($_usage);
            }

            switch ($_usage) {
                case $this->getDayOptionValue():
                    return 'am-icon';
                    break;
                case $this->getNightOptionValue():
                    return 'pm-icon';
                    break;
                case $this->getDayNightOptionValue():
                    return 'ampm-icon';
                    break;
                default:
                    return;
                    break;
            }
        }
        return;
    }

}