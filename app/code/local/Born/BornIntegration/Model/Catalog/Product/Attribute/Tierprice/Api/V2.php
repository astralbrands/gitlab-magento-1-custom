<?php
class Born_BornIntegration_Model_Catalog_Product_Attribute_Tierprice_Api_V2 extends Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2
{
    /**
     *  Prepare tier prices for save
     *
     *  @param      Mage_Catalog_Model_Product $product
     *  @param      array $tierPrices
     *  @return     array
     */
    public function prepareTierPrices($product, $tierPrices = null)
    {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Catalog_Product_Attribute_Tierprice_Api_V2_'.date('Ymd').'.log');
        if (!is_array($tierPrices)) {
            return null;
        }

        $updateValue = array();
        
        $resource = Mage::getSingleton('core/resource');
        $groupTable = $resource->getTableName('customer/customer_group');
        $readAdapter = $resource->getConnection('core_read');
        
        foreach ($tierPrices as $tierPrice) {
            if (!is_object($tierPrice)
                || !isset($tierPrice->qty)
                || !isset($tierPrice->price)) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid Tier Prices'));
            }

            if (!isset($tierPrice->website) || $tierPrice->website == 'all') {
                $tierPrice->website = 0;
            } else {
                try {
                    $tierPrice->website = Mage::app()->getWebsite($tierPrice->website)->getId();
                } catch (Mage_Core_Exception $e) {
                    $tierPrice->website = 0;
                }
            }

            if (intval($tierPrice->website) > 0 && !in_array($tierPrice->website, $product->getWebsiteIds())) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid tier prices. The product is not associated to the requested website.'));
            }

            if (!isset($tierPrice->customer_group_id)) {
                $tierPrice->customer_group_id = 'all';
            }
            
            if(!is_numeric($tierPrice->customer_group_id) && $tierPrice->customer_group_id != 'all'){
                try{
                    $query = "SELECT `customer_group_id` FROM `{$groupTable}` WHERE `sage_code`='".$tierPrice->customer_group_id."'";
                    $groupId = $readAdapter->fetchOne($query);
                    if(!is_numeric($groupId)){
                        $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid tier prices. The Sage group code "'.$tierPrice->customer_group_id.'" is invalid for customer.'));
                    }else{
                        $tierPrice->customer_group_id = $groupId;
                    }
                }catch(Exception $e){
                    $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid tier prices.'));
                }
            }

            if ($tierPrice->customer_group_id == 'all') {
                $tierPrice->customer_group_id = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
            
            $updateValue[] = array(
                'website_id' => $tierPrice->website,
                'cust_group' => $tierPrice->customer_group_id,
                'price_qty'  => $tierPrice->qty,
                'price'      => $tierPrice->price
            );

        }
        return $updateValue;
    }
}

