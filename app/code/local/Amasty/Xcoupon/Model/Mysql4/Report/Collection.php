<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xcoupon
 */
class Amasty_Xcoupon_Model_Mysql4_Report_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{
    public function addFields()
    {
        $select = $this->getSelect();
        
        if (empty($this->_map))
            $this->_map = array();
            
        if (version_compare(Mage::getVersion(), '1.4.1.0') < 0){
            $this->_map['fields']['created_at'] = 'e.created_at'; 
            $this->addAttributeToSelect('*');
            
            $couponCondition = array('neq'=>'');
            $couponBind = 'e.coupon_code';
            
            if (version_compare(Mage::getVersion(), '1.4') < 0){
                $couponCondition = array('notnull'=>true);  
                $couponBind      = '_table_coupon_code.value';
            }
            
            $this->addAttributeToFilter('coupon_code', $couponCondition); 
            
            $select->joinLeft(array('r' => $this->getTable('salesrule/rule')), 
                $couponBind . ' = r.coupon_code', 
                array('rule_name'=> 'r.name'))
            ;
            $this->_joinFields['rule_name'] = array(
                'table'=> 'r',
                'field'=>'name',
            );
            
            // join tracking number
            $entity = Mage::getModel('eav/entity')->setType('shipment_track');
            $orderAttr = $entity->getAttribute('order_id');
            $numberAttr = $entity->getAttribute('number');
            
            $select->joinLeft(array('o' => $this->getTable('sales_order_entity_int')), 
                'e.entity_id = o.value AND o.attribute_id = '. $orderAttr->getId(), 
                array())
            ;

            $select->joinLeft(array('t' => $this->getTable('sales_order_entity_text')), 
                'o.entity_id = t.entity_id AND t.attribute_id = '. $numberAttr->getId(), 
                array('tracking'=> new Zend_Db_Expr('GROUP_CONCAT(t.value SEPARATOR ",")')))
            ;
            
            $this->_joinFields['tracking'] = array(
                'table'=> 't',
                'field'=> 'value',
            );
            
            $select->group('e.entity_id');            
        }
        // modern versions
        else {
            $this->_map['fields']['created_at'] = 'main_table.created_at'; 
            $field = $this->_getTrackFieldName();

            $this->addFieldToFilter('coupon_code', array('neq'=>''));
            $select->joinLeft(array('t'=>$this->getTable('sales/shipment_track')),
                't.order_id = main_table.entity_id', array('tracking'=> new Zend_Db_Expr('GROUP_CONCAT('.$field.' SEPARATOR ",")')))
                ->group('main_table.entity_id')
            ;             
        } 
        
        return $this; 
    }
    
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);         

        return $countSelect;
    }

    protected function _getTrackFieldName()
    {
        $field = 'track_number';

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('sales/shipment_track');
        $tableFields = $readConnection->describeTable($tableName);

        foreach ($tableFields as $key => $value) {
            if ($key == 'number') {
                $field = $key;
                break;
            }
        }

        return $field;
    }
}
