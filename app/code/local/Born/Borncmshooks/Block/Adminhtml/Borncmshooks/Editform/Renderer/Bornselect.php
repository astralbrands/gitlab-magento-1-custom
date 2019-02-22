<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Editform_Renderer_Bornselect extends Varien_Data_Form_Element_Select
{
   
    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html ='<optgroup label="'.$option['label'].'">'."\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .='</optgroup>'."\n";
        }
        else {
            $html = '<option data-section="' .$this->_escape($option['section']). '" data-field="' .$this->_escape($option['field']). '" value="'.$this->_escape($option['value']).'"';
            $html.= isset($option['title']) ? 'title="'.$this->_escape($option['title']).'"' : '';
            $html.= isset($option['style']) ? 'style="'.$option['style'].'"' : '';
            if (in_array($option['value'], $selected)) {
                $html.= ' selected="selected"';
            }
            $html.= '>'.$this->_escape($option['label']). '</option>'."\n";
        }
        return $html;
    }
}
