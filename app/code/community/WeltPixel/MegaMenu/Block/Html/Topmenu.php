<?php

class WeltPixel_MegaMenu_Block_Html_Topmenu extends Mage_Page_Block_Html_Topmenu {

    public function _construct() {
        parent::_construct();

        if (Mage::getStoreConfig('weltpixel_megamenu/general/enable')) {
            $this->_addHomeLink();

            Mage::dispatchEvent('wp_page_block_html_topmenu_gethtml_before', array(
                'menu' => $this->_menu,
                'block' => $this
            ));

            $this->_menu->setOutermostClass('level-top');
            $this->_menu->setPositionClass('nav');
            $this->_menu->setChildrenWrapClass('wrapper-nav-columns');
        }
    }

//    public function setTemplate($template)
//    {
//        if (!Mage::getStoreConfig('weltpixel_megamenu/general/enable')) {
//            return parent::setTemplate($template);
//        }
//
//        $this->_template = 'weltpixel/page/html/topmenu.phtml';
//        return $this;
//    }

    protected function _addHomeLink() {
        if (Mage::getStoreConfig('weltpixel_megamenu/general/display_home_link')) {
            $homeNode = new Varien_Data_Tree_Node(array(
                        'name' => Mage::helper('core')->__('Home'),
                        'id' => 'home-link',
                        'url' => Mage::getBaseUrl(),
                        'is_active' => Mage::getBlockSingleton('page/html_header')->getIsHomePage()
                            ), 'id',  $this->_menu->getTree());
            $this->_menu->addChild($homeNode);
        }
    }

    public function getMenu() {
        return $this->_menu;
    }

    public function getRenderedMenuItemAttributes($child) {
        return $this->_getRenderedMenuItemAttributes($child);
    }

    public function renderChild(Varien_Data_Tree_Node $child, $childrenWrapClass) {
        return $this->_getHtml($child, $childrenWrapClass);
    }

    public function getStaticBlock($blockId) {
        $html = '';
        $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
        if ($block->getIsActive()) {
            /* @var $helper Mage_Cms_Helper_Data */
            $helper = Mage::helper('cms');
            $processor = $helper->getBlockTemplateProcessor();
            $html = $processor->filter($block->getContent());
            $this->addModelTags($block);
        }
        return $html;
    }

    public function getWidthThreshold() {
        return Mage::getStoreConfig('weltpixel_megamenu/mobile/width_threshold');
    }

    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass) {
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

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '  class="nav-link">'
                    . $this->escapeHtml($child->getName()) . '</a>';

            if ($child->hasChildren()) {
                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="' . $childrenWrapClass . '">';
                }
                $html .= '<ul class="level' . $childLevel . '">';
                $html .= $this->_getHtml($child, $childrenWrapClass);
                $html .= '</ul>';

                if (!empty($childrenWrapClass)) {
                    $html .= '</div>';
                }

                $html .= '<span class="subnav-toggle"><i class="icon icon-angle-down"></i><i class="icon icon-angle-up"></i></span>';
            }
            $html .= '</li>';

            $counter++;
        }

        return $html;
    }

}
