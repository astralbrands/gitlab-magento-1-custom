<?php
class Born_OnePageOrder_Block_View extends Mage_Catalog_Block_Product_View_Abstract
{
    protected $_currentCategoryId = null;
    protected $_filterCategoryId = null;
    protected $_qtySteps = null;
    
    public function __construct(){
        parent::__construct();
        $this->setTemplate('onepageorder/view.phtml');
        $this->_currentCategoryId = $this->getRequest()->getParam('category_id', null);
        $this->_filterCategoryId = $this->getRequest()->getParam('refinement_id', null); 
        $this->_qtySteps = unserialize(Mage::getStoreConfig('onepageorder/general/qty_steps'));
    }

    public function getAllowAttributes($product){
			if (!is_null($product) && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $product->getTypeInstance(true)->getConfigurableAttributes($product);
			}
        return;
    }

    public function getPriceValueByIndex($parentProduct, $childProduct, $priceInfo, $finalPrice){
			if (!$childProduct || !$parentProduct || !is_numeric($finalPrice)) {
            return;
			}
			if ($parentProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $finalPrice;
			}
        $price = 0.0;
        $optionLabel = $childProduct->getAttributeText('size');
			if ($optionLabel && isset($priceInfo[$optionLabel]) && $priceInfo[$optionLabel]) {
            $priceInfo = $priceInfo[$optionLabel];
            $price += $this->_calcSelectionPrice($priceInfo, $finalPrice);
            $price = $price + $finalPrice;
            return $price;
			}
        return;
    }

    protected function _calcSelectionPrice($priceInfo, $productPrice){
			if($priceInfo['is_percent']){
            $ratio = $priceInfo['pricing_value']/100;
            $price = $productPrice * $ratio;
			}else{
            $price = $priceInfo['pricing_value'];
			}
        return $price;
    }



    public function getPriceInfo(Mage_Catalog_Model_Product $product){
		if (!$product || !$product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                return;
		}
        $prices = array();
		    if(null !== $this->getAllowAttributes($product)) {
                foreach ($this->getAllowAttributes($product) as $attribute) {
                    $tempPrices = $attribute->getPrices();
                }
                if (isset($tempPrices)) {
                    foreach ($tempPrices as $key => $price) {
                        if ($price && $price['store_label']) {
                            $prices[$price['store_label']] = $price;
                        }
                    }
                }
            }

        return $prices;
    }

    
    public function getNavigations(){
        $collection = Mage::getModel('catalog/category')
                ->getCollection()
                ->addLevelFilter(2)
                ->addAttributeToFilter('parent_id',array('eq'=>Mage::app()->getStore()->getRootCategoryId()))
                ->addOrderField('position', Mage_Catalog_Model_Resource_Category_Collection::SORT_ORDER_ASC)
                ->addIsActiveFilter()
                ->addNameToResult()
                ->addUrlRewriteToResult();

        //Filter by enable_for_onepageorder category attribute value
        foreach ($collection as $key => $category) {

            $_isEnabled = $this->getEnableForOnepageOrder($category->getId());

            if (!$_isEnabled) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    public function getChildCategoriesById($parentId)
    {
        if (!$parentId) {
            return;
        }

        $collection = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToFilter('parent_id',array('eq'=>$parentId))
                ->addOrderField('position', Mage_Catalog_Model_Resource_Category_Collection::SORT_ORDER_ASC)
                ->addIsActiveFilter()
                ->addNameToResult()
                ->addUrlRewriteToResult();

        return $collection;

    }

    public function isLoggedIn()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return true;
        }
        return false;
    }
    
    public function getActiveCategoryProductCollection($categoryId)
    {
        
    }
    
    public function getActiveNavId()
    {
        $navigations = $this->getNavigations();
        $result = null;
        if($navigations->count() > 0){
            $result = (!is_null($this->_currentCategoryId)) ? $this->_currentCategoryId: $navigations->getFirstItem()->getId();
        }
        return $result;
    }
    
    public function getActivecategory()
    {
        $categoryId = $this->getActiveNavId();
        $category = null;
        if(!is_null($categoryId))
        {
            $category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($categoryId);
        }
        return $category;
    }
    
    public function getRefinements(Mage_Catalog_Model_Category $category)
    {
        $childrenCategories = null;
        if($category instanceof Mage_Catalog_Model_Category){

            $childrenCategories = $this->getChildCategoriesById($category->getId());

            $childrenCategories->load();
        }

        //Filter by enable_for_onepageorder value
        foreach ($childrenCategories as $key => $category) {
            $_isEnabled = $this->getEnableForOnepageOrder($category->getId());

            if (!$_isEnabled) {
                $childrenCategories->removeItemByKey($key);
            }
        }

        return $childrenCategories;
    }

    public function filterByEnableOnePageOrder($collection)
    {

        if (!$collection || !$collection->count()) {
            return $collection;
        }

        foreach ($collection as $key => $category) {
            $_isEnabled = $this->getEnableForOnepageOrder($category->getId());

            if (!$_isEnabled) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    public function getEnableForOnepageOrder($categoryId)
    {
        $onePageOrderValue = Mage::getModel('born_package/catalog_category_data')->getOnePageOrderValue($categoryId);

        return $onePageOrderValue;
    }
    
    public function getChildCategories($filterOnePageOrder = false)
    {
        $refinments = $this->getRefinements($this->getActivecategory());
        $childrenCategories = null;
        if(!is_null($refinments) && count($refinments) > 0){
            $filters = (is_null($this->_filterCategoryId)) ? $refinments->getAllIds(): explode('_', $this->_filterCategoryId);

            $childrenCategories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addFieldToFilter('parent_id', array('in'=>$filters))
                    ->addFieldToFilter('level',array('eq'=>4))
                    ->addOrderField('position', Mage_Catalog_Model_Resource_Category_Collection::SORT_ORDER_ASC)
                    //->addAttributeToSelect('enable_for_onepageorder',array('eq'=>1))
                    ->addIsActiveFilter()
                    ->addNameToResult();

            if ($childrenCategories->count() <= 0) {
                $childrenCategories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addFieldToFilter('parent_id', array('eq'=>$this->getActivecategory()->getId()))
                    ->addFieldToFilter('entity_id', array('in'=>$filters))
                    ->addOrderField('position', Mage_Catalog_Model_Resource_Category_Collection::SORT_ORDER_ASC)
                        //->addAttributeToSelect('enable_for_onepageorder',array('eq'=>1))
                    ->addIsActiveFilter()
                    ->addNameToResult();
            }
        }

        if ($filterOnePageOrder) {
            $childrenCategories = $this->filterByEnableOnePageOrder($childrenCategories);
        }
    
        return $childrenCategories;
    }
    
    public function getCategoryRefinementUrl($currentUrl, $activeCategoryId, $id)
    {
        $currentRefinement = $this->_filterCategoryId;
        $refinment = null;
        if(is_null($currentRefinement)){
            $refinment = $id;
        }else{
            $explodedString = explode('_', $currentRefinement);
            if(in_array($id, $explodedString))
            {
                $key = array_search($id, $explodedString);
                unset($explodedString[$key]);
                $refinment = implode('_', $explodedString);
            }else{
                $explodedString[] = $id;
                $refinment = implode('_', $explodedString);
            }
        }
        $query = array('category_id' => $activeCategoryId);
        if(!is_null($refinment) && strlen($refinment) > 0){
            $query['refinement_id'] = $refinment;
        }
        return Mage::helper('core/url')->addRequestParam($currentUrl, $query);
    }
    
    public function getIsActiveRefinments($refinementId = null)
    {
        $result = false;
        if(is_null($refinementId) || strlen($this->_filterCategoryId) <=  0){
            $result = true;
        }
        
        $exploded = explode('_', $this->_filterCategoryId);
        if(is_array($exploded)){
            $result = (in_array($refinementId, $exploded)) ? true: false;
        }
        return $result;
    }
    
    public function getChildProducts($product)
    {
        $data = array();
        if(!is_null($product)){
            switch($product->getTypeId())
            {
                case Mage_Catalog_Model_Product_Type::DEFAULT_TYPE:
                                                                    $data[] = $product;
                                                                    break;
                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                                                                    foreach($product->getTypeInstance(true)->getUsedProducts(null, $product) as $_child)
                                                                    {
                                                                        $data[] = $_child;
                                                                    }
                                                                    break;
             case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                                                                 $coreResource = Mage::getSingleton('core/resource');
                                                                 $readAdapter = $coreResource->getConnection('core_read');
                                                                 $tableName = $coreResource->getTableName('bundle/selection');
                                                                 $query = "SELECT `selection_id`,`option_id`, `product_id`,`is_default`  FROM `{$tableName}` WHERE `parent_product_id`='".$product->getId()."'";
                                                                 $results = $readAdapter->fetchAll($query);
                                                                 if(is_array($results) && count($results) > 0){
                                                                     foreach($results as $_result){
                                                                         if($_result['is_default'] == '1') {
                                                                             $key = $_result['option_id'] . '_' . $_result['selection_id'];
                                                                             $data[$key] = Mage::getModel('catalog/product')->load($_result['product_id']);
                                                                         }
                                                                     }
                                                                 }
                                                                 break;
                
            }
        }
        return $data;
    }
    
    public function getJsonConfig()
    {
        
    }
    

    public function getTierPricesB2B($product, $qty = null, $totalBundlePrice = null)
    {
        if (is_null($product) || is_null($qty)) {
            return;
        }
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) 
        {
            $tierPercentage = Mage::getModel('bundle/product_price')->getTierPrice($qty, $product);

            $bundlePrice = $totalBundlePrice * (100 - $tierPercentage)/100;

            return number_format($bundlePrice, 2);
        }
    }
    
    public function getQtySteps()
    {
        
        $steps = array();
        if(count($this->_qtySteps) > 0)
        {
            foreach($this->_qtySteps as $_values)
            {
                $tmp = array();
                $tmp[0] = $_values['qty_from'];
                $tmp[1] = (isset($_values['qty_to']) && $_values['qty_to'] > 0) ? '-': '+';
                $tmp[2] = $_values['qty_to'];
                $steps[] = implode('',$tmp);
            }
        }
        return $steps;
    }
    
    public function getCalculationQty()
    {
        $qty = array();
        if(count($this->_qtySteps) > 0)
        {
            foreach($this->_qtySteps as $_values)
            {
                $qty[] = (isset($_values['qty_to']) && $_values['qty_to'] > 0) ? $_values['qty_to']: $_values['qty_from'];
            }
        }
        return $qty;
    }
    
    public function getSubmitUrl($product, $additional = array())
    {
        return $this->getUrl('born_onepageorder/index/save');
    }


    /**
     * Check if product belongs to refinement categories
     * @return boolean
     */
    public function isRefinementProduct(Mage_Catalog_Model_Product $product, $refinementIds)
    {
        if (!$refinementIds || !count($refinementIds)) {
            //If no refinement filter applied, no check required.
            return true;
        }

        if ($product instanceof Mage_Catalog_Model_Product) {
            $categoryIds = $product->getCategoryIds();

            foreach ($categoryIds as $categoryId) {
                if (in_array($categoryId, $refinementIds)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Get refinement Ids from current url
     * @param  [string] $url [Current Url]
     * @return [array]
     */
    public function getRefinementId($url)
    {
        $refinementKey = 'refinement_id';
        $query = $this->getUrlQuery($url);

        if ($query && is_array($query) && $query[$refinementKey]) {

            $refinementIdsTemp = $query[$refinementKey];
            $refinementIds =array();

            if (!strpos($refinementIdsTemp, '_') === false) {
                $refinementIdsTemp = explode('_', $refinementIdsTemp);
            }

            if (is_array($refinementIdsTemp)) {
                foreach ($refinementIdsTemp as $idValue) {
                    $refinementIds[] = $idValue;
                }
            }
            else{
                $refinementIds[] = $refinementIdsTemp;
            }

            if (count($refinementIds)) {
                return $refinementIds;
            }
        }

        return;
    }

    public function getUrlQuery($url)
    {
        if (!$url) {
            return;
        }

        parse_str(parse_url($url, PHP_URL_QUERY), $urlQuery);

        return $urlQuery;
    }
    
    public function getHowToUseContents()
    {
        $blockId = Mage::getStoreConfig('onepageorder/general/instruction_block');
        $html = '';
        if ($blockId) {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            if ($block->getIsActive()) {
                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());
            }
        }
        return $html;
    }
    
    public function getPreOrderLabel($product = null){
        if(is_null($product || !Mage::helper('core')->isModuleEnabled('Amasty_Preorder'))){
            return false;
        }
        
        $inventoryItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $backorderStatus = $inventoryItem->getBackorders();
        $preOrderNote = false;
        if($backorderStatus == Amasty_Preorder_Model_Rewrite_CatalogInventory_Source_Backorders::BACKORDERS_PREORDER){
            $preOrderNote = Mage::helper('ampreorder')->getProductPreorderNote($product);
        }
        return $preOrderNote;
    }

    public function getProductSkuColumnData($product)
    {
        if (!$product) {
            return;
        }

        if($product->getSku() == '8860127')
        {
            echo '';
        }

        $columnArray = array();

        if ($productType = $product->getAttributeText('product_type')) {
            $columnArray[] = $productType;
        }elseif($productType = $this->getProductTypeText($product)){
            $columnArray[] = $productType;
        }

        if ($sizeText = $product->getAttributeText('size')) {
            $columnArray[] = $sizeText;
        }elseif($sizeText = $this->getProductSizeText($product)){
            $columnArray[] = $sizeText;
        }

        if ($productSku = $product->getSku()) {
            $columnArray[] = '#' . $productSku;
        }

        if ($columnArray && count($columnArray) > 0) {
            return implode(': ', $columnArray);
        }

        return;
    }

    public function getProductTypeText($product)
    {
        $helper = Mage::Helper('born_package/catalog_product_data');

        $productType = $helper->getProductTypeText($product->getId());

        return $productType;
    }

    public function getProductSizeText($product)
    {
        $helper = Mage::Helper('born_package/catalog_product_data');

        $productType = $helper->getProductSizeText($product);

        return $productType;
    }

    protected function _convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }

        $price = $this->getCurrentStore()->convertPrice($price);
        if ($round) {
            $price = $this->getCurrentStore()->roundPrice($price);
        }

        return $price;
    }
     public function getTotalBundlePrice($bundleProduct, $selectionProducts)
    {
        $selectionQty = 1;
        $bundlePriceModel = Mage::getModel('bundle/product_price');

        if ($bundleProduct->getPriceType() == '1') {
            $totalPrice = $bundlePriceModel->getPrice($bundleProduct);
            return $totalPrice;
        }

        $totalPrice = 0;

        foreach ($selectionProducts as $selection) {
            $selectionPrice = $bundlePriceModel->getSelectionPrice($bundleProduct, $selection, $selectionQty);
            $totalPrice += $selectionPrice;
        }

        return $totalPrice;
    }
	public function getAvailableQuantity($_product)
	{
		return Mage::helper('born_onepageorder')->getAvailableQuantity($_product);
	}
	public function getStockThresholdQuantity($_product)
	{
		return Mage::helper('born_onepageorder')->getStockThresholdQuantity($_product);
	}
	public function getAlertBlock($_product)
	{
		$alertBlock = Mage::app()->getLayout()->createBlock('born_onepageorder/view')->setData('product',$_product);
		$alertBlock->setTemplate('onepageorder/stock_alert.phtml');
		return $alertBlock->toHtml();

	}
}

