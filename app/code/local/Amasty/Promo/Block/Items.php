<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Block_Items extends Mage_Core_Block_Template
{
    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getItems()
    {
        if (!$this->hasData('items')) {
            /** @var Amasty_Promo_Helper_Data $helper */
            $helper = Mage::helper('ampromo');
            //force reload new items collection, possible changes in quote object
            $products = $helper->getNewItems(true);
            $this->setData('items', $products);
        }

        return $this->getData('items');
    }

    public function getItemsByRule()
    {
        if (!$this->hasData('items_by_rule')) {
            $products = $this->getItems();
            $result = array();

            foreach ($products as $product) {
                /** @var Mage_SalesRule_Model_Rule $rule */
                $rule = $product->getData('ampromo_rule');

                if (!array_key_exists($rule->getId(), $result)) {
                    $result[$rule->getId()] = array(
                        'rule' => $rule,
                        'products' => array()
                    );
                }

                $result[$rule->getId()]['products'][] = $product;
            }
            $this->setData('items_by_rule', $result);
        }

        return $this->getData('items_by_rule');
    }

    public function renderItem(Mage_Catalog_Model_Product $product, $rule = null)
    {
        $block = $this->getLayout()->createBlock(
            'ampromo/items_item',
            'ampromo_item_' . $product->getId(),
            array(
                'product' => $product,
                'rule' => $rule
            )
        );

        $block->setParentBlock($this);

        return $block->toHtml();
    }

    public function getModeName()
    {
        $mode = Mage::getStoreConfig('ampromo/popup/mode');

        return $mode == Amasty_Promo_Model_Source_DisplayMode::MODE_INLINE ? 'inline' : 'popup';
    }

    public function useCarousel()
    {
        if (Mage::getStoreConfig('ampromo/popup/mode') == Amasty_Promo_Model_Source_DisplayMode::MODE_INLINE) {
            return false;
        }

        $items = $this->getItems();

        if (!$items || count($items) <= 3) {
            return false;
        }

        return true;
    }

    public function getPopupHeader()
    {
        return trim(Mage::getStoreConfig('ampromo/popup/block_header'));
    }

    public function getAddToCartText()
    {
        return trim(Mage::getStoreConfig('ampromo/popup/add_to_cart_text'));
    }

    public function getOptionsJs()
    {
        return $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('catalog/product/view/options/js.phtml')
            ->toHtml();
    }

    public function getCalendarJs()
    {
        return $this->getLayout()
            ->createBlock('core/html_calendar')
            ->setTemplate('page/js/calendar.phtml')
            ->toHtml();
    }

    public function _toHtml()
    {
        if (count($this->getItems()) > 0) {
            if (Mage::getStoreConfigFlag('ampromo/popup/multiselect')
                && Mage::getStoreConfig('ampromo/popup/mode') == Amasty_Promo_Model_Source_DisplayMode::MODE_INLINE) {

                $this->setTemplate('amasty/ampromo/items/by_rule.phtml');
            }

            return parent::_toHtml();
        }
        else {
            return '';
        }
    }

    public function getFormActionUrl()
    {
        $params = Mage::helper('ampromo')->getUrlParams();

        return $this->getUrl('ampromo/cart/updateMultiple', $params);
    }
}
