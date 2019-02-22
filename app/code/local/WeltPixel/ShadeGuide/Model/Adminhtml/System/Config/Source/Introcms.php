<?php

class WeltPixel_ShadeGuide_Model_Adminhtml_System_Config_Source_Introcms
{
    public function toOptionArray()
    {
        $blocks = Mage::getModel('cms/block')
            ->getCollection();

        $blocks->load();
        $blockOptions = array(
            array(
                'label' => '-- Please Select --',
                'value' => ''
            )
        );

        foreach ($blocks->getItems() as $item) {
            $blockOptions[] = array(
                'value' => $item->getIdentifier(),
                'label' => $item->getTitle()
            );
        }

        return $blockOptions;
    }
}
