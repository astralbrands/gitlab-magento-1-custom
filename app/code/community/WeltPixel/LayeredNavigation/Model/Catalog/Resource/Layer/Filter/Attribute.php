<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Resource_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute {

    public function applyFilterToCollection($filter, $value) {

                
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';

        if (is_array($value)) {
            $conditions = array(
                "{$tableAlias}.entity_id = e.entity_id",
                $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
                "{$tableAlias}.value in (" . implode(',', $value) . ")"
            );
        } else {
            $conditions = array(
                "{$tableAlias}.entity_id = e.entity_id",
                $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
                $connection->quoteInto("{$tableAlias}.value = ?", $value)
            );
        }

        $collection->getSelect()->join(
                array($tableAlias => $this->getMainTable()), implode(' AND ', $conditions), array()
        );
        
        $collection->getSelect()->distinct();

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter, $requestVar = null) {

        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);



        $connection = $this->_getReadAdapter();
        $attribute = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idxcount', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        );


        if (isset($requestVar)) :
            $options = $select->getPart('from');
            $select->reset(Zend_Db_Select::FROM);

            unset($options[$requestVar.'_idx']);
            $select->forFrom($options);
        endif;


        $select
                ->join(
                        array($tableAlias => $this->getMainTable()), join(' AND ', $conditions), array('value', 'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")))
                ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }

}
