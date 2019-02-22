<?php

/**
 * Class Born_Mediacenter_Helper_Data
 */
class Born_Mediacenter_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCustomerGroupSelect()
	{
		$customer = Mage::getModel('customer/group')->getCollection()->addFieldToFilter('customer_group_id', array('gt'=> 0));
		$customerGroupOptions = '';
		foreach($customer as $type) {
			$customerGroupOptions .= '<option value='.$type->getCustomerGroupId().'>'.$type->getCustomerGroupCode().'</option>';
		   }
		return $customerGroupOptions;
	}
}
	 