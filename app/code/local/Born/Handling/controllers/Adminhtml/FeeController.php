<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Born_Handling_Adminhtml_FeeController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){
                $this->loadLayout()
                ->_addContent(
                $this->getLayout()
                ->createBlock('handling/adminhtml_order_create_info')
                ->setTemplate('born/handling/order/create/info.phtml'))
                ->renderLayout();
    }
}