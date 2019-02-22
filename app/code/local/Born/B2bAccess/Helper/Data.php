<?php
class Born_B2bAccess_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isActionAllowed($action = null)
    {
        $allowed = true;
        if($this->isModuleEnabled()){
            $storeId = Mage::app()->getStore()->getId();
            $moduleEnabled = (boolean)Mage::getStoreConfig('b2baccess_restriction/b2baccess/enabled',$storeId);
            $b2bGroups = Mage::getStoreConfig('b2baccess_restriction/b2baccess/restrict_groups');
            $b2bGroups = (strlen($b2bGroups) > 0) ? explode(',',$b2bGroups): array();
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if($moduleEnabled)
            {
                if (!$action || is_null($action)) 
                {
                    if($customerGroupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
                    {
                        $allowed = false;
                    }
                    elseif(in_array($customerGroupId, $b2bGroups)){
                        $allowed = false;
                    }
                }
                else
                {
                    switch($action)
                    {
                        case 'customer_account_create':
                        if($customerGroupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
                        {
                            $allowed = false;
                        }
                        break;

                        case 'bornajax_customer_account_myinfoSave':
                        case 'customer_address_new':
                        case 'customer_address_edit':
                        case 'customer_address_formPost':
                        case 'customer_address_delete':
                        if(in_array($customerGroupId, $b2bGroups)){
                            $allowed = false;
                        }
                        break;
                    }
                }

            }
        }
        return $allowed;
    }
}

