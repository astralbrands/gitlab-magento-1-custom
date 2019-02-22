<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price {

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

        /**
         * 
         */
        $_helper = Mage::helper('weltpixel_layerednavigation');
        $priceDisplayOption = $_helper->getPriceDisplay();
        if (in_array($priceDisplayOption, array(1, 3, 4, 5))) {
            $items[] = $this->_createItem('from_to', 'price', 0);
        }

        $this->_items = $items;
        return $this;
    }

    public function getAttributeCode() {
        return $this->getAttributeModel()->getAttributeCode();
    }

}
