<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_CartController extends Mage_Core_Controller_Front_Action
{
    public function updateMultipleAction()
    {
        $data = $this->getRequest()->getParam('data');

        if (!$data) {
            $this->getResponse()->setHttpResponseCode(403);
            return;
        }

        $data = json_decode($data, true);

        foreach ($data as $request) {
            // Convert statements like "super_attributes[123]" => 321 to associative arrays
            parse_str(http_build_query($request), $request);

            $this->addProduct(new Varien_Object($request));
        }

        $this->_redirectReferer();
    }

    public function updateAction()
    {
        $requestData = $this->getRequest()->getParams();

        $this->addProduct(new Varien_Object($requestData));

        $this->_redirectReferer();
    }

    protected function addProduct(Varien_Object $requestData)
    {
        $productId = $requestData->getData('product_id');

        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product->getId()) {
            $limits = Mage::getSingleton('ampromo/registry')->getLimits();

            $sku = $product->getSku();

            $qty = array_key_exists($sku, $limits) ? $limits[$sku]['qty'] : 1;

            $addAllRule = isset($limits[$sku]) && $limits[$sku] > 0;
            $addOneRule = false;
            if (!$addAllRule) {
                foreach ($limits['_groups'] as $ruleId => $rule) {
                    if (in_array($sku, $rule['sku'])) {
                        $addOneRule = $ruleId;
                    }
                }
            } else if (isset($limits[$sku])) {
                $addOneRule = $limits[$sku]['rule_id'];
            }

            if ($addAllRule || $addOneRule) {
                $super = $requestData->getData('super_attributes');
                $options = $requestData->getData('options');
                $bundleOptions = $requestData->getData('bundle_option');
                $downloadableLinks = $requestData->getData('links');

                /* To compatibility amgiftcard module */
                $amgiftcardValues = array();
                if ($product->getTypeId() == 'amgiftcard') {
                    $amgiftcardFields = array_keys(Mage::helper('amgiftcard')->getAmGiftCardFields());
                    foreach ($amgiftcardFields as $amgiftcardField) {
                        if ($this->getRequest()->getParam($amgiftcardField)) {
                            $amgiftcardValues[$amgiftcardField] = $requestData->getData($amgiftcardField);
                        }
                    }
                }

                Mage::helper('ampromo')->addProduct(
                    $product,
                    $super,
                    $options,
                    $bundleOptions,
                    $addOneRule,
                    $amgiftcardValues,
                    $qty,
                    $downloadableLinks,
                    $requestData->getData()
                );
            }
        }
    }
}
