<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {

    protected $_maxPriceWithoutFilter;
    protected $_minPriceWithoutFilter;

    public function getMaxPriceWithoutFilter() {
        if (is_null($this->_maxPriceWithoutFilter)) {
            $this->_prepareStatisticsDataWithoutFilter();
        }

        return $this->_maxPriceWithoutFilter;
    }

    public function getMinPriceWithoutFilter() {
        if (is_null($this->_minPriceWithoutFilter)) {
            $this->_prepareStatisticsDataWithoutFilter();
        }

        return $this->_minPriceWithoutFilter;
    }

    protected function _prepareStatisticsDataWithoutFilter () {
        $select = clone $this->getSelect();

        $select->reset(Zend_Db_Select::WHERE);

        $priceExpression = $this->getPriceExpression($select) . ' ' . $this->getAdditionalPriceExpression($select);
        $sqlEndPart = ') * ' . $this->getCurrencyRate() . ', 2)';
        $select = $this->_getSelectCountSql($select, false);
        $select->columns(array(
            'max' => 'ROUND(MAX(' . $priceExpression . $sqlEndPart,
            'min' => 'ROUND(MIN(' . $priceExpression . $sqlEndPart,
            'std' => $this->getConnection()->getStandardDeviationSql('ROUND((' . $priceExpression . $sqlEndPart)
        ));
        $select->where($this->getPriceExpression($select) . ' IS NOT NULL');

        $row = $this->getConnection()->fetchRow($select, $this->_bindParams, Zend_Db::FETCH_NUM);
        $this->_pricesCount = (int) $row[0];
        $this->_maxPriceWithoutFilter = (float) $row[1];
        $this->_minPriceWithoutFilter = (float) $row[2];
        $this->_priceStandardDeviation = (float) $row[3];

        return $this;
    }

}
