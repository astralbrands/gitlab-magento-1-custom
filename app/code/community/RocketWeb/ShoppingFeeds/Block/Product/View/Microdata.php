<?php

/**
 * Class RocketWeb_ShoppingFeeds_Block_Product_View_Microdata
 *
 * Microdata block - supports all default product types. Configurables will include information about children
 * Uses the Feed model and Adapters to generate map for the current product
 *
 * @usage $list = $block->setProduct($product)->getMicrodata();
 *        $microdata = $list[0];
 *        $microdata->getName();
 *        $microdata->getPrice();
 *        $microdata->getCurrency();
 *        $microdata->getAvailability();
 *
 * @see RocketWeb_ShoppingFeeds_Model_Generator
 */
class RocketWeb_ShoppingFeeds_Block_Product_View_Microdata
    extends Mage_Catalog_Block_Product_View_Abstract
{
    const XML_PATH_ENABLED              = 'rocketweb_shoppingfeeds/general/microdata_turned_on';
    const XML_PATH_CONDITION_ATTRIBUTE  = 'rocketweb_shoppingfeeds/general/microdata_condition_attribute';

    /** @var array columns to generate by the map generator */
    protected $_columns = array('id', 'price', 'sale_price', 'availability', 'title', 'condition');

    /** @var RocketWeb_ShoppingFeeds_Model_Feed */
    protected $_feed;
    protected $_product;

    protected function _construct() {
        $this->_feed = Mage::getModel('rocketshoppingfeeds/feed')->getCollection()
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('use_for_microdata', '1')
            ->load()
            ->getFirstItem();

        if ($this->_feed->getId()) {
            $this->_feed->load($this->_feed->getId());
            $this->_feed->setOutputColumns($this->_columns);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) ($this->_feed->getId()) && Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Object[]
     */
    public function getMicrodata()
    {
        /** @var Varien_Object[] $microdata_list */
        $microdata_list = array();

        $this->_product = $this->getProduct();
        if ($this->isEnabled() && $this->_product && $this->_product->getId()) {
            try {
                $map = $this->_loadMap();
                if ($map) {
                    $microdata_list[] = $map;
                }
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $microdata_list;
    }

    /**
     * Run the map for the current product
     */
    protected function _loadMap() {

        $adapter = $this->_loadAdapter();
        $prices = $adapter->getPrices();
        $columns = $this->_feed->getColumnsMap();

        $priceIncludeTax = (bool) $columns['price']['param'];
        $salePriceIncludeTax = (bool) $columns['sale_price']['param'];

        return $this->_createRowObject(array(
            'id' => $adapter->mapColumn('id'),
            'price' => ($priceIncludeTax ? $prices['p_incl_tax'] : $prices['p_excl_tax']),
            'sale_price' => ($salePriceIncludeTax ? $prices['sp_incl_tax'] : $prices['sp_excl_tax']),
            'availability' => $adapter->mapColumn('availability'),
            'title' => $adapter->mapColumn('title'),
            'condition' => $adapter->mapColumn('condition')
        ));
    }

    /**
     * Get adapter for map; returns child adapter if needed; skips duplicate checks
     *
     * @return mixed
     * @throws Exception
     */
    protected function _loadAdapter() {
        $parentAdapter = Mage::helper('rocketshoppingfeeds/factory')
            ->getProductAdapterModel($this->_product, $this->_feed, array(
                'parents' => array(),
                'parent_type' => ''
            ));

        $adapter = $parentAdapter;

        $assocId = (int) $this->getRequest()->getParam('aid', false);
        if (is_int($assocId) && $assocId > 0) {
            $child = Mage::getModel('catalog/product')->load($assocId);
            if ($child->getId()) {
                $this->_product = $child;

                $childAdapter = Mage::helper('rocketshoppingfeeds/factory')
                    ->getChildAdapterModel($this->_product, $parentAdapter, $this->_feed);

                $adapter = $childAdapter;
            }
        }

        $adapter->setSkipDuplicateCheck(true);

        return $adapter;
    }

    /**
     * Converts map array to microdata Object
     *
     * @param array $map map array returned by the generator
     * @return null|Varien_Object
     */
    protected function _createRowObject($map)
    {
        if (empty($map['price']) || empty($map['availability']) || empty($map['title'])) {
            return null;
        }

        $map = $this->_addOptionPrice($map);

        $microdata = new Varien_Object();
        $microdata->setName($map['title']);
        $microdata->setId($map['id']);

        if (!empty($map['sale_price'])){
            $price = $map['sale_price'];
        }
        else {
            $price = $map['price'];
        }

        $microdata->setPrice(Zend_Locale_Format::toNumber($price, array(
            'precision' => 2,
            'number_format' => '#0.00'
        )));

        $microdata->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());
        if ($map['availability'] == 'in stock') {
            $microdata->setAvailability('http://schema.org/InStock');
        }
        else {
            $microdata->setAvailability('http://schema.org/OutOfStock');
        }

        if (array_key_exists('condition', $map)) {
            if (strcasecmp('new', $map['condition']) == 0) {
                $microdata->setCondition('http://schema.org/NewCondition');
            }
            else if (strcasecmp('used', $map['condition']) == 0) {
                $microdata->setCondition('http://schema.org/UsedCondition');
            }
            else if (strcasecmp('refurbished', $map['condition']) == 0) {
                $microdata->setCondition('http://schema.org/RefurbishedCondition');
            }
        }

        return $microdata;
    }

    protected function _addOptionPrice($map) {
        $productOptions = $this->_product->getOptions();
        if (count($productOptions) > 0) {
            /** @var Mage_Catalog_Model_Product_Option $option */
            foreach ($productOptions as $option) {
                $type = $option->getType();
                if ($type == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                    $type = Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT;
                }
                $key = $type. '_'. $option->getId();
                if (($valueId = $this->getRequest()->getParam($key, false)) !== false) {
                    /** @var Mage_Catalog_Model_Product_Option_Value $values */
                    $values = $option->getValues();
                    foreach ($values as $value) {
                        if ($valueId == $value->getId()) {
                            $map['price'] += $value->getPrice(true);
                            if (!empty($map['sale_price'])) {
                                $map['sale_price'] += $value->getPrice(true);
                            }
                        }
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Set a new product.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->setData('product', $product);
        return $this;
    }
}
