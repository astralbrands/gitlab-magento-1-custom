<?php

class WeltPixel_Setup_Model_Catalog_Resource_Attribute extends Mage_Catalog_Model_Resource_Attribute {

    protected function _saveOption(Mage_Core_Model_Abstract $object) {
        $option = $object->getOption();

        $swatchOnLayeredEnabled = Mage::helper('weltpixel_productswatch')->isColorSwatchEnabledOnLayeredNavigation();
        $swatchOnProductEnabled = Mage::helper('weltpixel_layerednavigation')->isColorSwatchEnabledOnProduct();

        if (is_array($option)) {
            $adapter = $this->_getWriteAdapter();
            $optionTable = $this->getTable('eav/attribute_option');
            $optionValueTable = $this->getTable('eav/attribute_option_value');

            $stores = Mage::app()->getStores(true);
            if (isset($option['value'])) {
                $attributeDefaultValue = array();
                if (!is_array($object->getDefault())) {
                    $object->setDefault(array());
                }

                foreach ($option['value'] as $optionId => $values) {
                    $intOptionId = (int) $optionId;
                    if (!empty($option['delete'][$optionId])) {
                        if ($intOptionId) {
                            $adapter->delete($optionTable, array('option_id = ?' => $intOptionId));
                            if ($swatchOnProductEnabled) :
                                Mage::helper('weltpixel_productswatch/image')->clearCache($object->getId(), $intOptionId);
                            endif;
                            if ($swatchOnLayeredEnabled):
                                Mage::helper('weltpixel_layerednavigation/image')->clearCache($object->getId(), $intOptionId);
                            endif;
                        }
                        continue;
                    }

                    $sortOrder = !empty($option['order'][$optionId]) ? $option['order'][$optionId] : 0;

                    /**
                     * Customization - adding image options
                     */
                    $navigationImage = !empty($option['navigation_image'][$optionId]) ? $option['navigation_image'][$optionId] : '';
                    $productImage = !empty($option['product_image'][$optionId]) ? $option['product_image'][$optionId] : '';
                    if (!$intOptionId) {
                        $data = array(
                            'attribute_id' => $object->getId(),
                            'sort_order' => $sortOrder,
                        );
                        if ($swatchOnProductEnabled) :
                            $data['product_image'] = $productImage;
                        endif;
                        if ($swatchOnLayeredEnabled) :
                            $data['navigation_image'] = $navigationImage;
                        endif;

                        $adapter->insert($optionTable, $data);
                        $intOptionId = $adapter->lastInsertId($optionTable);
                        if ($swatchOnProductEnabled) :
                            Mage::helper('weltpixel_productswatch/image')->clearCache($object->getId(), $intOptionId);
                        endif;
                        if ($swatchOnLayeredEnabled):
                            Mage::helper('weltpixel_layerednavigation/image')->clearCache($object->getId(), $intOptionId);
                        endif;                        
                    } else {
                        $data = array(
                            'sort_order' => $sortOrder,
                        );

                        if ($swatchOnProductEnabled) :
                            $data['product_image'] = $productImage;
                        endif;
                        if ($swatchOnLayeredEnabled) :
                            $data['navigation_image'] = $navigationImage;
                        endif;

                        $where = array('option_id =?' => $intOptionId);
                        $wasThereUpdate = $adapter->update($optionTable, $data, $where);
                        if ($wasThereUpdate) :
                            ///delete swatch images
                            if ($swatchOnProductEnabled) :
                                Mage::helper('weltpixel_productswatch/image')->clearCache($object->getId(), $intOptionId);
                            endif;
                            if ($swatchOnLayeredEnabled):
                                Mage::helper('weltpixel_layerednavigation/image')->clearCache($object->getId(), $intOptionId);
                            endif;
                        endif;
                    }

                    /**
                     * End Of Customization - adding image options
                     */
                    if (in_array($optionId, $object->getDefault())) {
                        if ($object->getFrontendInput() == 'multiselect') {
                            $attributeDefaultValue[] = $intOptionId;
                        } elseif ($object->getFrontendInput() == 'select') {
                            $attributeDefaultValue = array($intOptionId);
                        }
                    }

                    // Default value
                    if (!isset($values[0])) {
                        Mage::throwException(Mage::helper('eav')->__('Default option value is not defined'));
                    }

                    $adapter->delete($optionValueTable, array('option_id =?' => $intOptionId));
                    foreach ($stores as $store) {
                        if (isset($values[$store->getId()])
                                && (!empty($values[$store->getId()])
                                || $values[$store->getId()] == "0")
                        ) {
                            $data = array(
                                'option_id' => $intOptionId,
                                'store_id' => $store->getId(),
                                'value' => $values[$store->getId()],
                            );
                            $adapter->insert($optionValueTable, $data);
                        }
                    }
                }
                $bind = array('default_value' => implode(',', $attributeDefaultValue));
                $where = array('attribute_id =?' => $object->getId());
                $adapter->update($this->getMainTable(), $bind, $where);
            }
        }

        return $this;
    }

}
