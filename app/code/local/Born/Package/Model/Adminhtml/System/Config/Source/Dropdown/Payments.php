<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Born_Package_Model_Adminhtml_System_Config_Source_Dropdown_Payments
{
    public function toOptionArray()
    {
        $allAvailablePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
      
        $result=array();
        foreach($allAvailablePaymentMethods as $a){
                $result[]=array(
                    'value'=>$a->getId(), 'label'=>$a->getId()
                );
        }

        return $result;
    }
}
