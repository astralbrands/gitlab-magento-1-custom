<?php
class Born_Package_Model_Attribute_Source_Ordertype
extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    
    public function getAllOptions()
    {		
      $options = array(
          array(
            'value'=>'',
            'label'=>''
            ),
        array(
            'value'=>'Web',
            'label'=>'Web'
            ),
        array(
            'value'=>'Call In',
            'label'=>'Call In'
            ),
        array(  
            'value'=>'Email',
            'label'=>'Email'
            ),
        array(
            'value'=>'Reship',
            'label'=>'Reship'
            ),
        array(
            'value'=>'Samples',
            'label'=>'Samples'
            ),
        array(
            'value'=>'AE01',
            'label'=>'AE01'
            )
        );

      return $options;
  }
}
