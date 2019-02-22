<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Layer_Filter_Attribute
    extends Mage_Catalog_Model_Layer_Filter_Attribute
{

    protected $_itemsRemain = false;

    protected function _initItems()
    {
        $data = $this->_getItemsData();

        /* Sort the attributes as it was specified in backend position, name, count */
        Mage::helper('weltpixel_layerednavigation')->orderAttributes($data);

        $items = array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItem(
                    $itemData['label'], $itemData['value'], $itemData['count'],
                    null, $itemData['newurloption'], $itemData['checked']
            );
        }

        $this->_items = $items;
        return $this;
    }

    public function apply(Zend_Controller_Request_Abstract $request,
        $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }

        $displayOption = Mage::helper('weltpixel_layerednavigation')->getAttributeDisplay($this->getRequestVar());

        /**
         * if checkbox was set from backend or colorswatch
         */
        if (in_array($displayOption, array(1, 3))) {
            /**
             * explode the params based on _ if multiple checkboxes was enabled
             */
            $options = array();
            if (strlen($filter)) {
                $options = explode('_', $filter);
                $this->_filteredOptions = $options;
            }
            //$options = explode('_', $filter);
            /* used with checkboxes, in _getItemsData */
            //$this->_filteredOptions = $options;
            $optionTexts = array();
            $canProceed = true;
            foreach ($options as $opt) :
                $text = $this->_getOptionText($opt);
                if (!strlen($text)) {
                    $canProceed = false;
                    break;
                }
                $optionTexts[$opt] = $text;
            endforeach;

            //$text = $this->_getOptionText($filter);

            if ($filter && strlen($text)) {
                $this->_getResource()->applyFilterToCollection($this, $options);
                if (is_array($options)) {
                    $this->_itemsRemain = true;
                    foreach ($options as $option) :
                        //resetoption
                        $resetOption = array_diff($options, array($option));
                        $resetOption = implode('_', $resetOption);
                        $this->getLayer()->getState()->addFilter($this->_createItem($optionTexts[$option],
                                $option, 0, $resetOption));
                    endforeach;
                } else {
                    $this->getLayer()->getState()->addFilter($this->_createItem($text,
                            $filter));
                    $this->_items = array();
                }
            }
        } else {
            $text = $this->_getOptionText($filter);
            if ($filter && strlen($text)) {
                $this->_getResource()->applyFilterToCollection($this, $filter);
                $this->getLayer()->getState()->addFilter($this->_createItem($text,
                        $filter));
                $this->_items = array();
            }
        }


        return $this;
    }

    public function shouldItemsRemain()
    {
        return $this->_itemsRemain;
    }

    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey() . '_' . $this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {

            $options = $attribute->getFrontend()->getSelectOptions();

            if ($this->shouldItemsRemain()) {
                $optionsCount = $this->_getResource()->getCount($this,
                        $this->_requestVar);
            } else {
                $optionsCount = $this->_getResource()->getCount($this);
            }

            $data = array();
            foreach ($options as $option) {

                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {

                    /**
                     * for checkboxes
                     */
                    $newUrlOption = '';
                    $checked = false;
                    if ($this->_filteredOptions) :
                        if (in_array($option['value'], $this->_filteredOptions)) {
                            //it means it must be removed
                            $newUrlOption = array_diff($this->_filteredOptions,
                                array($option['value']));
                            $newUrlOption = implode('_', $newUrlOption);
                            $checked = true;
                        } else {
                            $newUrlOption = array_merge($this->_filteredOptions,
                                array($option['value']));
                            $newUrlOption = implode('_', $newUrlOption);
                        }
                    endif;

                    if ((strlen($newUrlOption) == 0 ) && isset($this->_filteredOptions) && count($this->_filteredOptions == 1) && ($option['value'] == $this->_filteredOptions[0])) {
                        $newUrlOption = 'exclude';
                    }

                    /**
                     * for checkboxes
                     */
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']])) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => $optionsCount[$option['value']],
                                'newurloption' => $newUrlOption,
                                'checked' => $checked
                            );
                        }
                    } else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']]
                                    : 0,
                            'newurloption' => $newUrlOption,
                            'checked' => $checked
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  Mage_Catalog_Model_Layer_Filter_Item
     */
    protected function _createItem($label, $value, $count = 0,
        $resetoption = null, $newurloption = null, $checked = null)
    {
        return Mage::getModel('catalog/layer_filter_item')
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count)
            ->setResetoption($resetoption)
            ->setNewurloption($newurloption)
            ->setChecked($checked);
    }

    public function getAttributeCode()
    {
        return $this->getAttributeModel()->getAttributeCode();
    }

}
