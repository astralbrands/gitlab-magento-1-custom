<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Model_Resource_Location
    extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('amlocator/table_location', 'id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {

        $state = $object->getStateId();

        if (is_numeric($state)) {
            $object->setState(
                Mage::getModel('directory/region')->load($object->getStateId())
                    ->getName()
            );
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        //save image
        $helperImage = Mage::helper('amlocator/image');
        $id = $object->getId();
        $photo = $helperImage->saveAction("photo", $id, $object->getPhoto());
        $marker = $helperImage->saveAction("marker", $id, $object->getMarker());
        $condition = $this->_getWriteAdapter()->quoteInto(
            'id = ?', $object->getId()
        );
        $this->_getWriteAdapter()->update(
            $this->getTable('amlocator/table_location'),
            array("photo" => "$photo"), $condition
        );
        $this->_getWriteAdapter()->update(
            $this->getTable('amlocator/table_location'),
            array("marker" => $marker), $condition
        );
        //save stores
        $this->_saveToLinkedTable($object, 'store', $id);

        $product_ids = $object->getSelectedProducts();
        if (!is_null($product_ids)) {
            $object->setProductId(
                Mage::helper('adminhtml/js')
                    ->decodeGridSerializedInput($product_ids)
            );
        }
        $this->_saveToLinkedTable($object, 'product', $id);


        $categories_id = $object->getCategories();
        if (!is_null($categories_id)) {
            $object->setCategoryId(explode(',', $categories_id));
        }
        $this->_saveToLinkedTable($object, 'category', $id);

        return parent::_afterSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData(
            'store_id',
            $this->_loadFromLinkedTable('store', $object->getId())
        );

        $object->setData(
            'product_id',
            $this->_loadFromLinkedTable('product', $object->getId())
        );

        $object->setData(
            'category_id',
            $this->_loadFromLinkedTable('category', $object->getId())
        );

        return parent::_afterLoad($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     *
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $helper = Mage::helper('amlocator/image');
        $locationtable = $this->getTable('amlocator/table_location');

        $row = $this->_getReadAdapter()->fetchRow(
            $this->_getReadAdapter()->select()
                ->from($locationtable)
                ->where("{$this->getIdFieldName()} = ?", $object->getData('id'))
        );

        $helper->deleteImage($row['photo']);

        $this->_deleteFromLinkedTable("store", $object->getData('id'));
        $this->_deleteFromLinkedTable("product", $object->getData('id'));
        $this->_deleteFromLinkedTable("category", $object->getData('id'));
    }

    /**
     * @param $name
     *
     * @return array
     */
    private function _loadFromLinkedTable($name, $id)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('amlocator/table_location_' . $name))
            ->where('location_id = ?', $id);
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $dataArray = array();
            foreach ($data as $row) {
                $dataArray[] = $row[$name . '_id'];
            }
            return $dataArray;
        }
    }

    private function _deleteFromLinkedTable($name, $id)
    {
        $table = $this->getTable('amlocator/table_location_' . $name);
        $this->_getWriteAdapter()->delete(
            $table, array(
                $this->_getWriteAdapter()->quoteInto(
                    'location_id = ?', $id
                )
            )
        );
    }

    private function _saveToLinkedTable($object, $name, $id)
    {
        if ($object->getData($name . '_id')) {
            $condition = $this->_getWriteAdapter()->quoteInto(
                'location_id = ?', $object->getId()
            );
            $this->_getWriteAdapter()->delete(
                $this->getTable('amlocator/table_location_' . $name), $condition
            );
            if (in_array(0, (array)$object->getData($name . '_id'))
                && $name == 'store'
            ) {
                $object->setData($name . '_id', array(0));
            }


            if ($name == 'product' && $object->getProductAccessMode() == 0) {
                return true;
            }
            if ($name == 'category' && $object->getCategoryAccessMode() == 0) {
                return true;
            }

            foreach ((array)$object->getData($name . '_id') as $data) {
                $dataArray = array();
                $dataArray['location_id'] = $id;
                $dataArray[$name . '_id'] = $data;
                $this->_getWriteAdapter()->insert(
                    $this->getTable('amlocator/table_location_' . $name),
                    $dataArray
                );
            }
        }
    }

    public function issetLocation($productId, $categoryId)
    {
        $read = $this->_getReadAdapter();
        $location = $this->getTable('amlocator/table_location');
        $product = $this->getTable('amlocator/table_location_product');
        $category = $this->getTable('amlocator/table_location_category');

        $select = "SELECT count(*) FROM " . $location . " as loc LEFT JOIN "
            . $product . " as prod ON loc.id=prod.location_id LEFT JOIN "
            . $category
            . " as cat ON loc.id=cat.location_id WHERE prod.product_id IN ("
            . $productId . ") OR cat.category_id  IN (".$categoryId.") ";
        return $read->fetchCol($select);
    }
}