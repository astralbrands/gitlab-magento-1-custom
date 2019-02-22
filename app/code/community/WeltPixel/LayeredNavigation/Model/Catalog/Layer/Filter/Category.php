<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category {

    protected $categData = array();
    protected $_layeredHelper = null;
    protected $_currentCategory = null;

    public function __construct() {
        parent::__construct();
        $this->_layeredHelper = Mage::helper('weltpixel_layerednavigation');
    }

    protected function _initItems() {
        $data = $this->_getItemsData();

        /* Sort the attributes as it was specified in backend position, name, count */
        if ($this->_layeredHelper->getCategoryDisplay() != 2) //tree listing
            Mage::helper('weltpixel_layerednavigation')->orderAttributes($data);

        $items = array();
        foreach ($data as $itemData) {

            if ($this->_layeredHelper->getCategoryDisplay() == 2) {
                $obj = new Varien_Object();
                $itemData['filtermodel'] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count']);
                $obj->setData($itemData);

                $items[] = $obj;
            } else {
                $items[] = $this->_createItem(
                        $itemData['label'], $itemData['value'], $itemData['count']
                );
            }
        }

        $this->_items = $items;
        return $this;
    }

    protected function _getItemsData() {
        if ($this->_layeredHelper->getCategoryDisplay() != 2) //tree listing, extra listing currently ignored
            return parent::_getItemsData();

        $key = $this->getLayer()->getStateKey() . '_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $this->_currentCategory = $this->getCategory();

            $root = Mage::getModel('catalog/category')
                    ->load($this->getLayer()->getCurrentStore()->getRootCategoryId());

            $this->getLayer()->setCurrentCategory($root);

            $categories = $root->getChildrenCategories();
            $level = 1;
            $this->collectCategories($categories, $level);

            $data = $this->categData;

            $this->getLayer()->setCurrentCategory($this->_currentCategory);

            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    protected function collectCategories($categories, $level) {
        $this->getLayer()->getProductCollection()
                ->addCountToCategories($categories);
        foreach ($categories as $category) {
            if ($category->getIsActive() && $category->getProductCount()) {
                $this->categData[] = array(
                    'label' => Mage::helper('core')->escapeHtml($category->getName()),
                    'value' => $category->getId(),
                    'count' => $category->getProductCount(),
                    'level' => $level,
                    'selected' => ($this->_currentCategory->getId() == $category->getId())
                );
            }

            $children = $category->getChildrenCategories();
            if ($children && count($children)) {
                $this->getLayer()->getProductCollection()->addCountToCategories($children);
                $this->collectCategories($children, $level + 1);
            }
        }
    }

    /**
     *  Customize it, add more categories, also Mage_Catalog_Model_Resource_Product_Collection
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        
        $filters =  $request->getParam($this->getRequestVar());
        $catids = explode('_', $filters);
        $catids = implode(',', $catids);
        
        $filter = (int) $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        
        $this->_categoryId = $filter;

        Mage::register('current_category_filter', $this->getCategory(), true);

        $this->_appliedCategory = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                    ->addCategoryFilter($this->_appliedCategory);
                    //->addCategoriesFilter(array(8));
            
         
            $this->getLayer()->getState()->addFilter(
                    $this->_createItem($this->_appliedCategory->getName(), $filter)
            );
        }
        
        return $this;
    }

    /**
     * do not show the level 2 items if the tree category listing is selected
     */
    public function getResetValue() {

        if ($this->_layeredHelper->getCategoryDisplay() != 2) //tree listing
            parent::getResetValue();
        else {
            if ($this->_appliedCategory) {
                /**
                 * Revert path ids
                 */
                $pathIds = array_reverse($this->_appliedCategory->getPathIds());
                $curCategoryId = $this->getLayer()->getCurrentCategory()->getId();
                if (Mage::getModel('catalog/category')->load($curCategoryId)->getLevel() == 2)
                    return null;
                if (isset($pathIds[1]) && $pathIds[1] != $curCategoryId) {
                    return $pathIds[1];
                }
            }
            return null;
        }
    }

}
