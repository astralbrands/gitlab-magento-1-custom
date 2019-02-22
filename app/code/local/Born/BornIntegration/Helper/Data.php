<?php
class Born_BornIntegration_Helper_Data extends Mage_Core_Helper_Abstract {
    const LOGFILE = 'born_integration.log';
	protected $groupCodeCondition = array(
				"Employee - Pur",
				"Employee - Cosmedix"
			);

    public function error($message) {
       
    }
	/* To get customer group code while exporting customer to X3 for BCGCOD field */
    public function getStatisticalCustomerGroupCode($customerGroupId, $websiteId = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $groupTable = $resource->getTableName('customer/customer_group');
        $readAdapter = $resource->getConnection('core_read');
        
        $query = "SELECT `sage_code`,`customer_group_code` FROM `{$groupTable}` WHERE `customer_group_id` ='".$customerGroupId."'";
        $row = $readAdapter->fetchRow($query);
		$groupCode = $row['customer_group_code'];
		
		if(in_array($groupCode,$this->groupCodeCondition)){
			$result = $row['sage_code'];
		} else {
            $websiteCode = Mage::app()->getWebsite($websiteId)->getCode();
            switch($websiteCode){
                case 'cosb2b':
                            $result = 'CSRTL';
                            break;
                case 'cosb2c':
                            $result = 'CSWEB';
                            break;
                case 'pur':
                            $result = 'PCWEB';
                            break;
				case 'cosb2bint': 
							$result = 'CSINT';
							break;
            }
        }
        
        return $result;
    }
    public function getStatisticalGroupCode($customerGroupId, $websiteId = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $groupTable = $resource->getTableName('customer/customer_group');
        $readAdapter = $resource->getConnection('core_read');
        
        $query = "SELECT `sage_code` FROM `{$groupTable}` WHERE `customer_group_id`='".$customerGroupId."'";
        $result = $readAdapter->fetchOne($query);
        if(strlen($result) <= 0)
        {
            $websiteCode = Mage::app()->getWebsite($websiteId)->getCode();
            switch($websiteCode){
                case 'cosb2b':
                            $result = 'CSRTL';
                            break;
                case 'cosb2c':
                            $result = 'CSETL';
                            break;
                case 'pur':
                            $result = 'PCETL';
                            break;
				case 'cosb2bint': 
							$result = 'CSINT';
							break;
            }
        }
        
        return $result;
    }
    
    public function getAvaTaxTerm($customerGroupId)
    {
        $resource = Mage::getSingleton('core/resource');
        $taxTable = $resource->getTableName('tax/tax_class');
        $groupTable = $resource->getTableName('customer/customer_group');
        $readAdapter = $resource->getConnection('core_read');
        
        $query = "SELECT otx.`op_avatax_code` FROM `{$taxTable}` as otx LEFT JOIN `{$groupTable}` as cgt ON `otx`.`class_id`=`cgt`.`tax_class_id` WHERE `cgt`.`customer_group_id`='".$customerGroupId."'";
        $result = $readAdapter->fetchOne($query);
        return $result;
    }
}