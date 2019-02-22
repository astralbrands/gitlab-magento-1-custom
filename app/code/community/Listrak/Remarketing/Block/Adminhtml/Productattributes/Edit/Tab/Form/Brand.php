<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.5
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2013 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tab_Form_Brand
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tab_Form_Brand
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'productattribute_form',
            array('legend' => Mage::helper('remarketing')->__('Field information'))
        );

        $attributeCodes = array();
        $attributeCodes[''] = '- No Brand Attribute -';
        foreach (Mage::registry('productattribute_options') as $key => $value) {
            $attributeCodes[$key] = $value;
        }

        $fieldset->addField(
            'brand_attribute',
            'select',
            array(
                'label' => Mage::helper('remarketing')->__('Brand Attribute'),
                'name' => 'brand_attribute',
                'values' => $attributeCodes,
                'value' => Mage::registry('productattribute_data')
                    ->getBrandAttributeCode()
            )
        );

        return parent::_prepareForm();
    }
    
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        
        return $helper->__('Brands');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}

