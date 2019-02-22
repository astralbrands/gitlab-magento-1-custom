<?php
class Born_BornIntegration_Block_Adminhtml_System_Config_Statistical extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_websiteCollection = null;
    protected $_statisticalGroups = array();
    protected $_helper = null;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('bornintegration/config/form/field/array.phtml');
        $this->_helper = Mage::helper('bornintegration');
        $this->_websiteCollection = Mage::getModel('core/website')->getCollection()->toOptionHash();
        $this->_statisticalGroups = array(
            'ALCO' => $this->_helper->__('Aloette Corp Franchise'),
            'ALFRC' => $this->_helper->__('Aloette Franchise Canda'),
            'ALFRU' => $this->_helper->__('Aloette Franchise US'),
            'ASTRL' => $this->_helper->__('Astral Employee G&A'),
            'CORP' => $this->_helper->__('Corporate'),
            'CSDST' => $this->_helper->__('Cosmedix Distributor'),
            'CSETL' => $this->_helper->__('Cosmedix Etail'),
            'CSRTL' => $this->_helper->__('Cosmedix Retail'),
            'CSTV' => $this->_helper->__('Cosmedix Retail'),
            'CSWEB' => $this->_helper->__('Cosmedix Web'),
            'MRKT' => $this->_helper->__('Marketing'),
            'PCETL' => $this->_helper->__('Pur Cosmetics Etail'),
            'PCRTL' => $this->_helper->__('Pur Cosmetics Retail'),
            'PCRUK' => $this->_helper->__('Pur Cosmetics Retail UK'),
            'PCTV' => $this->_helper->__('Pur Cosmetics TV'),
            'PCWEB' => $this->_helper->__('Pur Cosmetics Web'),
            'PRODV' => $this->_helper->__('Product Development')
        );
    }
    
    public function _prepareToRender() {
        $this->addColumn('website_id', array(
            'label' => Mage::helper('bornintegration')->__('Website'),
            'style' => 'width:100px',
        ));
        $this->addColumn('statistical_groups', array(
            'label' => Mage::helper('bornintegration')->__('Statistical Groups'),
            'style' => 'width:100px',
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('bornintegration')->__('Add');
    }
    
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
                ->toHtml();
        }
        $loopArg = ($columnName == 'website_id') ? $this->_websiteCollection: $this->_statisticalGroups;
        $elementTemplate = '<select name="'.$inputName.'" class="'.(isset($column['class']) ? $column['class'] : 'select').'"  style="'.(isset($column['style']) ? ' style="'.$column['style'] . '"' : '') .'">';
            foreach($loopArg as $id=>$label){
                $elementTemplate .= '<option value="'.$id.'">'.$label.'</option>';
            }
        $elementTemplate .= '</select>';
        return $elementTemplate;
    }
}

