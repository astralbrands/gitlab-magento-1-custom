<?php
class Born_Borncmshooks_Block_Borncmshooks extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function createChild($template,$data_name=null,$data_object=null){
        $child = $this->getLayout()->createBlock('borncmshooks/borncmshooks')->setTemplate($template)->setData($data_name, $data_object)->toHtml();
        return $child;
    }
    
    public function createNodes(){   
        //  Let's see if we are calling this block independently
        try {
            $node_identifier = $this->getNodeidentifier();
        } catch (Exception $exc) {
            $node_identifier = NULL;
        }

        if($node_identifier == NULL){
            try {
                  $passed_page_id = $this->getPageid();
              } catch (Exception $exc) {
                  $passed_page_id = NULL;
              }
              if($passed_page_id == NULL){
                  $current_page_id = Mage::getSingleton('cms/page')->getPageId();
                  $current_page_node = Mage::getModel('enterprise_cms/hierarchy_node')->load($current_page_id,'page_id');

                  $parent_page_node = Mage::getModel('enterprise_cms/hierarchy_node')->load($current_page_node->getParentNodeId());
                  $parent_page_label = $parent_page_node->getLabel();

                  $collection = Mage::getModel('enterprise_cms/hierarchy_node')
                        ->getCollection()
                        ->joinCmsPage()
                        ->setTreeOrder()
                        ->addFieldToFilter('parent_node_id', array('eq' => $current_page_node->getParentNodeId()));
              }else{
                  $current_page_id = $passed_page_id;
                  $current_page_node = Mage::getModel('enterprise_cms/hierarchy_node')->load($current_page_id,'page_id');

                  $parent_page_node = Mage::getModel('enterprise_cms/hierarchy_node')->load($current_page_node->getParentNodeId());
                  $parent_page_label = $parent_page_node->getLabel();

                  $collection = Mage::getModel('enterprise_cms/hierarchy_node')
                        ->getCollection()
                        ->joinCmsPage()
                        ->setTreeOrder()
                        ->addFieldToFilter('parent_node_id', array('eq' => $current_page_node->getParentNodeId()));
              }

        }else{
            $current_page_node = Mage::getModel('enterprise_cms/hierarchy_node')->load($node_identifier,'identifier');
        //      $parent_page_label = $current_page_node->getLabel();
            $parent_page_label = "Helpful Links";

            $collection = Mage::getModel('enterprise_cms/hierarchy_node')
                  ->getCollection()
                  ->joinCmsPage()
                  ->setTreeOrder()
                  ->addFieldToFilter('parent_node_id', array('eq' => $current_page_node->getNodeId()));
        }

        $nodes = array();

        foreach ($collection as $item) {
            /* @var $item Enterprise_Cms_Model_Hierarchy_Node */
            $child_node = array(
                'node_id'        => $item->getId(),
                'parent_node_id' => $item->getParentNodeId(),
                'label'          => $item->getLabel(),
                'url'            => $item->getRequestUrl(),
                'page_exists'    => (bool)$item->getPageExists(),
                'page_id'        => $item->getPageId(),
            );
            array_push($nodes, $child_node);
        }

          //checking for secure pages
          if(Mage::app()->getStore()->isCurrentlySecure()){
              $base_url = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL);
          }else{
              $base_url = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
          }
          
          $response['base_url'] = $base_url;
          $response['nodes'] = $nodes;
          $response['parent_page_label'] = $parent_page_label;
          $response['current_page_node'] = $current_page_node->getId();
          $response_obj = new Varien_Object();
          $response_obj->setData($response);
          return $response_obj;
    }
    
    public function renderWidgets($block, $code=null){
        $hooks_model = Mage::getModel('borncmshooks/borncmshooks');
        $hooks_helper = Mage::helper('borncmshooks');
            
        if(is_string($block)){
            $name_in_layout = $block;
            $block = $this->getLayout()->createBlock('borncmshooks/borncmshooks');
        }else{
            $name_in_layout = $block->getNameInLayout();
            $code = $block->getCode();
        } 
        $block_data_as_array = $hooks_model->getallData($code);
        $block_data_as_collection = $hooks_helper->arrayToVarienCollection($block_data_as_array);
        $block_template = 'borncmshooks/blocks/' . $name_in_layout . '.phtml';
        $block->setTemplate($block_template);
        
        $current_data = new Varien_Data_Collection();
        if(count($block_data_as_collection->getColumnValues($name_in_layout . '-field'))){
            $current_data = $block_data_as_collection->getColumnValues($name_in_layout . '-field');
            $current_data = $hooks_helper->arrayToVarienCollection(array_shift($current_data));
        }
        if(isset($current_data) && count($current_data)){
            $block->setData( $name_in_layout . '_object_collection',$current_data);
            echo $block->toHtml();
        }
    }

    public function getHookData($field) {   
      $hooks_model = Mage::getModel('borncmshooks/borncmshooks');
      $hooks_helper = Mage::helper('borncmshooks');

      $code = $hooks_model->getPageCode();
      $block_data_as_array = $hooks_model->getallData($code);
      $block_data_as_collection = $hooks_helper->arrayToVarienCollection($block_data_as_array);

      $current_data = new Varien_Data_Collection();
      $current_data = $block_data_as_collection->getColumnValues($field . '-field');
      $current_data = $hooks_helper->arrayToVarienCollection(array_shift($current_data));

      return $current_data;
    }

    public function getSlots($sectionId, $fieldName='slot', $sortFlag=true)
    {   
      $sort_helper = Mage::helper('borncmshooks/sort');

      $slotCollection = $this->getHookData($fieldName);

      $dataCollection = new Varien_Data_Collection();

      foreach($slotCollection as $slot)
      {
        if($slot->getSectionId() == $sectionId)
        {
          $dataCollection->addItem($slot);
        }
      }

      if($sortFlag && $dataCollection)
      {
        $dataCollection = $sort_helper->sortCollection($dataCollection);
      }
      return $dataCollection;
    }

    public function renderSubWidgets($block, $objectCollection, $index=null)
    {   
      if(is_string($block)){
        $name_in_layout = $block;
        $block = $this->getLayout()->createBlock('borncmshooks/borncmshooks');
      }else{
        $name_in_layout = $block->getNameInLayout();
        $code = $block->getCode();
      } 

      $block_template = 'borncmshooks/blocks/' . $name_in_layout . '.phtml';
      $block->setTemplate($block_template);

      $block->setData( $name_in_layout . '_object_collection',$objectCollection);

      if ($index) {
        $block->setData('index', $index);
      }
      echo $block->toHtml();
    }

    public function getSectionObject(&$objectCollection)
    {
      foreach($objectCollection as $key => $object)
      {
        if($object->getTitle())
        {
          $tempObject = new Varien_Object();
          $tempObject = $object;
          $objectCollection->removeItemByKey($key);
          return $object;
        }
      }
      return;
    }

    public function getSectionTitle(&$objectCollection)
    {   
      foreach ($objectCollection as $key => $object) {
        if($object->getSectionTitle()){
          $tempObject = new Varien_Object();
          $tempObject = $object;
          $objectCollection->removeItemByKey($key);
          
          return $tempObject->getSectionTitle();
        }
      }
    }

    public function getPriceHtml($_product)
    {    
      $_product = Mage::getModel('catalog/product')->load($_product->getId());
      $productBlock = $this->getLayout()->createBlock('catalog/product_price');
      $html = $productBlock->getPriceHtml($_product);

      return $html;
    }

    //Debug
    public function displayCollection($object_collection)
    {    
      foreach($object_collection as $object){
        var_dump($object);
      }
    }


    public function isCategoryLandingPage()
    {  
      $currentCategory = $this->getCurrentCategory();

      if($currentCategory)
      {
        if($currentCategory->getLevel() == 2)
        {
          return true;
        }
      }

      return false;
    }

    public function saveProductInfo($product)
    {  
      $key = $this->getPageBvProductInfoKey();

      $info = Mage::registry($key);
      if ($info) {
          Mage::unregister($key);
      }

      $_productInfo = array('sku' => $product->getSku(), 'url' => $product->getProductUrl());
      $info[] = $_productInfo;
      Mage::register($key, $info);
    }

    public function getPageBvProductInfoKey()
    {    
      return 'bv_product_info_' . Mage::getSingleton('cms/page')->getIdentifier();
    }

    /**
     * @return array
     */
    public function getProductInfo()
    {  
      $key = $this->getPageBvProductInfoKey();
      $info = Mage::registry($key);

      if ($info) {
        Mage::unregister($key);
      }

      return $info;
    }

    public function getCurrentCategory()
    {   
      return Mage::registry('current_category');
    }

    public function getIsHomePage()
    {   
       return (Mage::getSingleton('cms/page')->getIdentifier() == 'home' && 
        Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms');
    }

    public function renderProductView($_product, $tagMessage = null)
    {
      if($_product){
        $block = $this->getLayout()->createBlock('borncmshooks/borncmshooks');
        $block_template = 'borncmshooks/blocks/productview.phtml';
        $block->setTemplate($block_template);
        $block->setData('product', $_product);
        if ($tagMessage) {
          $block->setData('tag_message', $tagMessage);
        }
        echo $block->toHtml();
      }
    }

    public function showBvInlineRating()
    {  
      return '1';
    }

    public function getTitleModalType($typeId)
    {
      switch ($typeId) {
        case '1':
        return 'wysiwyg';
        break;
        case '2':
        return 'video';
        break;
        case '3':
        return false;
        break;
        default:
        return false;
        break;
      }
    }

    public function getTitleCtaLink($_tile, $_modalType)
    {   
      if ($_modalType) {
        return '#';
      }

      if ($_tile->getCtaLink()) {
        return $_tile->getCtaLink();
      }
    }

}