<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Renderer_Button extends Varien_Data_Form_Element_Button
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
        $html = '<button class="' . $this->getClass() . '" 
                         style="' . $this->getStyle() . '" 
                         onclick="' .  Mage::helper('core')->htmlEscape($this->getOnclick()) .'" 
                         type="button" 
                         title="' . $this->getTitle() .'">
                         <span><span>';
        $html.= $this->getTitle();
        $html.= '</span></span></button>';
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getLabelHtml($idSuffix = ''){
        if (!is_null($this->getLabel())) {
            $html = '<label for="'.$this->getHtmlId() . $idSuffix . '" style="'.$this->getLabelStyle().'">'.$this->getLabel()
                . ( $this->getRequired() ? ' <span class="required">*</span>' : '' ).'</label>'."\n";
        }
        else {
            $html = '';
        }
        return $html;
    }

}