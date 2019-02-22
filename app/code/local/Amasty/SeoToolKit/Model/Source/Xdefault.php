<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Source_Xdefault
{
    public function toOptionArray()
    {
        $currentWebsite = Mage::app()->getRequest()->getParam('website');
        if ($currentWebsite) {
            $stores = Mage::app()->getWebsite($currentWebsite)->getStores();
        } else {
            $stores = Mage::app()->getStores();
        }

        $options = array();
        foreach ($stores as $store) {
            $websiteId        = $store->getWebsite()->getId();
            $storeId          = $store->getStoreId();
            $label = $store->getName();
            if (!$currentWebsite) {
                $label = $store->getWebsite()->getName() . " — " . $label;
            }

            $options[] = array(
                'label'              => $label,
                'value'              => $storeId,
                'website_id'         => $websiteId,
            );
        }

        usort($options, array($this, "compare"));
        array_unshift(
            $options,
            array(
                'value' => '',
                'label' => Mage::helper('amseotoolkit')->__('--Please Select--')
            )
        );
        return $options;
    }

    protected function compare($a, $b)
    {
        if ($a['website_id'] == $b['website_id']) {
            return ($a['value'] < $b['value']) ? -1 : 1;
        }

        return ($a['website_id'] < $b['website_id']) ? -1 : 1;
    }
}
