<?php

class WeltPixel_Custom_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    public function setDefaultOrder($field)
    {
        if (isset($this->_availableOrder[$field])) {

            $this->_availableOrder = array(
                'name' => $this->__('Name'),
                'price' => $this->__('Price'),
                'position' => $this->__('Best Match'),
                'entity_id' => $this->__('Entity Id'),
                'rating_summary' => Mage::helper('catalog')->__('Rating'),
                'created_at' => Mage::helper('catalog')->__('Created At')
            );

            $this->_orderField = $field;
            if ($field == 'created_at') {
                $this->_direction = 'desc';
            }
        }
        return $this;
    }

}
