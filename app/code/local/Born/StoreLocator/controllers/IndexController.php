<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_IndexController extends Mage_Core_Controller_Front_Action
{     
  	/**
     * search and send store list as JSON response
     */
	public function searchAction()
	{    
        $helper = Mage::helper('storelocator');
		if(!$helper->searchCheck()){
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($helper->getErrorMsg()));
        }else{
            $stores=$this->getLayout()->createBlock('storelocator/search');
            $this->getResponse()->setHeader('Content-type', 'application/json');                
            $this->getResponse()->setBody($stores->toHtml());
        }
                
	}

	public function indexAction()
	{
		$this->loadLayout();
                $pageTitle = Mage::getStoreConfig('storelocator/storelocator/page_title');
                $this->getLayout()->getBlock('head')->setTitle($pageTitle);
		$this->addActionLayoutHandles();      
		$this->renderLayout(); 		
	}
}


