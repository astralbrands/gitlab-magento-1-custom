<?php

// Controllers are not autoloaded so we will have to do it manually:
require_once('Mage/Adminhtml/controllers/Cms/BlockController.php');

class Addedbytes_Duplicatecmspage_Adminhtml_Cms_BlockController extends Mage_Adminhtml_Cms_BlockController
{
    public function duplicateAction()
    {
        // Load block being duplicated by block_id param
        $params = $this->getRequest()->getParams();
        $cmsBlock = Mage::getModel('cms/block')->load($params['block_id']);
        if ($cmsBlock) {

            // Create new block
            $duplicateBlock = Mage::getModel('cms/block');

            // Now we need to get the existing block data to populate new object
            $cmsBlockData = $cmsBlock->getData();

            // We don't want to set the block ID, otherwise we're just updating
            // the original block.
            unset($cmsBlockData['block_id']);

            // Update title and identifier to make them unique. Trim any
            // existing duplication info first.
            $cmsBlockData['title'] = preg_replace('~( - Duplicate \([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\))+$~i', '', $cmsBlockData['title']);
            $cmsBlockData['identifier'] = preg_replace('~(-duplicate-[0-9]{14})+$~i', '', $cmsBlockData['identifier']);
            $cmsBlockData['title'] = $cmsBlockData['title'] . ' - Duplicate (' . date('Y-m-d H:i:s') . ')';
            $cmsBlockData['identifier'] = $cmsBlockData['identifier'] . '-duplicate-' . date('YmdHis');

            // Set data and save
            $duplicateBlock->setData($cmsBlockData)->save();

            // Feedback
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('The block has been duplicated.'));

            // Redirect to new block
            $this->_redirect(
                '*/*/edit',
                array(
                    'block_id' => $duplicateBlock->getId(),
                    '_current' => true
                )
            );

        }

        return true;
    }
}
