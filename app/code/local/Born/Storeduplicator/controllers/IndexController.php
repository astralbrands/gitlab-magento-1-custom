<?php
class Born_Storeduplicator_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Store Duplicator"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("store duplicator", array(
                "label" => $this->__("Store Duplicator"),
                "title" => $this->__("Store Duplicator")
		   ));

      $this->renderLayout(); 
	  
    }
}