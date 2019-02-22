<?php
class Born_Storeduplicator_Adminhtml_StoreduplicatorbackendController extends Mage_Adminhtml_Controller_Action
{
	protected $websiteCode = 'cosb2bint';
	protected $websiteName = 'Cosmedix International Distributors';
	protected $storeName = 'COSMEDIX Website';
	protected $storeViewCode = 'cosb2bint_store';
	protected $storeViewName = 'COSMEDIX B2B INT Store View';
	protected $rootCategoryId = 3;
	protected $sourceStoreId = 3;
	protected function _isAllowed()
	{
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Store Duplocator"));
	   $this->renderLayout();
    }
	public function createStoresAction()
	{
		//#addWebsite
        /** @var $website Mage_Core_Model_Website */
        $website = Mage::getModel('core/website');
        $website->setCode($this->websiteCode)
            ->setName($this->websiteName)
            ->save();

        //#addStoreGroup
        /** @var $storeGroup Mage_Core_Model_Store_Group */
        $storeGroup = Mage::getModel('core/store_group');
        $storeGroup->setWebsiteId($website->getId())
            ->setName($this->storeName)
            ->setRootCategoryId($this->rootCategoryId)
            ->save();

        //#addStore
        /** @var $store Mage_Core_Model_Store */
        $store = Mage::getModel('core/store');
        $store->setCode($this->storeViewCode)
            ->setWebsiteId($storeGroup->getWebsiteId())
            ->setGroupId($storeGroup->getId())
            ->setName($this->storeViewName)
            ->setIsActive(1)
            ->save();
	}
	public function reindexAction()
	{
		/* @var $indexCollection Mage_Index_Model_Resource_Process_Collection */
		$indexCollection = Mage::getModel('index/process')->getCollection();
		foreach ($indexCollection as $index) {
		/* @var $index Mage_Index_Model_Process */
			$index->reindexAll();
		}
	}
	protected function _getStore()
	{
		$store = Mage::getModel('core/store')->load($this->storeViewCode, 'code');
		return $store;
	}
	public function assignCmsAction()
	{
		$collection = Mage::getModel('cms/page')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($this->sourceStoreId);
        foreach ($collection as $cmspage) {
            $cmspage = Mage::getModel('cms/page')->load($cmspage->getPageId());
            $storeIds = $cmspage->getStoreId();

            if (in_array($this->sourceStoreId, $storeIds) && !in_array($this->_getStore()->getId(), $storeIds)) {
                $storeIds[] = $this->_getStore()->getId();
                $cmspage->setStores($storeIds);
                $cmspage->save();
            }
        }
	}	
	public function assignBlocksAction()
	{
        $collection = Mage::getModel('cms/block')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($this->sourceStoreId);

        foreach ($collection as $block) {

            $block = Mage::getModel('cms/block')->load($block->getBlockId());
            $storeIds = $block->getResource()->lookupStoreIds($block->getBlockId());

            if (in_array($this->sourceStoreId, $storeIds) && !in_array($this->_getStore()->getId(), $storeIds)) {
                $storeIds[] = $this->_getStore()->getId();
                $block->setStores($storeIds);
                $block->save();
            }
        }
	}
	public function assignHooksAction()
	{
		$rows = Mage::getModel('borncmshooks/rows')->getCollection()->addStoreFilter($this->sourceStoreId);
        foreach ($rows as $row) {
            $row = Mage::getModel('borncmshooks/rows')->load($row->getId());
            $storeId = $row->getStoreId();
            $storeId .= ',' . $this->_getStore()->getId();
            $row->setStoreId($storeId);
            $row->save();
        }
	}
	public function copyStoreConfigAction()
	{
		/* script to copy all store configuration */
        /** @var $store Mage_Core_Model_Store */
        $store = Mage::getModel('core/store');
        $store->load($this->storeViewCode, 'code');
        $storeId = $this->_getStore()->getId();
        $websiteId = $this->_getStore()->getWebsiteId();

        /* initiate installer */
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        /* start setup */
        $installer->startSetup();
        /* do setup */
        $installer->run("

        # DEFINE
        SET @to_website := $websiteId; # the websites_id of the recipient store
        SET @to_store := $storeId; # the store_id of the recipient store
        SET @from_website := ".$this->sourceStoreId." ; # the websites_id of the donor store
        SET @from_store := ".$this->sourceStoreId."; # the store_id of the donor store

        /** core_config_data */
        DELETE FROM core_config_data
        WHERE scope = 'websites'
        AND scope_id = @to_website;
        DELETE FROM core_config_data
        WHERE scope = 'stores'
        AND scope_id = @to_store;

        INSERT INTO core_config_data (
        scope,
        scope_id,
        path,
        value
        ) SELECT
        scope,
        @to_website,
        path,
        value
        FROM core_config_data
        WHERE scope = 'websites'
        AND scope_id = @from_website;

        INSERT INTO core_config_data (
        scope,
        scope_id,
        path,
        value
        ) SELECT
        scope,
        @to_store,
        path,
        value
        FROM core_config_data
        WHERE scope = 'stores'
        AND scope_id = @from_store;
        ");
        /* end setup */
        $installer->endSetup();
	}
	public function copyCategoryConfigAction()
	{
		/** @var $store Mage_Core_Model_Store */
		$store = Mage::getModel('core/store');
		$store->load('store_code','code');
        $storeId = $this->_getStore()->getId();
        $websiteId = $this->_getStore()->getWebsiteId();
		 
		/* initiate installer */
		$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
		/* start setup */
		$installer->startSetup();
		/* do setup */
		$installer->run("
		 
        # DEFINE
        SET @to_website := $websiteId; # the websites_id of the recipient store
        SET @to_store := $storeId; # the store_id of the recipient store
        SET @from_website := ".$this->sourceStoreId." ; # the websites_id of the donor store
        SET @from_store := ".$this->sourceStoreId."; # the store_id of the donor store
		 
		/** catalog_category_entity_int */
		DELETE FROM catalog_category_entity_int
		WHERE store_id = @to_store;
		 
		INSERT INTO catalog_category_entity_int (
			entity_type_id,
			attribute_id,
			store_id,
			entity_id,
			value
		) SELECT
			entity_type_id,
			attribute_id,
			@to_store,
			entity_id,
			value
		FROM catalog_category_entity_int
		WHERE store_id = @from_store;
		 
		/** catalog_category_entity_text */
		DELETE FROM catalog_category_entity_text
		WHERE store_id = @to_store;
		 
		INSERT INTO catalog_category_entity_text (
			entity_type_id,
			attribute_id,
			store_id,
			entity_id,
			value
		) SELECT
			entity_type_id,
			attribute_id,
			@to_store,
			entity_id,
			value
		FROM catalog_category_entity_text
		WHERE store_id = @from_store;
		 
		/** catalog_category_entity_varchar */
		DELETE FROM catalog_category_entity_varchar
		WHERE store_id = @to_store;
		 
		INSERT INTO catalog_category_entity_varchar (
			entity_type_id,
			attribute_id,
			store_id,
			entity_id,
			value
		) SELECT
			entity_type_id,
			attribute_id,
			@to_store,
			entity_id,
			value
		FROM catalog_category_entity_varchar
		WHERE store_id = @from_store;
		");
		 
		/* end setup */
		$installer->endSetup();
	}
	public function assignStoreUrl()
	{
		/** @var $store Mage_Core_Model_Store */
		$store = Mage::getModel('core/store');
		$store->load('store_code','code');
		$storeId = $store->getStoreId();
		$websiteId = $store->getWebsiteId();
		 
		$_config = new Mage_Core_Model_Config();
		$_options = array(
			'web/secure/base_url' => 'https://www.your.new.domain.xxx/',
			'web/unsecure/base_url' => 'http://www.your.new.domain.xxx/',
		);
		 
		foreach( $_options as $_path => $_value ) {
			$_config->saveConfig( $_path, $_value, 'websites', $websiteId );
		}
	}
}