<?php

class WeltPixel_Newsletter_Model_Adminhtml_System_Config_Source_Display_Block {

    /**
     * Return all static block for the popup content
     * Filter might be added, to return just active ones, or something else,
     * blocks starting with weltpix_namespace, etc 
     */
    public function toOptionArray() {
        $blocks = Mage::getModel('cms/block')
                ->getCollection();

        $blocks->load();
        $blockOptions = array();

        foreach ($blocks->getItems() as $item) :
            $blockOptions[] = array(
                'value' => $item->getIdentifier(),
                'label' => $item->getTitle()
            );
        endforeach;

        return $blockOptions;
    }

}
