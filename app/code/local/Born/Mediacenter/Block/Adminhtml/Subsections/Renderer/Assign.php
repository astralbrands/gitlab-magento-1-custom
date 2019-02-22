<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Assign
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Assign extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        if ($value)
            return $this->__('assigned');
        else
            return $this->__('not assigned');

    }

}

?>