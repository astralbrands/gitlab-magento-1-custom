<?php


class Born_Package_Block_Page_Html_Cos_Topmenu extends Born_Package_Block_Page_Html_Topmenu 
{
/* 
        Do not delete the &nbsp; from this or else the styling for the header will be incorrect !!!!
    */
        protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass, $showHoverImage = true)
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

                if ($category->getLevel() == 2) {
                    if ($category->getHideImageNavigationMenu()) {
                        $showHoverImage = false;
                    }else{
                        $showHoverImage = true;
                    }
                }

                if($category->getIsTitle()) {
                    $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                    $html .= $category->getHideTitleNavigationMenu() ? '<span class="hide-title">' : '<span>';
                    $html .= $this->escapeHtml($child->getName()) . '</span>';   
                } elseif($childLevel < 3) {

                    $_url = ($category->getCategoryUrlAlias()) ? Mage::getBaseUrl() . $category->getCategoryUrlAlias() : $child->getUrl();
                    $_url = rtrim($_url,'/');
                    $html .= '<li '. $this->_getRenderedMenuItemAttributes($child) . '>';
                    $html .= '<a href="' . $_url . '" ' . $outermostClassCode . ' target="'.$category->getLinkTarget().'"><span>'
                    . $this->escapeHtml($child->getName()) . '</span></a>';
                }

                if ($showHoverImage) {
                    //Subcategory Hover Thumbnail
                    if($category->getLevel() == 3 || $category->getLevel() == 4){
                        $html .= $this->getHoverThumbnailHtml($category);
                    }
                }

                if ($child->hasChildren()) {
                    if (!empty($childrenWrapClass)) {
                        $html .= '<div class="' . $childrenWrapClass . '">';
                    }

                    $html .= '<a href="#" class="arrow"></a><ul class="level' . $childLevel . '">';

                    if ($showHoverImage) {

                        if($childLevel == 0 && $outermostClass)
                        {
                            $assets = $this->getCategoryThumbnailImages($category);
                            $html .= $this->thumbnailImagesToHtml($assets);
                        }
                    }

                    $html .= '<div class="menu-links-wrap child-level-' . $childLevel . '">';
                    $html .= $this->_getHtml($child, $childrenWrapClass, $showHoverImage);
                    $html .= '</div>';

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


        /**
         * @deprecated
         */
        protected function getShowNavImages()
        {
            $_storeId = Mage::app()->getStore()->getStoreId();

            $_config = Mage::getStoreConfig('catalog/category_nav_images/enable', $_storeId);

            if ($_config) {
                return true;
            }

            return false;
        }
    }