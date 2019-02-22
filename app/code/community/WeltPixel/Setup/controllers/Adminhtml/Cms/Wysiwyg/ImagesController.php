<?php

require_once 'Mage/Adminhtml/controllers/Cms/Wysiwyg/ImagesController.php';

class WeltPixel_Setup_Adminhtml_Cms_Wysiwyg_ImagesController extends Mage_Adminhtml_Cms_Wysiwyg_ImagesController
{
    
    /**
     * Add to session a param, with observer was not duable, another ajax call onInsert is called
     */
    public function indexAction()
    {
        if ($this->getRequest()->getParam('allow_static')) {
            $this->_getSession()->setAllowStatic(true);
        }
        if ($this->getRequest()->getParam('static_urls_allowed')){
            $this->_getSession()->setStaticUrlsAllowed(true);
        }
        parent::indexAction();
    }

    /**
     * Remove from session
     */
    public function onInsertAction()
    {
        parent::onInsertAction();
        $this->_getSession()->setAllowStatic();
    }
}