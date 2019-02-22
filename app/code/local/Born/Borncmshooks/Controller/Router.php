<?php

class Born_Borncmshooks_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard{
    
    public function match(Zend_Controller_Request_Http $request){
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        
        $front = $this->getFront();
        $pathInfo = trim($request->getPathInfo(), '/');
        $params = explode('/', $pathInfo);

        if((isset($params[0])) && ($params[0] == 'craftmanship')) {
             
             if((isset($params[1])) && ($params[1] == 'craftmanship')) {
                
                 if((isset($params[2])) && ($params[2] == 'detail')) {
                    $request->setModuleName('borncmshooks')  
                       ->setControllerName('craftmanship')
                       ->setActionName('detail');
                }else{
                    return false;
                }
             }else{
                 return false;
             }

            $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                $pathInfo
            );
            return true;
        }
        return false;
    }
}