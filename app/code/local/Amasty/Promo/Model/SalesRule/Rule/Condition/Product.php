<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Model_SalesRule_Rule_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product
{

    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_sku'] = Mage::helper('salesrule')->__('Custom Options');

        if (!Mage::helper('ambase')->isModuleActive('Amasty_Rules')) {
            $attributes['stock_item_qty'] = Mage::helper('reports')->__('Stock Qty');
            $attributes['weight'] = Mage::helper('sales')->__('Weight');
        }
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $product = false;

        if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
            $product = $object->getProduct();
        } else {
            $product = Mage::getModel('catalog/product')
                ->load($object->getProductId());
        }

        $product->setQuoteItemSku($object->getSku());

        if (!Mage::helper('ambase')->isModuleActive('Amasty_Rules') && $this->getAttribute() == 'stock_item_qty') {
            if ($product->getTypeId() == 'configurable') {
                $children = $object->getChildren();
                $simple = $children[0];
                $productStockQty = $simple->getProduct()->getStockItem()->getQty();
            } else {
                $productStockQty = $product->getStockItem()->getStockQty();
            }

            $product->setStockItemQty($productStockQty);
        }

        //$newObject = new Varien_Object();
        $object->setProduct($product);
        if ($object->getIsPromo() && $object->getIsShipruleValidation() !== true) {
            return false;
        }

        return parent::validate($object);
    }
}
