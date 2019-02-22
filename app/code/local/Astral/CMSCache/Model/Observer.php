<?php
/**
 * Class Astral_CMSCache_Model_Observer
 */
class Astral_CMSCache_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Model_Store_Exception
     */
    public function enableCmsBlockCaching(Varien_Event_Observer $observer)
	{
        if (!$this->_getHelper()->isEnabled()){
            return;
        }
		$block = $observer->getBlock();
		//make sure cache is going to apply for a cms block.
        if ($block instanceof Mage_Cms_Block_Widget_Block
            || $block instanceof Mage_Cms_Block_Block
        ) {
        	//making a unique cache key for each cms blocks so that cached HTML
        	//content will be unique per each static block
            $cacheKeyData = array(
                Mage_Cms_Model_Block::CACHE_TAG,
                $block->getBlockId(),
                Mage::app()->getStore()->getId(),
                intval(Mage::app()->getStore()->isCurrentlySecure()),
                Mage::getDesign()->getPackageName(),
                Mage::getDesign()->getTheme('template')
                //Mage::helper('astral_cmscache')->randomString() // UNCOMMENT IF IT IS NECESSARY
            );
            $block->setCacheKey(implode('_', $cacheKeyData));
            //set cache tags. This will help us to clear the cache related to
            //a static block based on store, CMS cache, or by identifier.
            $block->setCacheTags(array(
		        Mage_Core_Model_Store::CACHE_TAG,
		        Mage_Cms_Model_Block::CACHE_TAG,
		        (string)$block->getBlockId()
		    ));
		    //setting cache life time to default. ie 7200 seconds(2 hrs).
		    //an integer value in seconds. eg : 86400 for one day cache
            $block->setCacheLifetime($this->_getHelper()->getCacheTimeout());
        }
	}
    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper()
    {
        return Mage::helper('astral_cmscache');
    }
}