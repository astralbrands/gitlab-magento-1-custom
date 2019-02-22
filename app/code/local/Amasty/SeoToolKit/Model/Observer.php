<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Observer
{
    public function redirect301()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }

        $request = Mage::app()->getRequest();

        if (!Mage::isInstalled()
            || $request->getPost()
            || strtolower($request->getMethod()) == 'post'
            || !Mage::getStoreConfig('amseotoolkit/general/home_redirect')
        ) {
            return;
        }

        $baseUrl = Mage::getBaseUrl(
            Mage_Core_Model_Store::URL_TYPE_WEB,
            Mage::app()->getStore()->isCurrentlySecure()
        );

        if (!$baseUrl) {
            return;
        }

        $requestPath = $request->getRequestUri();
        $params = preg_split('/^.+?\?/', $request->getRequestUri());
        $baseUrl .= isset($params[1]) ? '?' . $params[1] : '';

        $redirectUrls = array(
            '',
            '/cms',
            '/cms/',
            '/cms/index',
            '/cms/index/',
            '/index.php',
            '/index.php/',
            '/home',
            '/home/',
        );

        if (!is_null($requestPath) && in_array($requestPath, $redirectUrls)) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect($baseUrl, 301)
                ->sendResponse();

            Mage::helper('ambase/utils')->_exit();
        }
    }

    public function onLayoutGenerateBlocksAfter($observer)
    {
        $appendTitleSuffix = Mage::getStoreConfigFlag('amseotoolkit/pager/meta_title_suffix');
        $appendDescriptionSuffix = Mage::getStoreConfigFlag('amseotoolkit/pager/meta_description_suffix');

        if (!$appendTitleSuffix && !$appendDescriptionSuffix) {
            return;
        }

        /** @var Mage_Core_Model_Layout $layout */
        $layout = $observer->getLayout();

        /** @var Mage_Page_Block_Html_Pager $pagerBlock */
        $pagerBlock = $layout->getBlock('product_list_toolbar_pager');

        if (!$pagerBlock) {
            return;
        }

        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $layout->getBlock('head');

        $page = +Mage::app()->getRequest()->getParam($pagerBlock->getPageVarName());

        if ($page < 2) {
            return;
        }

        $metaSuffix = Mage::helper('amseotoolkit')->__(' Page %d', $page);

        if ($appendTitleSuffix) {
            $headBlock->setTitle($headBlock->getTitle() . $metaSuffix);
        }

        if ($appendDescriptionSuffix) {
            $headBlock->setDescription($headBlock->getDescription() . $metaSuffix);
        }
    }

    public function onControllerFrontSendResponseBefore($observer)
    {
        if (!Mage::getStoreConfigFlag('amseotoolkit/pager/prev_next')) {
            return;
        }

        /** @var Mage_Core_Controller_Varien_Front $front */
        $front = $observer->getFront();

        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::app()->getLayout();

        /** @var Mage_Page_Block_Html_Pager $pagerBlock */
        $pagerBlock = $layout->getBlock('product_list_toolbar_pager');

        if (!$pagerBlock || !$pagerBlock->getCollection()) {
            return;
        }

        $additionalTags = array();

        if (!$pagerBlock->isLastPage()) {
            $additionalTags [] = "<link rel='next' href='{$pagerBlock->getNextPageUrl()}' />";
        }

        if (!$pagerBlock->isFirstPage()) {
            $additionalTags [] = "<link rel='prev' href='{$pagerBlock->getPreviousPageUrl()}' />";
        }

        if (empty($additionalTags)) {
            return;
        }

        $body = $front->getResponse()->getBody();

        $body = str_replace(
            '</head>',
            "\n" . implode("\n", $additionalTags) . "\n</head>",
            $body,
            $count
        );

        if ($count == 1) {
            $front->getResponse()->setBody($body);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addHreflangUuidField(Varien_Event_Observer $observer)
    {
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('cms_page');
        $uuidField = Amasty_SeoToolKit_Helper_Hrefurl::CMS_UUID;
        if (!$resource->getConnection('core_read')->tableColumnExists($tableName, $uuidField)) {
            return $this;
        }

        $form = $observer->getForm();
        $fieldset  = $form->getElements()->searchById('meta_fieldset');
        if (!$fieldset) {
            return $this;
        }

        $fieldset->addField($uuidField, 'text',
            array(
                'name'     => $uuidField,
                'label'    => Mage::helper('amseotoolkit')->__('Hreflang UUID'),
                'title'    => Mage::helper('amseotoolkit')->__('hreflang UUID'),
                'required' => false,
                'disabled' => false,
                'class' => 'validate-data',
            )
        );

        $model = Mage::registry('cms_page');
        $form->setValues($model->getData());

        return $this;
    }
}
