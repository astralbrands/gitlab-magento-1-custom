<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Model_Mysql4_Product_Collection extends  Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    /**
     * Customer mode flag
     *
     * @var bool
     */
    protected $_customerModeFlag = false;

    public function getSelectCountSql()
    {
        if ($this->getIsCustomerMode()) {
            $this->_renderFilters();

            $unionSelect = clone $this->getSelect();

            $unionSelect->reset(Zend_Db_Select::COLUMNS);
            $unionSelect->columns('e.entity_id');

            $unionSelect->reset(Zend_Db_Select::ORDER);
            $unionSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $unionSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

            $countSelect = clone $this->getSelect();
            $countSelect->reset();
            $countSelect->from(array('a' => $unionSelect), 'COUNT(*)');
        } else {
            $countSelect = parent::getSelectCountSql();
        }

        return $countSelect;
    }

    /**
     * Set customer mode flag value
     *
     * @param bool $value
     * @return Mage_Sales_Model_Resource_Order_Grid_Collection
     */
    public function setIsCustomerMode($value)
    {
        $this->_customerModeFlag = (bool)$value;
        return $this;
    }

    /**
     * Get customer mode flag value
     *
     * @return bool
     */
    public function getIsCustomerMode()
    {
        return $this->_customerModeFlag;
    }

    public function applyFilterForAdminNotification()
    {
        $stockAlertTable = Mage::getSingleton('core/resource')->getTableName('productalert/stock');

        $this->addAttributeToSelect('name')
            ->addAttributeToFilter(
                'status',
                array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            );

        $select = $this->getSelect();

        $select->joinRight(
            array('s' => $stockAlertTable),
            's.product_id = e.entity_id',
            array(
                'total_cnt' => 'count(s.product_id)',
                'cnt' => 'COUNT( NULLIF(`s`.`status`, 1) )',
                'last_d' => 'MAX(add_date)',
                'product_id'
            )
        )
            ->where('DATE(add_date) = DATE(NOW())')
            ->group(array('s.product_id'));
            
        return $this;
    }
}
