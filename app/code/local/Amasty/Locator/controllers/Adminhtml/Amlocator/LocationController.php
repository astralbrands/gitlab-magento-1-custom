<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Adminhtml_Amlocator_LocationController
    extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('amlocator');
        $contentBlock = $this->getLayout()->createBlock(
            'amlocator/adminhtml_location'
        );
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $locations = $this->getRequest()->getParam('id', null);

        if (is_array($locations) && sizeof($locations) > 0) {
            try {
                foreach ($locations as $location) {
                    Mage::getModel('amlocator/location')->setId($location)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Total of %d location(s) have been deleted',
                        sizeof($locations)
                    )
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select locations'));
        }
        $this->_redirect('*/*');
    }


    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amlocator/location');

        if ($data = Mage::getSingleton('adminhtml/session')->getFormData()) {
            if (isset($data['photo'])) {
                if (is_array($data['photo'])) {
                    $end = strrchr($data['photo']['value'], "/");
                    $end = substr($end, 1, strlen($end));
                    $data['photo'] = $end;
                }
            }
            $model->setData($data)->setId($id);
        } else {
            $model->load($id);
        }
        Mage::register('current_location', $model);
        $this->loadLayout()->_setActiveMenu('amlocator');

        $this->getLayout()->getBlock('head')->addJs('amasty/amlocator/admin.js');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addLeft(
            $this->getLayout()->createBlock(
                'amlocator/adminhtml_location_edit_tabs'
            )
        );
        $this->_addContent(
            $this->getLayout()->createBlock('amlocator/adminhtml_location_edit')
        );
        $this->renderLayout();
    }

    public function saveAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('amlocator/location');
                $model->setData($data)->setId(
                    $this->getRequest()->getParam('id')
                );
                if (!$model->getCreated()) {
                    $model->setCreated(now());
                }
                if (isset($data['photo'])) {
                    if (is_array($data['photo'])) {
                        $end = strrchr($data['photo']['value'], "/");
                        $end = substr($end, 1, strlen($end));
                        $model->setData('photo', $end);
                    }
                    if ( isset($data['photo']['delete']) &&  $data['photo']['delete'] == 1) {
                        $helperImage = Mage::helper('amlocator/image');
                        $model->setData('photo', '');
                        $helperImage->deleteImage($end);
                    }
                }

                if (isset($data['marker'])) {
                    if (is_array($data['marker'])) {
                        $end = strrchr($data['marker']['value'], "/");
                        $end = substr($end, 1, strlen($end));
                        $model->setData('marker', $end);
                    }
                    if ( isset($data['marker']['delete']) && $data['marker']['delete'] == 1) {
                        $helperImage = Mage::helper('amlocator/image');
                        $model->setData('marker', '');
                        $helperImage->deleteImage($end);
                    }
                }

                $model->save();
                $session->addSuccess($this->__('Location was saved successfully'));
                $session->setFormData(false);
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                $session->addError(
                    $e->getMessage()
                );
                $session->setFormData($data);
                $this->_redirect(
                    '*/*/edit', array(
                        'id' => $this->getRequest()->getParam('id')
                    )
                );
            }
            return;
        }
        $session->addError(
            $this->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('amlocator/location')->setId($id)->delete();
                $session->addSuccess(
                    $this->__('Location was deleted successfully')
                );
            } catch (Exception $e) {
                $session->addError(
                    $e->getMessage()
                );
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }

    public function productsAction()
    {
        $grid = $this->getLayout()->createBlock('amlocator/adminhtml_location_edit_tabs_products_grid')
            ->setSelectedProducts($this->getRequest()->getPost('selected_products', null));

        $this->getResponse()->setBody(
            $grid->toHtml()
        );
    }

    /*
     * Action for getting Category Tree (JS tree view) for tab `Access Category Restriction`
     * which is build with AJAX requests calling this action
     */
    public function categoriesJsonAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amlocator/location');

        $model->load($id);
        Mage::register('current_location', $model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('amlocator/adminhtml_location_edit_tabs_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            'cms/amlocator'
        );
    }

}