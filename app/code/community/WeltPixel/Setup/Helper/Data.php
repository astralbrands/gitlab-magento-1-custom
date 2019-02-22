<?php

class WeltPixel_Setup_Helper_Data
    extends Mage_Core_Helper_Data
{

    public function getStoreOptions($options, $allstoreview = false)
    {
        $storeValues = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false,
                $allstoreview);
        return $this->getElementHtml($options, $storeValues);
    }

    protected function getElementHtml($options, $values, $value = null)
    {
        $multipleselect = '';
        if (isset($options['multiple']) && $options['multiple']) {
            $multipleselect = "multiple = 'multiple' ";
        }
        
        $html = '<select '. $multipleselect .' class="select multiselect"  style=" ' . $options['style'] . '" size=" ' . $options['size'] . '" id="' . $options['id'] . '" name="' . $options['name'] . '" ' . '>' . "\n";

        if (!is_array($value)) {
            $value = array($value);
        }

        if ($values) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html.= $this->_optionToHtml(array(
                            'value' => $key,
                            'label' => $option), $value
                    );
                } elseif (is_array($option['value'])) {
                    $html.='<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem) {
                        $html.= $this->_optionToHtml($groupItem, $value);
                    }
                    $html.='</optgroup>' . "\n";
                } else {
                    $html.= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html.= '</select>' . "\n";
        return $html;
    }

    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html = '<optgroup label="' . $option['label'] . '">' . "\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .='</optgroup>' . "\n";
        } else {
            $html = '<option value="' . $this->_escape($option['value']) . '"';
            $html.= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"'
                    : '';
            $html.= isset($option['style']) ? 'style="' . $option['style'] . '"'
                    : '';
            if (in_array($option['value'], $selected)) {
                $html.= ' selected="selected"';
            }
            $html.= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        }
        return $html;
    }

    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

}
