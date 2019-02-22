<?php
class Born_Sidecar_Model_System_Config_Source_Options
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
		
            array('value' => 1, 'label'=>Mage::helper('sidecar')->__('Sku')),
            array('value' => 2, 'label'=>Mage::helper('sidecar')->__('Product Id')),
        );
    }

}
