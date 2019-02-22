<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Multiselect
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
    {
        $name = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
        $html = '<select style="width:150px;" size="10" multiple name="' . $this->escapeHtml($name) . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = explode(',',$row->getData($this->getColumn()->getIndex()));
        foreach ($this->getColumn()->getValues() as $val){
            $selected = ( (in_array($val['value'],$value) && (!is_null($value))) ? ' selected="selected"' : '' );
            $html .= '<option value="' . $this->escapeHtml($val['value']) . '"' . $selected . '>';
            $html .= $this->escapeHtml($val['label']) . '</option>';
        }
        $html.='</select>';
		$html .= '<button type="button" onclick="updateGroup(this, ' . $row->getId() . ');">' . Mage::helper('mediacenter')->__('Update') . '</button>';
        return $html;
    }

}

?>