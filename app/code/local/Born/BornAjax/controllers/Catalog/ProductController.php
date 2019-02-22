<?php

require_once(Mage::getBaseDir() . '/app/code/core/Mage/Catalog/controllers/ProductController.php');

class Born_BornAjax_Catalog_ProductController extends Mage_Catalog_ProductController
{
	/**
	 * Chen Born 10/22/14 
	 * for quick view on listing page 
	 *
	 * @return unknown
	 */
    public function quickviewAction(){
        $productId  = (int) $this->getRequest()->getParam('id');
        $storeId = Mage::app()->getStore()->getId();
        $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
        $productInfo = array();
        if(!!$product && !!$product->getId()){
            $productInfo['id'] = $product->getId();
            $productInfo['name'] = $product->getData('name');           
            $productInfo['price'] = number_format($product->getFinalPrice(),2);
            $productInfo['form_key'] = Mage::getSingleton('core/session')->getFormKey();
            $productInfo['default_qty'] = $this->getProductDefaultQty($product) < 1 ? 1 :$this->getProductDefaultQty($product) * 1;
            $productInfo['is_saleable'] = $product->isSaleable();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($productInfo));
        return true;        
    }

    public function quickviewblockAction() { 
        $productId = (int) $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        #Set product and current_product registry
        Mage::register('product', $product);
        Mage::register('current_product', $product);

        #Set product view layout
        $viewHelper = Mage::helper('catalog/product_view');
        $viewHelper->initProductLayout($product, $this);
        Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
		try {
			Mage::dispatchEvent('catalog_controller_product_init_after',array('product' => $product,'controller_action' =>$this ));
			#Render layout
			$this->loadLayout();
			$this->renderLayout();
		} catch(Exception $e) {
			$response['status'] = 'ERROR';
            $response['message'] = $this->__('Please log in to access this product');
			
 			$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true)->setHttpResponseCode(200);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));			 
		}
    }

    /*
     * product complete view in lookbook
     *
     */
    public function detailviewAction() {
        $productId  = (int) $this->getRequest()->getParam('id');
        $productBlock = $this->getLayout()->createBlock('Mage_Catalog_Block_Product_View');
        $productBlock->setProductId($productId);
        $product = $productBlock->getProduct();
        
        $productInfo = array();
        if(!!$product && !!$product->getId()){
            $productInfo['id'] = $product->getId();
            $productInfo['name'] = $product->getData('name');
            $productInfo['short_description'] = $product->getData('short_description');
            $productInfo['description'] = $product->getData('description');
            $productInfo['price'] = number_format($product->getFinalPrice(),2);
            $productInfo['url'] = $product->getProductUrl();
            //$productInfo['image'] = Mage::helper('package/catalog')->getImageUrl($product,'image');
            
            //get product images
            $productImages = $product->getMediaGalleryImages();
            $images = array();
            foreach($productImages as $image) {
                $src = Mage::helper('catalog/image')->init($product, 'thumbnail', $image->getFile())->resize(600, 750);

                $images[] = array(
                    'label' => $image->getLabel(),
                    'src' => $src->__toString()
                );
            }
            $productInfo['media_gallery'] = $images;

            $productInfo['form_key'] = Mage::getSingleton('core/session')->getFormKey();
            $productInfo['default_qty'] = $this->getProductDefaultQty($product) < 1 ? 1 :$this->getProductDefaultQty($product) * 1;
            $productInfo['is_saleable'] = $product->isSaleable();
            $productInfo['option_price'] = $productBlock->getJsonConfig();
            $productInfo['related_products'] = $this->getLayout()->createBlock('catalog/product_list_related')->setTemplate('catalog/product/list/related.phtml')->toHtml();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($productInfo));
        return true;
    }


    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|Mage_Catalog_Model_Product $product
     * @return int|float
     */
    public function getProductDefaultQty($product)
    {
        $qty = $product->getStockItem()->getMinSaleQty();
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }
        return $qty;
    }

    public function shareProductAction() {
        $productId  = (int) $this->getRequest()->getParam('id');
        $name = $this->getRequest()->getParam('name');
        $email = $this->getRequest()->getParam('email');
        $message = $this->getRequest()->getParam('message');

        if($productId && $name && $email) {
            $storeId = Mage::app()->getStore()->getId();
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);

            $templateId = 'custom_product_share';

            $email_template  = Mage::getModel('core/email_template')->loadDefault($templateId);

            $email_template_variables = array(
                'product_name' => $product->getName(),
                'product_link' => $product->getProductUrl(),
                'message' => $message
            );

            $sender_name = 'HATCH';
            $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');

            $email_template->setSenderName($sender_name);
            $email_template->setSenderEmail($sender_email);

            $email_template->send($email, $name, $email_template_variables);

            $response['status'] = 'SUCCESS';
            $response['message'] = 'An email has been sent to your friend.';    
        } else {
            $response['status'] = 'ERROR';
            $response['message'] = 'Something was missing.';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return true;
        
    }

    public function shopThisPostAction() {
        $products = explode(',', str_replace(' ', '', $this->getRequest()->getParam('skus')));

        $response = array();
        foreach($products as $productSku) {

            if($productSku) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
                
                $response[] = array(
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'image' => (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(600),
                    'link' => $product->getProductUrl()
                );
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return true;
    }

}
