<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_SalesRule_Rule_Condition_Product_Subselect
    extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    /**
     * @param Varien_Object $object
     * @param bool $triggered
     * @return bool
     */
    public function validate(Varien_Object $object, $triggered = false)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
        $items = $object->getQuote()->getAllItems();

        foreach ($items as $item) {
            // fix magento bug
            if ($item->getParentItemId()) {
                continue;
            }

            // for bundle we need to add a loop here
            // if we treat them as set of separate items
            $validator = new Amasty_Promo_Model_SalesRule_Rule_Condition_Product_Combine();
            $validator->setData($this->getData());
            $result = $validator->validate($item);
            $this->setData($validator->getData());

            if ($result) {
                $total += $item->getData($attr);
            }
        }

        return $this->validateAttribute($total);
    }

    protected function _initializeProductAttributesInfo()
    {
        parent::_initializeProductAttributesInfo();

        foreach (Mage::helper('ampromo')->getIsNotAllowedAssignedAttributes() as $code) {
            $conditionKey = "salesrule/rule_condition_product_attribute_assigned|$code";
            unset($this->_productAttributesInfo['product_attribute_isset'][$conditionKey]);
        }
    }
}
