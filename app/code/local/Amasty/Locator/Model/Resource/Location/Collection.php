<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Model_Resource_Location_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('amlocator/location');
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['product'] = 'product_table.product_id';
    }

    public function addStoreFilter($store, $withAdmin = true)
    {

        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }
        $this->getSelect()->join(
            array('store_table' => $this->getTable(
                'amlocator/table_location_store'
            )),
            'main_table.id = store_table.location_id',
            array()
        )
            ->where(
                'store_table.store_id in (?)',
                ($withAdmin ? array(0, $store) : $store)
            )
            ->group('main_table.id');

        return $this;
    }

    /**
     * @param string $productId
     * @param string $categoryId
     *
     * @return $this
     */
    public function addProductCategoryFilter($productId = "NULL", $categoryId = "NULL")
    {
        $this->getSelect()->joinLeft(
            array('product_table' => $this->getTable(
                'amlocator/table_location_product'
            )),
            'main_table.id = product_table.location_id',
            array('product_id')
        );

        $select = $this->getSelect()->joinLeft(
            array('category_table' => $this->getTable(
                'amlocator/table_location_category'
            )),
            'main_table.id = category_table.location_id',
            array('category_id')
        );

        if ($productId) {
            $select->where(
                '( product_table.product_id in (?)',
                $productId
            );

            $select->orwhere(
                'category_table.category_id in (?) )',
                $categoryId
            );
        } else {
            $select->where(
                'product_table.product_id is NULL'
            );
            $select->where(
                'category_table.category_id is NULL'
            );
        }
        $select->group("main_table.id");

        return $this;
    }

    public function applyDefaultFilters()
    {
        $select = $this->getSelect();
        if (!Mage::app()->isSingleStoreMode()) {
            $store = array(0, Mage::app()->getStore()->getId());
            $this->addFieldToFilter(
                'store_table.store_id', array('in' => $store)
            );
            $select->join(
                array('store_table' => $this->getTable(
                    'amlocator/table_location_store'
                )),
                'main_table.id = store_table.location_id'
            );
        }

        $select->where('main_table.status = 1');

        $defWrite = Mage::getSingleton('core/resource')->getConnection(
            'default_write'
        );

        $lat = (float)Mage::app()->getRequest()->getPost('lat');
        $lng = (float)Mage::app()->getRequest()->getPost('lng');
        $sort = $defWrite->quote(Mage::app()->getRequest()->getPost('sort'));
        $radius = (float)Mage::app()->getRequest()->getPost('radius');

        $ip = $this->_getIp();
        if ((Mage::getStoreConfig('amlocator/geoip/use') == 1)
            && ($sort == "'distance'")
            && (!$lat)
        ) {
            $geoIpModel = Mage::getModel('amgeoip/geolocation');
            $geodata = $geoIpModel->locate($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();
        }

        if ($lat && $lng) {
            $select->columns(
                array('distance' => 'SQRT(
                POW(69.1 * (main_table.lat - ' . $lat . '), 2) +
                POW(69.1 * (' . $lng
                    . ' - main_table.lng) * COS(main_table.lat / 57.3), 2))')
            );
            $select->order("distance");
        } else {
            $select->order('main_table.position ASC');
        }

        if ($radius) {
            switch (Mage::getStoreConfig('amlocator/locator/distance')) {
                case "km":
                    $radius = $radius / 1.609344;
                    break;
                case "choose":

                    break;
            }
            $select->having('distance < ' . $radius);
        }
    }

    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);
        if (Mage::getDesign()->getArea() != 'adminhtml') {
            $this->_totalRecords = sizeof($this->_items);
        }

        return $this;
    }

    private function _getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

}