<?php 
class Born_Package_Model_Container_Catalog_Product_View_Wishlist extends Enterprise_PageCache_Model_Container_Abstract {
	protected function _getCacheId() {
    	$key = time();
        return 'BORN_PACKAGE_CATALOG_PRODUCT_VIEW_WISHLIST' . md5($key);
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $block = new $block;


        $product = $block->getProduct();

        if(!$product)
        {
            $product = Mage::registry('current_product');
        }

        if (!($product && $product->getId()) && $this->_getProductId()) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($this->_getProductId());
 
            if ($product && $product->getId()) {
                $block->setProduct($product);
            }
        }

        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }

}