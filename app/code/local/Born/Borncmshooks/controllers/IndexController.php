<?php
class Born_Borncmshooks_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }

    public function getalldataAction(){
    	$page_code = $this->getRequest()->getParam('page_code');
    	$response = Mage::getModel('borncmshooks/borncmshooks')->getAllData($page_code);
    	$response = json_encode($response);
        $this->getResponse()->setBody($response);
    }

    public function getsectionAction(){
    	$page_code = $this->getRequest()->getParam('page_code');
    	$section_code = $this->getRequest()->getParam('section_code');
    	$response = Mage::getModel('borncmshooks/borncmshooks')->getSection($section_code, $page_code);
    	$response = json_encode($response);
        $this->getResponse()->setBody($response);
    }

    public function getfieldAction(){
    	$page_code = $this->getRequest()->getParam('page_code');
    	$field_code = $this->getRequest()->getParam('field_code');
    	$response = Mage::getModel('borncmshooks/borncmshooks')->getField($field_code, $page_code);
    	$response = json_encode($response);
        $this->getResponse()->setBody($response);
    }

    public function getrowAction(){
    	$page_code = $this->getRequest()->getParam('page_code');
    	$row_code = $this->getRequest()->getParam('row_code');
    	$response = Mage::getModel('borncmshooks/borncmshooks')->getRow($row_code, $page_code);
    	$response = json_encode($response);
        $this->getResponse()->setBody($response);
    }
}