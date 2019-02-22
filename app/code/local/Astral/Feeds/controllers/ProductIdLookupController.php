<?php
/**
 * The purpose of this controller is to transfer a sku to a product ID
 */
class Astral_Feeds_ProductIdLookupController 
    extends Mage_Core_Controller_Front_Action {

    /**
     * Returns a collect of all products
     * @return json string containing product information
     */
    public function indexAction() {

        $sku = Mage::app()->getRequest()->getParam('product-sku');

        if (!isset($sku)) {
            $errorMsg = [
                'status' => 400,
                'error' => 'ERROR_INVALID_PARAMETER',
                'message' => 'Sku Undefined.'
            ];

            $obj = json_encode($errorMsg);
            $this->getResponse()->setHeader('Content-type', 'application/json')
                                ->setHeader('HTTP/1.0', 400, true)
                                ->setBody($obj);
            return;
        }
        
        $sku = trim($sku);
        $sku = (filter_var($sku, FILTER_SANITIZE_STRING));
        $sku = (filter_var($sku, FILTER_SANITIZE_SPECIAL_CHARS));

        if (!isset($sku) || empty($sku)) {
            $errorMsg = [
                'status' => 400,
                'error' => 'ERROR_INVALID_PARAMETER',
                'message' => 'Sku Invalid.'
            ];

            $obj = json_encode($errorMsg);
            $this->getResponse()->setHeader('Content-type', 'application/json')
                                ->setHeader('HTTP/1.0', 400, true)
                                ->setBody($obj);
            return;
        }
 
        $query = 'SELECT sku, entity_id
                FROM catalog_product_entity
                WHERE sku = "' . $sku . '" LIMIT 1';

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $_product = $readConnection->fetchall($query);

        if (count($_product) < 1) {
            $errorMsg = [
                'status' => 400,
                'error' => 'ERROR_INVALID_PARAMETER',
                'message' => 'Invalid Sku.'
            ];
            $obj = json_encode($errorMsg);
            $this->getResponse()->setHeader('Content-type', 'application/json')
                                ->setHeader('HTTP/1.0', 400, true)
                                ->setBody($obj);
            return;
        }

        $formKey = Mage::getSingleton('core/session')->getFormKey();

        $productDto =  [
            'status' => 200,
            'data' => [
                'sku' => $_product[0]['sku'],
                'product_id' => $_product[0]['entity_id'],
                'form_key' => $formKey,             
            ]
        ];

        $obj = json_encode($productDto);
        $this->getResponse()->setHeader('Content-type', 'application/json')
                            ->setHeader('HTTP/1.0', 200, true)
                            ->setBody($obj);
        return;
    }

    public function findIdBySkuAction() {
        $sku = Mage::app()->getRequest()->getParam('product-sku');

        if (!isset($sku)) {
            $errorMsg = [
                'status' => 400,
                'error' => 'ERROR_INVALID_PARAMETER',
                'message' => 'Sku Undefined.'
            ];

            $obj = json_encode($errorMsg);
            $this->getResponse()->setHeader('Content-type', 'application/json')
                                ->setHeader('HTTP/1.0', 400, true)
                                ->setBody($obj);
            return;
        }

        $sku = trim($sku);
        $sku = (filter_var($sku, FILTER_SANITIZE_STRING));
        $sku = (filter_var($sku, FILTER_SANITIZE_SPECIAL_CHARS));

        if (!isset($sku) || empty($sku)) {
            $errorMsg = [
                'status' => 400,
                'error' => 'ERROR_INVALID_PARAMETER',
                'message' => 'Sku Invalid.'
            ];

            $obj = json_encode($errorMsg);
            $this->getResponse()->setHeader('Content-type', 'application/json')
                                ->setHeader('HTTP/1.0', 400, true)
                                ->setBody($obj);
            return;
        }

        $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
        $formKey = Mage::getSingleton('core/session')->getFormKey();

        $productDto = [
            'status' => 200,
            'data' => [
                'sku' => $_product->sku,
                'product_id' => $_product->entity_id,
                'form_key' => $formKey,             
            ]
        ];

        $obj = json_encode($productDto);
        $this->getResponse()->setHeader('Content-type', 'application/json')
                            ->setHeader('HTTP/1.0', 200, true)
                            ->setBody($obj);
        return;
    }
}