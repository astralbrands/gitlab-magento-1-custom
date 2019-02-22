<?php
class Born_Borncmshooks_Block_Adminhtml_Borncmshooks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_borncmshooks';
    $this->_blockGroup = 'borncmshooks';
    $this->_headerText = Mage::helper('borncmshooks')->__('Hook Manager');
    $this->_addButtonLabel = Mage::helper('borncmshooks')->__('Hook a Page');

      parent::__construct();
  }
}