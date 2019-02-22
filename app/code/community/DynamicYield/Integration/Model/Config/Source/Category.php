<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Model_Config_Source_Category
 */
class DynamicYield_Integration_Model_Config_Source_Category
{
    const CATEGORY_LEVEL = '-';

    public function getCategories() {

        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->load()
            ->toArray();

        $categoryList = array();
        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => $category['name'],
                    'level'  =>$category['level'],
                    'value' => $catId
                );
            }
        }

        return $categoryList;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $categories = $this->getCategories();

        foreach($categories as $category)
        {
            $prefix = static::CATEGORY_LEVEL;

            for($i=1; $i<$category['level']; $i++) {
                $prefix = $prefix . static::CATEGORY_LEVEL;
            }

            $options[] = array(
                'label' => $prefix . $category['label'],
                'value' => $category['value']
            );
        }

        return $options;
    }

}