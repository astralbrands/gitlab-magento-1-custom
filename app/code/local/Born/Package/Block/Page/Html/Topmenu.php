<?php


class Born_Package_Block_Page_Html_Topmenu extends Mage_Page_Block_Html_Topmenu {
    public $_thumbnail = null;


    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }

    /*
        Do not delete the &nbsp; from this or else the styling for the header will be incorrect !!!!
    */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';

                $child->setClass($outermostClass);
            }

            $categoryId = str_replace('category-node-', '', $child->getId());
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($categoryId == $this->getProfessionalCategoryId()) {
                continue;
            }

            if($category->getIsTitle()) {
                $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<span>' . $this->escapeHtml($child->getName()) . '</span>';
            } elseif($childLevel < 2) {
                $_url = ($category->getCategoryUrlAlias()) ? Mage::getUrl($category->getCategoryUrlAlias()) : $child->getUrl();
                $_url = rtrim($_url,'/');
                $html .= '<li '. $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<a href="' . $_url . '" ' . $outermostClassCode . '><span>'
                    . $this->escapeHtml($child->getName()) . '</span></a>';
            }
            //Subcategory Hover Thumbnail
            if($category->getLevel() == 3){
                $html .= $this->getHoverThumbnailHtml($category);
            }

            if ($child->hasChildren() && $childLevel < 1) {
                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="' . $childrenWrapClass . '">';
                }

                $html .= '<a href="#" class="arrow"></a><ul class="level' . $childLevel . '">';

//                if ($childLevel === 0) {
//                    $html .= '<span>';
//                }

                if($childLevel == 0 && $outermostClass)
                {
                    $assets = $this->getCategoryThumbnailImages($category);
                    $html .= $this->thumbnailImagesToHtml($assets);
                }

                $html .= '<div class="menu-links-wrap">';
                $html .= $this->_getHtml($child, $childrenWrapClass);
                $html .= '</div>';

//                if($childLevel === 0) {
//                    $html .= '</span>';
//                }

                $html .= '</ul>';

                if (!empty($childrenWrapClass)) {
                    $html .= '</div>';
                }
            }

            $html .= '</li>&nbsp;';

            $counter++;
        }

        return $html;
    }

    protected function getHoverThumbnailHtml($category)
    {
        $thumbnailImageName = $category->getData('thumbnail_image01');

        if($thumbnailImageName)
        {
           $thumbnailUrl = Mage::getBaseUrl('media', array('_secure'=>true)).'catalog/category/' . $thumbnailImageName;


           $_html = '<div class="hover-thumbnail category-' . $category->getId() . ' category-level-' . $category->getLevel() . '">';
           $_html .= '<img src="' . $thumbnailUrl .'" />';
           $_html .= '</div>';

           return $_html;
       }
       return;
   }

    protected function getCategoryThumbnailImages($category)
    {
        $_attributes = array('thumbnail_cta_link', 'thumbnail_image01', 'thumbnail_image02');

        $_thumbnailAssets = array();

        foreach ($_attributes as $attribute) {
            if(!isset($_thumbnailAssets[$attribute]))
            {
                $_thumbnailAssets[$attribute] = $category->getData($attribute);
            }
        }
        return $_thumbnailAssets;
    }

    protected function thumbnailImagesToHtml($_thumbnailAssets)
    {
        if($_thumbnailAssets)
        {
            $ctaLink = Mage::getBaseUrl() . $_thumbnailAssets['thumbnail_cta_link'];
            unset($_thumbnailAssets['thumbnail_cta_link']);

            $_html = '<div class="category-thumbnail">';

            $_isWrapperCreated = false;

            foreach($_thumbnailAssets as $key => $value)
            {
                $_htmlClassName = str_replace('_','-', $key);

                if(strpos($key, 'image') !== false && $value)
                {
                    $thumbnailUrl = Mage::getBaseUrl('media', array('_secure'=>true)).'catalog/category/' . $value;

                    $_html .= '<div class="' . $_htmlClassName . '">';
                    $_html .= '<a href="' . $ctaLink . '">';
                    $_html .= '<img src="' . $thumbnailUrl .'" />';
                    $_html .= '</a>';
                    $_html .= '</div>';
                }
            }

            $_html .= '</div>';
        }
        return $_html;
    }

    /**
     * @deprecated
     * [replaced by getCategoryThumbnailImages due to text will now be part of the images]
     */
    protected function getCategoryThumbnailAssets($category)
    {
        $_attributes = array('thumbnail_image01', 'thumbnail_image02', 'thumbnail_title', 'thumbnail_subtitle', 'thumbnail_description',
            'thumbnail_cta_text', 'thumbnail_cta_link', 'thumbnail_text_color');

        $_thumbnailAssets = array();

        foreach ($_attributes as $attribute) {
            if(!isset($_thumbnailAssets[$attribute]))
            {
                $_thumbnailAssets[$attribute] = $category->getData($attribute);
            }
        }
        return $_thumbnailAssets;
    }

    protected function thumbnailToHtml($_thumbnailAssets)
    {
        if($_thumbnailAssets)
        {
            $colorString = $this->getColorString($_thumbnailAssets['thumbnail_text_color']);
            unset($_thumbnailAssets['thumbnail_text_color']);

            $_html = '<div class="category-thumbnail '. $colorString .'">';

            $_isWrapperCreated = false;

            foreach($_thumbnailAssets as $key => $value)
            {
                $_htmlClassName = str_replace('_','-', $key);

                if(strpos($key, 'image') !== false && $value)
                {
                    $thumbnailUrl = Mage::getBaseUrl('media', array('_secure'=>true)).'catalog/category/' . $value;

                    $_html .= '<div class="' . $_htmlClassName . '">';
                    $_html .= '<img src="' . $thumbnailUrl .'" />';
                    $_html .= '</div>';
                }
                elseif($key == 'thumbnail_cta_text' && $value)
                {

                    $_html .= '<a class="category-thumbnail__btn '. $colorString .'" href="' . Mage::getBaseUrl() . $_thumbnailAssets['thumbnail_cta_link'] . '">';
//                    $_html .= '<h2 class="' . $_htmlClassName . '">';
                    $_html .= $value;
//                    $_html .= '</h2>';
                    $_html .= '</a>';
                    if ($_isWrapperCreated == true) {
                        $_html .= '</div>';
                        $_isWrapperCreated = false;
                    }

                    unset($_thumbnailAssets['thumbnail_cta_link']);
                }
                elseif($value)
                {
                    if ($_isWrapperCreated == false) {
                        $_html .= '<div class="category-thumbnail__about">';
                        $_isWrapperCreated = true;
                    }
                    $_html .= '<h2 class="' . $_htmlClassName . '">';
                    $_html .= $value;
                    $_html .= '</h2>';
                }
            }

            $_html .= '</div>';
        }
        return $_html;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren() && $item->getLevel() < 1) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    protected function getColorString($int)
    {
        if($int == 1)
        {
            return "white";
        }
        else{
            return "black";
        }
    }


}