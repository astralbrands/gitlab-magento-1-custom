<?php

class Born_Borncmshooks_CraftmanshipController extends Mage_Core_Controller_Front_Action
{   
    public function indexAction(){
        $this->loadLayout();     
//        Zend_Debug::dump($this->getLayout()->getUpdate());
        $this->renderLayout();
    }
    
    public function detailAction(){
        $this->loadLayout();     
//        Zend_Debug::dump($this->getLayout()->getUpdate());
        $this->renderLayout();
    }
}