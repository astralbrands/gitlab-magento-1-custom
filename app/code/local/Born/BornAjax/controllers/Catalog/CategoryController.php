<?php 
require_once('app/code/core/Mage/Catalog/controllers/CategoryController.php');
class Born_BornAjax_Catalog_CategoryController extends Mage_Catalog_CategoryController
{
    /**
     * Initialize requested category object
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCatagory()
    {
        Mage::dispatchEvent('catalog_controller_category_init_before', array('controller_action' => $this));
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
        Mage::register('current_category', $category);
        Mage::register('current_entity_key', $category->getPath());

        try {
            Mage::dispatchEvent(
                'catalog_controller_category_init_after',
                array(
                    'category' => $category,
                    'controller_action' => $this
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     */
    public function viewAction()
    {
        if ($category = $this->_initCatagory()) {
            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }

            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());
			$layout = $this->getLayout();
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();
            $update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            if ($layoutUpdates = $settings->getLayoutUpdates()) {
                if (is_array($layoutUpdates)) {
                    foreach($layoutUpdates as $layoutUpdate) {
                        $update->addUpdate($layoutUpdate);
                    }
                }
            }

            $this->generateLayoutXml()->generateLayoutBlocks();
			Mage::dispatchEvent('controller_action_layout_render_before_'.$this->getFullActionName());
		    $block = $layout->getBlock('product_list');
		    $jsonResponse = array();
		    try{
		    	$jsonResponse['status'] = 1;
		    	$jsonResponse['html'] = $block->toHtml();
	
	//			$this->getResponse()->setBody($jsonResponse);
		    }catch(Exception $e){
		    	$jsonResponse['status'] = 0;
		    	$jsonResponse['html'] = $e->getMessage();
		    	
		    }

        }
        elseif (!$this->getResponse()->isRedirect()) {
		    	$jsonResponse['status'] = 0;
		    	$jsonResponse['html'] = $this->__('Invalid Category');
        }
	        echo json_encode($jsonResponse);
			exit;            
    }
}
