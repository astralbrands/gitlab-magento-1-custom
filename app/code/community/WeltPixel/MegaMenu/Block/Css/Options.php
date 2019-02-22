<?php

class WeltPixel_MegaMenu_Block_Css_Options extends WeltPixel_MegaMenu_Block_Html_Topmenu
{

    public function appendMenu($menu) {
        foreach ($menu->getChildren() as $children) {
            $this->_menu->addChild($children);
        }
    }

    protected function _toHtml()
    {
        return $this->_getHtml($this->_menu);
    }

    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass = null)
    {
        $css = '';

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
            if ($child->getId() == 'home-link') {
                ++$counter;
                continue;
            }


            if ($child->getHeaderBgColor()) {
                $css .= "li.{$child->getPositionClass()} {\n";
                $css .= "    background-color: {$child->getHeaderBgColor()} !important;\n";
                $css .= "}\n";
            }

            if ($child->getHeaderBgHoverColor()) {
                $css .= "li.{$child->getPositionClass()}:hover {\n";
                $css .= "    background-color: {$child->getHeaderBgHoverColor()} !important;\n";
                $css .= "}\n";
            }

            $css .= "#wpmm-nav li.{$child->getPositionClass()} > a {\n";
            if ($child->getTitleColor()) {
                $css .= "    color: {$child->getTitleColor()} !important;\n";
            }
            $css .= "}\n";



            if ($child->getTitleHoverColor()) {
                $css .= "#wpmm-nav li.{$child->getPositionClass()} > a:hover {\n";
                $css .= "    color: {$child->getTitleHoverColor()} !important;\n";
                $css .= "}\n";
            }

            if ($child->getLevel() == 0) {
                $css .= "li.{$child->getPositionClass()} > .wpmm-nav-content {\n";
                if ($child->getContentBgColor()) {
                    $css .= "    background-color: {$child->getContentBgColor()} !important;\n";
                }
                if ($child->getContentBgImage()) {
                	$css .= "    background-image: url({$child->getContentBgImage()}) !important;\n";
                }
                switch ($child->getContentBgImgDm()) {
                    case WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display::REPEAT:
                        $css .= "    background-repeat: repeat;\n";
                        break;
                    case WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display::STRETCH:
                        $css .= "    background-size: cover;\n";
                        break;
                }
                $css .= "}\n";
            }

            if ($child->hasChildren()) {
                $css .= $this->_getHtml($child);
            }

            ++$counter;
        }

        if ($childLevel == 0) {
            if ($this->getDisplayMode() == WeltPixel_Selector_Model_Adminhtml_System_Config_Displaymode::BOXED) {
                $css .= "#wpmm-nav {\n";
                $css .= "    position: relative;\n";
                $css .= "}\n";
            }
        }

        return $css;
    }

    protected function getDisplayMode()
    {
        return Mage::getStoreConfig('weltpixel_selector/customization_settings/displaymode');
    }
}