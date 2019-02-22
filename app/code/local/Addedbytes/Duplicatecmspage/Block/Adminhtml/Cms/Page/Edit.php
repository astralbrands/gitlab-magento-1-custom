<?php
class Addedbytes_Duplicatecmspage_Block_Adminhtml_Cms_Page_Edit extends Mage_Adminhtml_Block_Cms_Page_Edit
{
    public function __construct()
    {
        parent::__construct();

        // Create a button labelled Duplicate of which when clicked will call our action.
        $this->_addButton(
            'duplicate',
            array(
                'label' => Mage::helper('adminhtml')->__('Duplicate Page'),
                'onclick' => 'window.location = \''.$this->_getDuplicatePageUrl().'\'',
                'class' => 'add',
            ),
            -100
        );
    }

    protected function _getDuplicatePageUrl()
    {
        return $this->getUrl(
            '*/*/duplicate',
            array(
                '_current' => true,
                'back' => 'edit',
                'active_tab' => '{{tab_id}}'
            )
        );
    }
}
