<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Model_Observer
{
    public function onAdminhtmlInitSystemConfig($observer)
    {
        if (!Mage::helper('amseorichdata')->isYotpoReviewsEnabled()) {
            $observer->getConfig()->setNode(
                'sections/amseorichdata/groups/yotpo', false, true
            );
        }
    }

    public function onControllerProductInit($observer)
    {
        if (!Mage::getStoreConfigFlag('amseorichdata/breadcrumbs/extend')) {
            return;
        }

        $category = Mage::registry('current_category');

        if (!$category)
        {
            $product = Mage::registry('current_product');

            $categories = $product->getCategoryCollection();

            $select = $categories->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('entity_id'))
                ->order('level DESC')
                ->limit(1);
            ;

            $categoryId = $categories->getConnection()->fetchOne($select);

            if ($categoryId)
            {
                $category = Mage::getModel('catalog/category')->load($categoryId);

                if ($category)
                    Mage::register('current_category', $category);
            }
        }
    }
}
