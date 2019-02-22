<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
class Amasty_Xnotif_Block_Product_View_Type_Configurable extends Amasty_Xnotif_Block_Product_View_Type_Configurable_Pure
{
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        if ('product.info.options.configurable' == $this->getNameInLayout()
            && 'true' != (string)Mage::getConfig()->getNode('modules/Amasty_Stockstatus/active')
            && !Mage::app()->getRequest()->isAjax()
        ) {
            $_attributes = $this->getProduct()->getTypeInstance(true)
                ->getConfigurableAttributes($this->getProduct());
            foreach ($this->getAllowProducts() as $product) {
                $key = array();
                foreach ($_attributes as $attribute) {
                    $key[] = $product->getData($attribute->getData('product_attribute')->getData('attribute_code'));
                }

                $stockStatus = (!$product->isInStock())? Mage::helper('amxnotif')->__('Out of Stock') : '';
                if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Preorder/active')
                    && Mage::helper('ampreorder')->getIsProductPreorder($product)
                ) {
                    $stockStatus =  Mage::helper('ampreorder')->getProductPreorderNote($product);
                }
                if ($key) {
                    $saleable = $product->isSaleable();
                    $aStockStatus[implode(',', $key)] = array(
                        'is_in_stock'   => $saleable,
                        'custom_status_icon' => '',
                        'custom_status' => $stockStatus,
                        'product_id'    => $product->getId()
                    );

                    if (!$saleable) {
                        $aStockStatus[implode(',', $key)]['stockalert'] =
                            Mage::helper('amxnotif')->getStockAlert($product, $this->getProduct()->getId());
                    }
                }
            }
            foreach ($aStockStatus as $k=>$v) {
                if (!$v['is_in_stock'] && !$v['custom_status']) {
                    $v['custom_status'] = Mage::helper('amxnotif')->__('Out of Stock');
                    $aStockStatus[$k] = $v;
                }   
            }
            $html = '<script type="text/javascript">
						try{
							var changeConfigurableStatus = true;
							var stStatus = new StockStatus(' . Zend_Json::encode($aStockStatus) . ');
						}
                            catch(ex){}
                    </script>' . $html;
        }
        return $html;
    }

    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());

            $websiteId = Mage::app()->getStore()->getWebsiteId();
            foreach ($allProducts as $product) {
                if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_DISABLED
                    && in_array($websiteId, $product->getWebsiteIds())
                ) {
                    $product->getStockItem()->setData('is_in_stock', 1);
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
}
