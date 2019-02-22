<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 */

/**
 * Class DynamicYield_Integration_Block_Adminhtml_Config_Updaterate
 */
class DynamicYield_Integration_Block_Adminhtml_Config_Updaterate extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $types = (array)$element->getValues();

        uasort($types, function ($a, $b) {
            if ($a['value'] == $b['value']) {
                return 0;
            }
            return ($a['value'] < $b['value']) ? -1 : 1;
        });

        $types = array_reverse($types);
        $element->addClass('select');

        $value = explode(',', $element->getValue());
        $minutes = NULL;
        $multiplier = NULL;

        if (sizeof($value) == 2) {
            list($minutes, $multiplier) = $value;
        } else {
            $value = $value[0];


            foreach ($types as $row) {
                $minutes = $value / $row['value'];

                if ($minutes >= 1) {
                    $multiplier = $row['value'];
                    break;
                }
            }
        }

        $element->setValue($multiplier);

        $name = $element->getName();

        $style = $element->getData('style');

        $element->setData('name', substr($name, -2) == '[]' ? $name : $name . '[]');
        $element->setData('style', ($style ? $style . ';' : '') . 'width: 100px;');

        $html = '<input type="text" name="' . $element->getName() . '" id="' . $element->getHtmlId() . '" value="' . $minutes . '" />';

        $html .= "&nbsp;&nbsp;" . $element->getElementHtml();


        $html .= $element->getAfterElementHtml();
        return $html;
    }
}
