<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Inline
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Inline extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{

    public function render(Varien_Object $row)
    {
        $html = parent::render($row);

        $html .= '<button type="button" onclick="updateName(this, ' . $row->getId() . ');">' . Mage::helper('mediacenter')->__('Update') . '</button>';

        return $html;

    }

}

?>