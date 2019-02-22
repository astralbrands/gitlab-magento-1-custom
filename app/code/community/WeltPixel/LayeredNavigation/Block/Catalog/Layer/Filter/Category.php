<?php

class WeltPixel_LayeredNavigation_Block_Catalog_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category {

    protected $_showCategoryCount = false;
    
    public function __construct() {
        parent::__construct();
        $this->setTemplate('weltpixel/layerednavigation/catalog/layer/category-filter.phtml');
        $this->_filterModelName = 'catalog/layer_filter_category';
        $_helper = $this->helper('weltpixel_layerednavigation');
        $this->_showCategoryCount = $_helper->showProductCountOnCategories();
    }

    /**
     * $this->getItems()
     * $level =1;
     */
    public function _generateTree($data, $level) {
        $nrElements = count($data);
        foreach ($data as $key => $_item) :
            $_level = $_item->getLevel();
            if ($_level == $level) :
              //$generatedTree .= '<li class="weltpixel-layered-category-' . $_level . '">';
            endif;
            if ($_level > $level) :
                //open a new ul
                $generatedTree .= '<ul>';
                //$generatedTree .= '<li class="weltpixel-layered-category-' . $_level . '">';
            endif;
            if ($_level < $level) :
                for ($i = 0; $i < $level - $_level; $i++) :
                    $generatedTree .= '</ul></li>';
                endfor;
                //$generatedTree .= '<li class="weltpixel-layered-category-' . $_level . '">';
            endif;
            $generatedTree .= '<li class="weltpixel-layered-category-' . $_level . '">';
            $nextElem = $data[$key+1];
            if (isset($nextElem) && $_level < $nextElem->getLevel()) {
                $generatedTree .= '<span class="toggleSign">-</span>';
            }
            
            if ($_item->getCount() > 0 && (!$_item->getSelected())):
                $generatedTree .= '<a href="' . Mage::helper('core')->escapeUrl($_item->getFiltermodel()->getUrl()).'">' . $_item->getLabel() . '</a>';
            else: 
                $generatedTree .=  $_item->getLabel();
            endif;
            if ( !$this->_showCategoryCount ):
                continue;
            endif;
            if (Mage::helper('catalog')->shouldDisplayProductCountOnLayer()):
                $generatedTree .= '(' . $_item->getCount(). ')';
            endif;
            
            $level = $_level;
        endforeach;

        return $generatedTree;
    }

}
