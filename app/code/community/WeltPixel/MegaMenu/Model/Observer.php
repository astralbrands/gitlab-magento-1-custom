<?php

class WeltPixel_MegaMenu_Model_Observer
    extends Mage_Catalog_Model_Observer
{

    protected function _addCategoriesToMenu($categories, $parentCategoryNode,
        $menuBlock, $addTags = false)
    {
        $categoryModel = Mage::getModel('catalog/category');
        $mediaCategoryUrl = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/';
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();
            $categoryModel->setId($category->getId());

            if ($addTags) {
                $menuBlock->addModelTags($categoryModel);
            }

            $tree = $parentCategoryNode->getTree();

            $customLink = $category->getWpCustomLink();
            $categoryData = array(
                'name' => $category->getName(),
                'right_block' => $category->getWpCatRightBlock(),
                'top_block' => $category->getWpCatTopBlock(),
                'bottom_block' => $category->getWpCatBottomBlock(),
                'columns' => $category->getWpNoColumns(),
                'static_block' => $category->getWpStaticBlocks(),
                'title_color' => $category->getWpTitleColor(),
                'title_hover_color' => $category->getWpTitleHoverColor(),
                'title_image' => ($category->getWpTitleImage()) ? $mediaCategoryUrl . $category->getWpTitleImage() : null,
                'header_bg_color' => $category->getWpHeaderBgColor(),
                'header_bg_hover_color' => $category->getWpHeaderBgHoverColor(),
                'content_bg_image' => ($category->getWpContentBgImage()) ? $mediaCategoryUrl . $category->getWpContentBgImage() : null,
                'content_bg_img_dm' => $category->getWpContentBgImgDm(),
                'content_bg_color' => $category->getWpContentBgColor(),
                'id' => $nodeId,
                'url' => isset($customLink) ? $customLink : Mage::helper('catalog/category')->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category),
                'display_mode' => $category->getWpDisplayMode()
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

            $this->_addCategoriesToMenu($subcategories, $categoryNode,
                $menuBlock, $addTags);
        }
    }

    public function addFlatCustomCategoryAttributesToSelect($observer)
    {
        $select = $observer->getEvent()->getSelect();
        $select->columns(array(
            'wp_cat_right_block',
            'wp_custom_link',
            'wp_cat_right_block',
            'wp_cat_top_block',
            'wp_cat_bottom_block',
            'wp_no_columns',
            'wp_static_blocks',
            'wp_title_color',
            'wp_title_hover_color',
            'wp_title_image',
            'wp_header_bg_color',
            'wp_header_bg_hover_color',
            'wp_content_bg_image',
            'wp_content_bg_img_dm',
            'wp_display_mode',
        ));

        return $this;
    }

}
