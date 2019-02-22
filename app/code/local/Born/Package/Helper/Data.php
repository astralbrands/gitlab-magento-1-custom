<?php

/* 
 * @category   Customization
 * @package    Born Commerce
 *
 */

class Born_Package_Helper_Data extends Mage_Core_Helper_Abstract
{
    //test function to check the helper initialisation
    public function getTest()
    {
        return "If you can see this the module is working";
    }

    public function getStoreIdByCurrentUrl()
    {
        Mage::app();
        $currentDomain = Mage::helper('core/url')->getCurrentUrl();
        // $currentScheme = parse_url($currentDomain, PHP_URL_SCHEME);
        $currentDomain = parse_url($currentDomain, PHP_URL_HOST);

        if (strpos($currentDomain,'.coms')) {
            $currentDomain = chop($currentDomain,'.com');
        }

        if (strpos($currentDomain,'cosmedix') !== false) {
            return 2;
        }

        if (strpos($currentDomain,'purcosmetics')) {
            return 1;
        }
        return 1;
    }
    
    public function enableNewVersionCheck(){
        return Mage::getStoreConfig('cataloginventory/new_version_check/enable');
    }
    public function newVersionQtyThreshold(){
        $qty=Mage::getStoreConfig('cataloginventory/new_version_check/disable_limit');
        if($qty === NULL){
            return 0;
        }
        return $qty;
    }

    public function getPixelezeAccountId()
    {
        $_path = 'born_affiliate/pixeleze_setting/account_id';
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_accountId = Mage::getStoreConfig($_path, $_storeId);

        return $_accountId;
    }

    public function getPixelezeEnable()
    {
        $_path = 'born_affiliate/pixeleze_setting/enable';
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_enable = Mage::getStoreConfig($_path, $_storeId);

        if ($_enable) {
            return true;
        }

        return false;
    }
    public function getPixelezeProductLevel()
    {
        $_path = 'born_affiliate/pixeleze_setting/enable_product_level';
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_enable = Mage::getStoreConfig($_path, $_storeId);

        if ($_enable) {
            return true;
        }

        return false;
    }

    public function isPOBoxAddress($address) {
        //Check if PO box
        $pobox_regex = array(
            '/ p\.* *o\.* */i',
            '/ box\.* */i',
            '/\b((?:P(?:OST)?.?\s*(?:O(?:FF(?:ICE)?)?)?.?\s*(?:B(?:IN|OX)?)+)+|(?:B(?:IN|OX)+‌​\s+)+)\s*\w+/i'
        );

        foreach ($pobox_regex as $exp) {
            if (preg_match($exp, $address)) {
                return true;
            }
        }

        return false;
    }

    public function isAPOAddress($address) {
        #check If APO
        $apo_regex = array("/A\W?P\W?O\W?/i", #covers APO, A.P.O., A.P.O, A-P-O etc
            "/F\W?P\W?O\W?/i", #covers FPO, F.P.O., F.P.O, F-P-O etc
            "/Fleet\s*Post\s*Office/i",
            "/Fleet\s*P\W?O\W?\s*Army/i",
        );

        foreach ($apo_regex as $exp) {
            if (preg_match($exp, $address)) {
                return true;
            }
        }

        return false;
    }
	
	Public Function isB2BCustomer($group_id = NULL){
			if(!$group_id){
				$group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
			}
			$b2b_group=Mage::getStoreConfig('customer/b2b_settings/customer_group');
			////Mage::log('backend val'.$b2b_group,7,'group.log');
			if($b2b_group == $group_id){
				return 1;
			}
		return 0;	
	}

    public function getYoutubeId($url)
    {
        if ($url && strpos($url, 'youtu')) {
            if (strpos($url, 'watch')) {
                $_data = array();
                parse_str(parse_url($url, PHP_URL_QUERY),$_data);
                if ($_data['v']) {
                    return $_data['v'];
                }

            }else{
                parse_str(parse_url($url, PHP_URL_PATH),$_data);
                if (is_array($_data)) {
                    $_data = key($_data);
                    $_data = str_replace('/','',$_data);
                    return $_data;
                }
            }
        }
        return;
    }

    public function getImageUrl($product, $imageCode)
    {
        $imgSrc = null;
        try{
          $imgSrc = Mage::helper('catalog/image')->init($product, $imageCode);
        }
        catch(Exception $e) {
          $imgSrc = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg',array('_area'=>'frontend'));  
        }

        return $imgSrc;
    }

    public function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if (!isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}