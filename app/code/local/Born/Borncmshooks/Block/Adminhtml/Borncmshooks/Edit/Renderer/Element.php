<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Renderer_Element extends Varien_Data_Form_Element_Abstract
{
    /**
    * Retrieve Element HTML fragment
    *
    * @return string
    */
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    public function getElementHtml()
    {
            $html = '<div class=\'borncmshooks-delete-element\'>';
            $html .= '<a href="javascript:void(0);" onclick="' .  Mage::helper('core')->htmlEscape($this->getDeleteonclick()) .'" >';
            $html .= $this->getDeleteelement();
            $html .= '</a>';
            $html .= '</div>';
            $html .= '<div class=\'borncmshooks-element\'>';
            $html .= '<a href="javascript:void(0);" onclick="' .  Mage::helper('core')->htmlEscape($this->getOnclick()) .'" >';
            $html .= $this->getElementname();
            $html .= '</a>';
            $html .= '</div>';
        return $html;
    }

    public function getLabelHtml($idSuffix = ''){
        if (!is_null($this->getLabel())) {
            $html = '<div<label for="'.$this->getHtmlId() . $idSuffix . '" style="'.$this->getLabelStyle().'"><span style="width:50px;display: inline-block;">'.$this->getElementorder().'</span>'.$this->getLabel()
                . ( $this->getRequired() ? ' <span class="required">*</span>' : '' ).'</label>'."\n";
        }
        else {
            $html = '';
        }
        return $html;
    }

}