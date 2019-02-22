<?php

require_once Mage::getModuleDir('controllers', 'Mage_Catalog').DS.'ProductController.php';

class WeltPixel_QuickView_Ajax_Catalog_ProductController extends Mage_Catalog_ProductController
{
    public function compareAction(){
        $response = array();
         
        if (($productId = (int) $this->getRequest()->getParam('product')) && $this->getRequest()->isAjax()) {
            $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
 
            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                
                $response['status'] = 'SUCCESS';
                $response['message'] = $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()));
                
                Mage::helper('catalog/product_compare')->calculate();
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
                
                $this->loadLayout();
                
                $compareBlock = $this->getLayout()->getBlock('product_compare_block');
                $compareBlock->setTemplate('page/html/links/compare.phtml');
                $sidebar = $compareBlock->toHtml();
                $response['menubar'] = $sidebar;
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
}
