<?php

class WeltPixel_ShadeGuide_FoundationfinderController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        // redirect to 404 if module is disabled
        if (!Mage::helper('shadeguide')->foundationFinderIsEnabled()) {
            return $this->norouteAction();
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function collectionAction()
    {
        $params = $this->getRequest()->getParams();
        if (!Mage::helper('shadeguide')->foundationFinderIsEnabled() || !$params['isAjax']) {
            return $this->norouteAction();
        }

        $response['status'] = 0;

        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('type_id', array('eq' => 'simple'))
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setPageSize(false);
            ;

        if ($categoryId = Mage::helper('shadeguide')->getConfigValue('step_builder', 'category_filter')) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $collection->addCategoryFilter($category);
        }

        // apply filters
        $attribute = array();
        if (isset($params['filter']) && !empty($params['filter'])) {
            if (count($params['filter'])) {
                foreach ($params['filter'] as $attrCode => $attrValue) {
                    if ($attrCode == 'isAjax') continue;

                    $attribute[$attrCode] = $attrValue;
                    $collection = $this->filterThisCollection($collection, $attribute);
                    // reset $attribute
                    $attribute = array();
                }
            }
        }

        $allIds = array();
        foreach ($collection as $_product) {
            //Zend_Debug::dump($_product->getId());
            $allIds[] = $_product->getId();
        }

        $response['results'] = $this->getLayout()
            ->createBlock('catalog/product_list')
            ->setCollection($collection)
            ->setTemplate('catalog/product/list.phtml')
            ->toHtml();

        if ($response['results'] != '') {
            $response['status'] = 1;
            $response['productIds'] = Mage::helper('core')->jsonEncode($allIds);
            $response['container'] = 'product-results';
        }

        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function disableUnavailableOptionsAction()
    {
        $params = $this->getRequest()->getParams();
        if (!Mage::helper('shadeguide')->foundationFinderIsEnabled() || !$params['isAjax']) {
            return $this->norouteAction();
        }

        $response['status'] = 0;
        $attr = $params['attr'];

        $availableOptions = array();
        $productIds = Mage::helper('core')->jsonDecode($params['productIds']);
        $_resource = Mage::getModel('catalog/product')->getResource();

        foreach ($productIds as $productId) {
            $optionValue = explode(',', $_resource->getAttributeRawValue($productId, $attr, Mage::app()->getStore()));
            if (count($optionValue)) {
                foreach ($optionValue as $value) {
                    if (in_array($value, $availableOptions)) continue;
                    $availableOptions[] = $value;
                }
            }

            $productType = $_resource->getAttributeRawValue($productId, 'type_id', Mage::app()->getStore());
            if ($productType == 'configurable') {
                $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productId);
                foreach ($childrenIds[0] as $childId) {
                    $optionValue = explode(',', $_resource->getAttributeRawValue($childId, $attr, Mage::app()->getStore()));
                    if (count($optionValue)) {
                        foreach ($optionValue as $value) {
                            if (in_array($value, $availableOptions)) continue;
                            $availableOptions[] = $value;
                        }
                    }
                }
            }
        }

        if (count($availableOptions)) {
            $response['status'] = 1;
            $response['availableOptions'] = $availableOptions;
        }

        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function filterThisCollection($collection, $attribute)
    {
        $attrCode = key($attribute);
        $attrValue = $attribute[key($attribute)];
        $_resource = Mage::getModel('catalog/product')->getResource();
        foreach ($collection as $key => $_product) {
            $hasOption = false;

            if ($optionValue = $_resource->getAttributeRawValue($_product->getId(), $attrCode, Mage::app()->getStore())) {
                $optionValue = explode(',', $optionValue);
                // if (strpos($optionValue, $attrValue) !== false) {
                $intersect = array_intersect($attrValue, $optionValue);
                if ($intersect) {
                    $hasOption = true;
                }
            }

            if (!$hasOption && $_product->getTypeId() == 'configurable') {
                $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($_product->getId());
                foreach ($childrenIds[0] as $childId) {
                    if ($optionValue = $_resource->getAttributeRawValue($childId, $attrCode, Mage::app()->getStore())) {
                        $optionValue = explode(',', $optionValue);
                        // if (strpos($optionValue, $attrValue) !== false) {
                        $intersect = array_intersect($attrValue, $optionValue);
                        if ($intersect) {
                            $hasOption = true;
                            break;
                        }
                    }
                }
            }

            if (!$hasOption) {
                 $collection->removeItemByKey($_product->getId());
            }
        }

        return $collection;
    }
}