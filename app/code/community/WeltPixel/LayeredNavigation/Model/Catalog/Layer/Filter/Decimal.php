<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal {

    protected function _initItems() {
        $data = $this->_getItemsData();

        /* Sort the attributes as it was specified in backend position, name, count */
        Mage::helper('weltpixel_layerednavigation')->orderAttributes($data);

        $items = array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItem(
                    $itemData['label'], $itemData['value'], $itemData['count']
            );
        }

        $this->_items = $items;
        return $this;
    }

    public function getAttributeCode() {
        return $this->getAttributeModel()->getAttributeCode();
    }

}
