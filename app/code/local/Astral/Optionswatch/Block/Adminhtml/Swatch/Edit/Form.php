<?php
class Astral_Optionswatch_Block_Adminhtml_Swatch_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct()
    {
        $this->setTemplate('optionswatch/form.phtml');
    }   	
	
	protected function _prepareForm() {

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action' 	=> $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post',
            'enctype'  	 => 'multipart/form-data'
        ));
        
        
        //Never allow an ID change
		$id = Mage::registry('swatch_id');
		if(!!$id){
        	$fieldset = $form->addFieldset('optionswatch', array('legend'=>Mage::helper('optionswatch')->__('Edit Product Option Detail')));
       		$fieldset->addField('id', 'label', array(
	          'label'     => Mage::helper('optionswatch')->__('Swatch ID'),
	          'name'      => 'id',
	        ));
		}
		else{
			$fieldset = $form->addFieldset('optionswatch', array('legend'=>Mage::helper('optionswatch')->__('Add New Option')));
		}
		
		if(!!$id){
	        $fieldset->addField('attribute_id', 'select', array(
	            'label'     => Mage::helper('optionswatch')->__('Attribute'),
	            'name'      => 'attribute_id',
	            'required'  => true,
	            'values'    => Mage::getModel('optionswatch/swatch')->toAttributeArray(),
	            'note'		=> Mage::helper('optionswatch')->__('Please choose your attribute'),
	        	'disabled' => true,
                'tabindex' => 1
	        ));

            $fieldset->addField('option_id', 'select', array(
                'label'     => Mage::helper('optionswatch')->__('Option'),
                'name'      => 'option_id',
                'required'  => true,
                'values'    => Mage::getModel('optionswatch/swatch')->toAllOptionsArray(),
                'note'		=> Mage::helper('optionswatch')->__('Please choose option'),
                'disabled' => true,
                'tabindex' => 2
            ));

            $fieldset->addField('default_option', 'checkbox', array(
                'label'     => Mage::helper('optionswatch')->__('Default'),
                'name'      => 'default_option',
                'checked' => false,
                'onclick' => "",
                'onchange' => "",
                'value'  => '1',
                'disabled' => false,
                'tabindex' => 3
            ));

            $fieldset->addField('sort_order', 'text', array(
	            'label'     => Mage::helper('optionswatch')->__('Sort Order'),
	            'name'      => 'sort_order',
	            'required'  => true,
	            'values'    => '',
                'tabindex' => 4
	        ));                

		}else{
	        $fieldset->addField('attribute_id', 'select', array(
	            'label'     => Mage::helper('optionswatch')->__('Attribute'),
	            'name'      => 'attribute_id',
	            'required'  => true,
	            'values'    => Mage::getModel('optionswatch/swatch')->toAttributeArray(),
	            'note'		=> Mage::helper('optionswatch')->__('Please choose your attribute')
	        ));		
					
	        $fieldset->addField('option_id', 'select', array(
	            'label'     => Mage::helper('optionswatch')->__('Option'),
	            'name'      => 'option_id',
	            'required'  => true,
	            'values'    => array("Please select attribute"),
	            'note'		=> Mage::helper('optionswatch')->__('Please choose option'),
	        ));

            $fieldset->addField('default_option', 'checkbox', array(
                'label'     => Mage::helper('optionswatch')->__('Default Option'),
                'name'      => 'default_option',
                'checked' => false,
                'onclick' => "",
                'onchange' => "",
                'value'  => '1',
                'disabled' => false,
                'tabindex' => 3
            ));

            $fieldset->addField('sort_order', 'text', array(
                'label'     => Mage::helper('optionswatch')->__('Sort Order'),
                'name'      => 'sort_order',
                'required'  => true,
                'values'    => '0',
                'tabindex' => 4
            ));
        }
		//color_code attribute
		$fieldset->addField('color_code', 'text', array(
	            'label'     => Mage::helper('optionswatch')->__('Color Code'),
	            'name'      => 'color_code',
	            'required'  => false,
	            'values'    => ''
	        ));
        
		$fieldset->addField('product_sku', 'text', array(
			'label'     => Mage::helper('optionswatch')->__('Product Sku'),
			'name'      => 'product_sku',
			'required'  => false,
			'values'    => ''
			));

        $fieldset->addField('description', 'textarea', array(
            'label'     => Mage::helper('optionswatch')->__('Description'),
            'name'      => 'description',
            'required'  => false
        ));
        
        $fieldset->addField('image_file', 'image', array(
          'label'     => Mage::helper('optionswatch')->__('Image File'),
          'required'  => true,
          'name'      => 'image_file',
      )); 

      	$fieldset->addField('filter_image_file', 'image', array(
          'label'     => Mage::helper('optionswatch')->__('Filter Image File'),
          'required'  => true,
          'name'      => 'filter_image_file',
      ));   
      
      	$fieldset->addField('cta_link', 'text', array(
          'label'     => Mage::helper('optionswatch')->__('CTA Link'),
          'required'  => false,
          'name'      => 'cta_link',
      ));  
		

        $form->setUseContainer(true);
        $form->addValues(Mage::getModel('optionswatch/swatch')->load($id)->getData());
        
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
}