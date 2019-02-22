<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Controller_Router
    extends Mage_Core_Controller_Varien_Router_Abstract
{
    protected $request;

    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $front->addRouter('amlocator', $this);
        return $this;
    }


    public function match(Zend_Controller_Request_Http $request)
    {
        $storeUrl = Mage::getStoreConfig('amlocator/locator/url');
        $realUrl = $request->getPathInfo();
        $this->request = $request;
        if ( ($realUrl=="/".$storeUrl) && (stripos($realUrl, ".html") !== false) ){
            $this->forwardAmlocator();
        }elseif( ($realUrl=="/".$storeUrl."/") ){
            $this->forwardAmlocator();
        }elseif(stripos($realUrl, $storeUrl) !== false){
            $this->forwardAmlocator();
        }
        if ( ($realUrl=="/".$storeUrl) && (stripos($realUrl, ".html") === false) ){
            Mage::app()->getResponse()->setRedirect(Mage::getUrl(Mage::getStoreConfig('amlocator/locator/url')."/" ), 301);
        }
    }

    protected function forwardAmlocator()
    {
        $reservedKey = "locator";
        $realModule = 'Amasty_Locator';

        $this->request->setPathInfo($reservedKey);
        $this->request->setModuleName('amlocator');
        $this->request->setRouteName('amlocator');
        $this->request->setControllerName('index');
        $this->request->setActionName('index');
        $this->request->setControllerModule($realModule);

        $file = Mage::getModuleDir('controllers', $realModule) . DS
            . 'IndexController.php';
        include $file;

        //compatibility with 1.3
        $class = $realModule . '_IndexController';
        $controllerInstance = new $class(
            $this->request, $this->getFront()->getResponse()
        );

        $this->request->setDispatched(true);
        $controllerInstance->dispatch('index');
    }
}