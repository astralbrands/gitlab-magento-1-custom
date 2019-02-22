<?php

// Controllers are not autoloaded so we will have to do it manually:
require_once('Mage/Adminhtml/controllers/Cms/PageController.php');

class Addedbytes_Duplicatecmspage_Adminhtml_Cms_PageController extends Mage_Adminhtml_Cms_PageController
{
    public function duplicateAction()
    {
        // Load page being duplicated by page_id param
        $params = $this->getRequest()->getParams();
        $cmsPage = Mage::getModel('cms/page')->load($params['page_id']);
        if ($cmsPage) {

            // Create new page
            $duplicatePage = Mage::getModel('cms/page');

            // Now we need to get the existing page data to populate new object
            $cmsPageData = $cmsPage->getData();

            // We don't want to set the page ID, otherwise we're just updating
            // the original page.
            unset($cmsPageData['page_id']);

            // Update title and identifier to make them unique. Trim any
            // existing duplication info first.
            $cmsPageData['title'] = preg_replace('~( - Duplicate \([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\))+$~i', '', $cmsPageData['title']);
            $cmsPageData['identifier'] = preg_replace('~(-duplicate-[0-9]{14})+$~i', '', $cmsPageData['identifier']);
            $cmsPageData['title'] = $cmsPageData['title'] . ' - Duplicate (' . date('Y-m-d H:i:s') . ')';
            $cmsPageData['identifier'] = $cmsPageData['identifier'] . '-duplicate-' . date('YmdHis');

            // Set data and save
            $duplicatePage->setData($cmsPageData)->save();

            // Feedback
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('The page has been duplicated.'));

            // Redirect to new page
            $this->_redirect(
                '*/*/edit',
                array(
                    'page_id' => $duplicatePage->getId(),
                    '_current' => true
                )
            );

        }

        return true;
    }
}
