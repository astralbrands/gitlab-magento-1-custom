<?php

/**
 * Class RocketWeb_ShoppingFeeds_Helper_Factory
 * Factory methods to build adapters and maps
 */
class RocketWeb_ShoppingFeeds_Helper_Factory extends Mage_Core_Helper_Abstract
{
    /**
     * Build the map model path based on product type and feed type
     *
     * @param Mage_Catalog_Model_Product $product
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @param array $args
     *  - array 'parents'
     *  - string 'parent_type'
     *  - bool 'is_assoc' - force assoc mode, needs a parent_type
     * @return RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Abstract
     */
    public function getProductAdapterModel(
            Mage_Catalog_Model_Product          $product,
            RocketWeb_ShoppingFeeds_Model_Feed  $feed,
                                                $args = array()
    )
    {
        $adapter = null;
        $helper = Mage::helper('rocketshoppingfeeds/map');

        $adapterArgs = array('product' => $product);
        $productType = $product->getTypeId();

        $isAssociated = (bool)
            (isset($args['is_assoc']) && $args['is_assoc'] && !empty($args['parent_type']))
            || (isset($args['parents']) && array_filter($args['parents']));

        if ($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            if ($isAssociated && isset($args['parent_type'])) {
                // this is used in order to follow the class folder structure
                $productType = $args['parent_type'];
            } else {
                // we use 'Abstract' folder in Model/Adapter and Model/Map/*/Product, not 'Simple'
                // substituting 'simple' for 'abstract' fixes both adapterExits() and getMapList() calls
                $productType = 'abstract';
            }
        }

        $key = ucfirst($productType);

        $adapterArgs['feed'] = $feed;

        if (($product->isConfigurable() || (isset($args['parent_type']) && $args['parent_type'] == 'configurable'))
            && $helper->isSimplePricingEnabled($product)) {
            $key .= '_Scp';
        }
        if ($isAssociated) {
            $key .= '_Associated';
        }

        $type = strpos($feed->getType(), '_') !== false ? ucfirst(substr($feed->getType(), 0, strpos($feed->getType(), '_'))) : ucfirst($feed->getType());

        // Load type specific adapter
        if ($helper->adapterExists('Adapter_'. $key)) {
            $adapter = Mage::getModel('rocketshoppingfeeds/adapter_' . strtolower($key), $adapterArgs);
            $adapter->setData('map_key', $key);
        } else {
            $adapter = Mage::getModel('rocketshoppingfeeds/adapter_abstract', $adapterArgs);
            $adapter->setData('map_key', 'Abstract');
        }

        $adapter->setMapList($this->getMapList($type, $key))
            ->setFeed($feed)
            ->setProduct($product)
            ->setColumnsMap($feed->getColumnsMap())
            ->setEmptyColumnsReplaceMap($feed->getEmptyColumnsReplaceMap())
            ->setData('store_currency_code', $feed->getStore()->getDefaultCurrencyCode())
            ->setStoreId($feed->getStoreId())
            ->initialize();

        return $adapter;
    }

    public function getChildAdapterModel($product, $parentAdapter, $feed) {
        $childAdapter = $this->getProductAdapterModel($product, $feed, array(
            'is_assoc' => true,
            'parent_type' => $parentAdapter->getProduct()->getTypeId()
        ));

        $childAdapter->setParentMap($parentAdapter);

        return $childAdapter;
    }

    /**
     * Creates map list of class inheritances
     *
     * @param string $type
     * @param string $key
     * @return array
     */
    public function getMapList($type, $key)
    {
        $keyList = explode('_', $type. '_Product_'. $key);
        $mapList = $this->_processMapList($keyList);

        if (lcfirst($type) != RocketWeb_ShoppingFeeds_Model_Feed_Type::TYPE_GENERIC) {
            $type = ucfirst(RocketWeb_ShoppingFeeds_Model_Feed_Type::TYPE_GENERIC);
            $keyList = explode('_', $type . '_Product_' . $key);
            $mapList = array_merge($mapList, $this->_processMapList($keyList));
        }

        $helper = Mage::helper('rocketshoppingfeeds/map');
        foreach ($mapList as $key => $map) {
            if (!$helper->mapExists($map)) {
                unset($mapList[$key]);
            }
        }
        return array_values($mapList);
    }

    /**
     * Process the class key string into possible map classes
     *
     * @param array $keyList
     * @return array
     */
    protected function _processMapList($keyList = array())
    {
        $mapList = array();
        $keyListSize = count($keyList) - 1;

        if ($keyList[$keyListSize] === 'Associated') {
            return $this->_processAssociatedMapList($keyList);
        }

        for ($i = $keyListSize; $i >= 0; $i--) {
            if ($keyList[$i] == 'Product') {
                $map = implode('_', $keyList) . '_Abstract';

                /**
                 * Substituting 'simple' for 'abstract' as product type in getProductAdapterModel()
                 * could add the same map twice for simple products.
                 */
                if (!in_array($map, $mapList)) {
                    $mapList[] = $map;
                }

                break;
            }

            $mapList[] = implode('_', $keyList);
            unset($keyList[$i]);
        }
        return $mapList;
    }

    /**
     * Process the class key string into possible map classes
     *
     * @param array $keyList
     * @return array
     */
    protected function _processAssociatedMapList($keyList)
    {
        $mapList = array();
        $keyListSize = count($keyList);

        while ($keyList[$keyListSize - 2] !== 'Product') {
            $mapList[] = implode('_', $keyList);
            unset($keyList[$keyListSize - 2]);
            $keyList = array_values($keyList);
            $keyListSize--;
        }

        array_splice($keyList, $keyListSize - 1, 0, 'Abstract');
        $keyList = array_values($keyList);
        $mapList[] = implode('_', $keyList);
        unset($keyList[$keyListSize]);
        $mapList[] = implode('_', $keyList);

        return $mapList;
    }
}