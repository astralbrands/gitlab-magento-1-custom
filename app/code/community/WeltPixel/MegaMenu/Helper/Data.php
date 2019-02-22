<?php

class WeltPixel_MegaMenu_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    public function getCssOptionsUrl()
    {
        return Mage::getUrl('weltpixel_megamenu/css/options',
            array('_secure' => true));
    }

    public function getMobileCssMedia()
    {
        $widthThreshold = Mage::getStoreConfig('weltpixel_megamenu/mobile/width_threshold');
        return 'media="only screen and (max-width: ' . $widthThreshold . 'px)"';
    }

    public function getDisplayModeClass($displayMode)
    {
        $displayOption = 'menu_fullwidth';
        switch ($displayMode) {
            case WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display_Mode::FULL_WIDTH :
                $displayOption = 'menu_fullwidth';
                break;

            case WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display_Mode::SECTIONED :
                $displayOption = 'menu_sectioned';
                break;

            case WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display_Mode::DROPDOWN :
                $displayOption = 'menu_dropdown';
                break;
            default:
                break;
        }

        return $displayOption;
    }

    function addCategoriesToMenu($categories, $parentCategoryNode)
    {
        $categoryModel = Mage::getModel('catalog/category');
        $mediaCategoryUrl = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/';
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $categoryModel->load($category->getId());

            $tree = $parentCategoryNode->getTree();

            $customLink = $categoryModel->getWpCustomLink();
            $categoryData = array(
                'name'                      => $category->getName(),
                'right_block'               => $categoryModel->getWpCatRightBlock(),
                'top_block'                 => $categoryModel->getWpCatTopBlock(),
                'bottom_block'              => $categoryModel->getWpCatBottomBlock(),
                'columns'                   => $categoryModel->getWpNoColumns(),
                'static_block'              => $categoryModel->getWpStaticBlocks(),
                'title_color'               => $categoryModel->getWpTitleColor(),
                'title_hover_color'         => $categoryModel->getWpTitleHoverColor(),
                'title_image'               => ($categoryModel->getWpTitleImage()) ? $mediaCategoryUrl . $categoryModel->getWpTitleImage() : null,
                'header_bg_color'           => $categoryModel->getWpHeaderBgColor(),
                'header_bg_hover_color'     => $categoryModel->getWpHeaderBgHoverColor(),
                'content_bg_image'          => ($categoryModel->getWpContentBgImage()) ? $mediaCategoryUrl . $categoryModel->getWpContentBgImage() : null,
                'content_bg_img_dm'         => $categoryModel->getWpContentBgImgDm(),
                'content_bg_color'          => $categoryModel->getWpContentBgColor(),
                'id'                        => $nodeId,
                'url'                       => isset($customLink) ? $customLink : Mage::helper('catalog/category')->getCategoryUrl($category),
                'is_active'                 => false,
                'display_mode'              => $categoryModel->getWpDisplayMode()
            );
            $categoryModel->unsetData();
            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            $flatHelper = Mage::helper('catalog/category_flat');
            if ($flatHelper->isEnabled() && $flatHelper->isBuilt(true)) {
                $subcategories = (array) $category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->addCategoriesToMenu($subcategories, $categoryNode);
        }
    }

}
