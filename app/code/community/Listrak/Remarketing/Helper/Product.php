<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.0.0
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2011 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Helper_Product
 */
class Listrak_Remarketing_Helper_Product
    extends Mage_Core_Helper_Abstract
{
    /* @var Mage_Catalog_Model_Product[] $parentsById */
    protected $parentsById = array();

    /* @var string[] $urlsById */
    protected $urlsById = array();

    /**
     * Attribute set options
     * @var array
     */
    protected $attributeSets = null;

    /**
     * Flag that enables fetching of all attributes' values
     * @var boolean
     */
    protected $retrieveAttributes = null;
    
    /**
     * Options to resolve attribute from selected ID to text
     * @var array
     */
    protected $attributeOptions = null;

    /* @var Mage_Catalog_Model_Category[] $categories */
    protected $categories = array();

    /* @var bool $useConfigurableParentImages */
    protected $useConfigurableParentImages = null;

    /**
     * Categories to skip because they have been disabled
     * or are set to be ignored
     * @var int[]
     */
    protected $skipCategories = null;

    /**
     * Inflate an API object from a product object
     *
     * @param Mage_Catalog_Model_Product $product       Product
     * @param int                        $storeId       Magento store ID
     * @param bool                       $includeExtras Retrieve all information
     *
     * @return array
     */
    public function getProductEntity(
        Mage_Catalog_Model_Product $product, $storeId, $includeExtras = true
    ) {
        $result = array();

        $result['entity_id'] = $product->getEntityId();
        $result['sku'] = $product->getSku();
        $result['name'] = $product->getName();
        $result['price'] = $product->getPrice();
        $result['special_price'] = $product->getSpecialPrice();
        $result['special_from_date'] = $product->getSpecialFromDate();
        $result['special_to_date'] = $product->getSpecialToDate();
        $result['cost'] = $product->getCost();
        $result['description'] = $product->getDescription();
        $result['short_description'] = $product->getShortDescription();
        $result['weight'] = $product->getWeight();
        if ($product->isVisibleInSiteVisibility()) {
            $result['url_path'] = $this->getProductUrlWithCache($product);
        }

        $parentProduct = $this->getParentProduct($product);
        if ($parentProduct != null) {
            $parentData = $this->getParentData($product, $parentProduct, $includeExtras);
            $result = $parentData + $result;
        }

        if (!isset($result['purchasable'])) {
            $result['purchasable'] = $this->isPurchasable($product);
        }

        $images = $this->getProductImages($product);
        if (isset($images['image'])) {
            $result['image'] = $images['image'];
        }
        if (isset($images['small_image'])) {
            $result['small_image'] = $images['small_image'];
        }
        if (isset($images['thumbnail'])) {
            $result['thumbnail'] = $images['thumbnail'];
        }

        if ($includeExtras) {
            $metas = $this->getMetaData($storeId, $product, $parentProduct);
            if ($metas != null) {
                $result = $result + $metas;
            }
            
            $extras = $this->getProductBrandAndCategory($storeId, $product, $parentProduct);
            $result = $extras + $result;
            
            $result['attributes'] = $this->getProductAttributes($product);

            $result['in_stock'] = $product->isAvailable() ? "true" : "false";

            /* @var Mage_Cataloginventory_Model_Stock_Item $stockItem */
            $stockItem = $product->getStockItem();
            if ($stockItem) {
                $result['qty_on_hand'] = $stockItem->getStockQty();
            }

            // Related Products
            $result['links'] = $this->getProductLinks($product);
        }

        $result['type'] = $product->getTypeId();

        return $result;
    }

    /**
     * Retrieve product information from a quote item object
     *
     * @param Mage_Sales_Model_Quote_Item $item           Quote item
     * @param array                       $additionalInfo Information to return
     *
     * @return Varien_Object
     */
    public function getProductInformationFromQuoteItem(
        Mage_Sales_Model_Quote_Item $item,
        $additionalInfo = array()
    ) {
        $children = $item->getChildren();
        return $this->getProductInformationWork(
            $item, $additionalInfo, count($children) > 0, $children
        );
    }

    /**
     * Retrieve product information from an order item object
     *
     * @param Mage_Sales_Model_Order_Item $item           Order item
     * @param array                       $additionalInfo Information to return
     *
     * @return Varien_Object
     */
    public function getProductInformationFromOrderItem(
        Mage_Sales_Model_Order_Item $item, $additionalInfo = array()
    ) {
        return $this->getProductInformationWork(
            $item, $additionalInfo,
            $item->getHasChildren(), $item->getChildrenItems()
        );
    }

    /**
     * Retrieve the relative product URL
     *
     * @param Mage_Catalog_Model_Product $product Product
     *
     * @return string
     */
    public function getProductUrl(Mage_Catalog_Model_Product $product)
    {
        /* @var Mage_Core_Model_Url $urlParser */
        $urlParser = Mage::getSingleton('core/url');

        $urlParser->parseUrl($product->getProductUrl());
        return substr($urlParser->getPath(), 1);
    }

    /**
     * Returns the image URL
     *
     * @param Mage_Catalog_Model_Product $product Product
     *
     * @return string
     */
    public function getProductImage(Mage_Catalog_Model_Product $product)
    {
        $images = $this->getProductImages($product);

        if (isset($images['thumbnail'])) {
            return $images['thumbnail'];
        }
        if (isset($images['small_image'])) {
            return $images['small_image'];
        }
        if (isset($images['image'])) {
            return $images['image'];
        }

        return null;
    }

    public function setAttributeOptions($withAttributes, $options)
    {
        $this->retrieveAttributes = $withAttributes;
        $this->attributeOptions = $options;
    }
    
    protected function getParentData($product, $parentProduct, $includeExtras)
    {
        $result = array();
        
        $result['parent_id'] = $parentProduct->getEntityId();
        $result['parent_sku'] = $parentProduct->getSku();

        if (!$product->isVisibleInSiteVisibility()) {
            $result['name'] = $parentProduct->getName();

            if ($parentProduct->isVisibleInSiteVisibility()) {
                $result['url_path']
                    = $this->getProductUrlWithCache($parentProduct);
            }
        }

        if ($includeExtras && $this->isConfigurableProduct($parentProduct)) {
            $result['purchasable']
                = $this->isPurchasable($product, $parentProduct);

            /* @var Mage_Catalog_Model_Product_Type_Configurable $typeInst */
            $typeInst = $parentProduct->getTypeInstance(true);
            $attributes = $typeInst
                ->getUsedProductAttributes($parentProduct);

            /* @var Mage_Eav_Model_Entity_Attribute_Abstract $attribute */
            foreach ($attributes as $attribute) {
                if (!array_key_exists('configurable_attributes', $result)) {
                    $result['configurable_attributes'] = array();
                }

                $result['configurable_attributes'][]
                    = array('attribute_name' => $attribute->getAttributeCode());
            }
        }
        
        return $result;
    }
    
    protected function getProductBrandAndCategory($storeId, $product, $parentProduct)
    {
        $result = array();

        // Brand and Category
        $brandCatProduct = $product;
        if ($parentProduct && !$product->isVisibleInSiteVisibility()) {
            $brandCatProduct = $parentProduct;
        }
        $setSettings = $this->getProductAttributeSetSettings($brandCatProduct);

        if ($setSettings['brandAttribute'] != null) {
            $result['brand'] = $this->getProductAttribute(
                $brandCatProduct, $setSettings['brandAttribute']);
        }

        if ($setSettings['catFromMagento']) {
            $cats = $this->getCategoryInformation($storeId, $brandCatProduct);
            if (isset($cats['category'])) {
                $result['category'] = $cats['category'];
            }
            if (isset($cats['sub_category'])) {
                $result['sub_category'] = $cats['sub_category'];
            }
        } else if ($setSettings['catFromAttributes']) {
            if ($setSettings['categoryAttribute'] != null) {
                $result['category'] = $this->getProductAttribute(
                    $brandCatProduct, $setSettings['categoryAttribute']);
            }
            if ($setSettings['subcategoryAttribute'] != null) {
                $result['sub_category'] = $this->getProductAttribute(
                    $brandCatProduct, $setSettings['subcategoryAttribute']);
            }
        }
        
        return $result;
    }

    /**
     * Retrieve product information from an object with basic information
     *
     * @param mixed $item        Object with data
     * @param array $getInfo     Additional information to retrieve
     * @param bool  $hasChildren Whether the product has children
     * @param array $children    Array of product children
     *
     * @return Varien_Object
     */
    protected function getProductInformationWork(
        $item, $getInfo, $hasChildren, $children
    ) {
        $getProduct = in_array('product', $getInfo);
        $getImage = in_array('image_url', $getInfo);
        $getLink = in_array('product_url', $getInfo);

        $result = new Varien_Object();

        $result->setProductId((int)$item->getProductId());
        $result->setIsConfigurable(false);
        $result->setIsBundle(false);
        $result->setSku($item->getSku());

        if ($this->isConfigurableType($item->getProductType()) && $hasChildren) {
            $result->setIsConfigurable(true);

            $result->setParentId($result->getProductId());
            $result->setProductId((int)$children[0]->getProductId());
        }

        if ($this->isBundleType($item->getProductType()) && $hasChildren) {
            $result->setIsBundle(true);

            $product = Mage::getModel('catalog/product')
                ->load($result->getProductId());
            $result->setSku($product->getSku());
            $result->setProduct($product);
        } else if ($getProduct || $getImage
            || ($getLink && !$result->getIsConfigurable())
        ) {
            $product = Mage::getModel('catalog/product')
                ->load($result->getProductId());

            $result->setProduct($product);
        }

        if ($getLink) {
            $linkProduct = $result->getProduct();
            if ($result->getIsConfigurable()) {
                $linkProduct = Mage::getModel('catalog/product')
                    ->load($result->getParentId());
            }

            $result->setProductUrl($this->getProductUrl($linkProduct));
        }

        if ($getImage) {
            $result->setImageUrl($this->getProductImage($result['product']));
        }

        return $result;
    }

    /**
     * Retrieve the product URL, with caching of the result for a request
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return string
     */
    protected function getProductUrlWithCache(Mage_Catalog_Model_Product $product)
    {
        $productId = $product->getEntityId();

        if (!isset($this->urlsById[$productId])) {
            $this->urlsById[$productId] = $this->getProductUrl($product);
        }

        return $this->urlsById[$productId];
    }

    /**
     * Retrieve an array of all available images for a product
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    protected function getProductImages(Mage_Catalog_Model_Product $product)
    {
        $parent = $this->getParentProduct($product);
        $parentIsConfigurable = $parent && $this->isConfigurableProduct($parent);
        if ($this->useConfigurableParentImages == null) {
            $confSetting = Mage::getStoreConfig(
                Mage_Checkout_Block_Cart_Item_Renderer_Configurable
                ::CONFIGURABLE_PRODUCT_IMAGE
            );
            $wanted = Mage_Checkout_Block_Cart_Item_Renderer_Configurable
                ::USE_PARENT_IMAGE;

            $this->useConfigurableParentImages = $confSetting == $wanted;
        }

        $none = 'no_selection';

        $image = null;
        $smallImage = null;
        $thumbnail = null;

        if ($parentIsConfigurable && $this->useConfigurableParentImages) {
            $image = $parent->getImage();
            $smallImage = $parent->getSmallImage();
            $thumbnail = $parent->getThumbnail();
        } else {
            $image = $product->getImage();
            if ($parent && (!$image || $image == $none)) {
                $image = $parent->getImage();
            }

            $smallImage = $product->getSmallImage();
            if ($parent && (!$smallImage || $smallImage == $none)) {
                $smallImage = $parent->getSmallImage();
            }

            $thumbnail = $product->getThumbnail();
            if ($parent && (!$thumbnail || $thumbnail == $none)) {
                $thumbnail = $parent->getThumbnail();
            }
        }

        $result = array();
        if ($image && $image != $none) {
            $result['image'] = $image;
        }
        if ($smallImage && $smallImage != $none) {
            $result['small_image'] = $smallImage;
        }
        if ($thumbnail && $thumbnail != $none) {
            $result['thumbnail'] = $thumbnail;
        }

        return $result;
    }

    /**
     * Get the parent of a configurable product
     *
     * @param Mage_Catalog_Model_Product $product Configurable product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getParentProduct(Mage_Catalog_Model_Product $product)
    {
        if ($product->hasParentProduct()) {
            return $product->getParentProduct();
        }

        /* @var Mage_Catalog_Model_Product_Type_Configurable $confProductModel */
        $confProductModel = Mage::getModel('catalog/product_type_configurable');

        $parentIds = $confProductModel
            ->getParentIdsByChild($product->getEntityId());

        if (is_array($parentIds) && count($parentIds) > 0) {
            $parentId = $parentIds[0];
            if ($parentId != null) {
                if (!array_key_exists($parentId, $this->parentsById)) {
                    /* @var Mage_Catalog_Model_Product $parent */
                    $parent = Mage::getModel('catalog/product')
                        ->load($parentId);

                    $this->parentsById[$parentId] = $parent;
                }
                return $this->parentsById[$parentId];
            }
        }

        return null;
    }

    protected function getProductAttributes(Mage_Catalog_Model_Product $product)
    {
        if (!$this->retrieveAttributes) {
            return null;
        }

        $result = array();

        $allAttributes = array_keys($product->getData());

        $hasParent = $product->hasParentProduct();
        if ($hasParent) {
            $parent = $product->getParentProduct();
            $allAttributes = array_unique(array_merge(
                $allAttributes,
                array_keys($parent->getData())));
        }

        $productAttributes = $this->getAttributeValues($product, $allAttributes);

        if ($hasParent) {
            $parentAttributes = $this->getAttributeValues($parent, $allAttributes);
        }

        foreach($allAttributes as $name) {
            $key = 'value';
            $value = $productAttributes[$name];
            if (is_array($value)) {
                $key = 'values';
            }

            $pkey = 'parent_value';
            $pvalue = null;
            if ($hasParent) {
                $pvalue = $parentAttributes[$name];
                if (is_array($pvalue)) {
                    $pkey = 'parent_values';
                }
            }

            if (($value !== null && $value !== "") || ($pvalue !== null && $pvalue !== "")) {
                $attr = array(
                    'attribute_name' => $name,
                    $key => $value,
                    $pkey => $pvalue
                );
                $result[] = $attr;
            }
        }

        return $result;
    }

    protected function getProductAttribute(Mage_Catalog_Model_Product $product, $attributeName)
    {
        if (!$this->retrieveAttributes) {
            return $product
                ->getAttributeText($attributeName);
        }
        else {
            $value = $this->getAttributeValue($product, $attributeName);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            return $value;
        }
    }

    protected function getAttributeValues(Mage_Catalog_Model_Product $product, $attributeNames)
    {
        $result = array();
        foreach($attributeNames as $name) {
            $value = $this->getAttributeValue($product, $name);
            $result[$name] = $value;
        }
        return $result;
    }

    protected function getAttributeValue(Mage_Catalog_Model_Product $product, $attributeName)
    {
        $value = $product->getData($attributeName);
        if (is_object($value)) {
            return null;
        }

        if (array_key_exists($attributeName, $this->attributeOptions)) {
            $options = $this->attributeOptions[$attributeName];

            if (array_key_exists('options', $options)) {
                if ($options['multiple']) {
                    $selects = array();

                    $parts = explode(',', $value);
                    foreach($parts as $part) {
                        if (array_key_exists($part, $options['options'])) {
                            $selects[] = $options['options'][$part];
                        }
                    }

                    if (count($selects) > 0) {
                        $value = $selects;
                    }
                } else if (array_key_exists($value, $options['options'])) {
                    $value = $options['options'][$value];
                }
            }
        }

        if (is_array($value) && sizeof($value) > 0) {
            $arrValue = array();
            foreach($value as $key => $item) {
                if (is_numeric($key) && !is_object($item)) {
                    $arrValue[] = $item;
                }
            }
            if (sizeof($arrValue) == 0) {
                $arrValue = null;
            }
            $value = $arrValue;
        }

        return $value;
    }

    /**
     * Retrieve purchasable value to be returned by the API
     *
     * @param Mage_Catalog_Model_Product $product Current product
     * @param Mage_Catalog_Model_Product $parent  Parent product
     *
     * @return string
     */
    protected function isPurchasable(
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parent = null
    ) {
        if (!$this->isEnabled($product)) {
            $result = false;
        } else if ($parent == null) {
            $result = $this->isVisible($product);
        } else {
            $result = $this->isEnabled($parent) && $this->isVisible($parent);
        }

        return $result ? "true" : "false";
    }

    /**
     * Returns whether the product is enabled in the catalog
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    protected function isEnabled(Mage_Catalog_Model_Product $product)
    {
        return $product->getStatus()
            == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    /**
     * Retrieve whether the product is purchasable according to the configuration
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    protected function isVisible(Mage_Catalog_Model_Product $product)
    {
        /* @var Listrak_Remarketing_Model_Product_Purchasable_Visibility $visModel */
        $visModel = Mage::getSingleton(
            'listrak/product_purchasable_visibility'
        );

        return $visModel->isProductPurchasable($product);
    }

    /**
     * Retrieve the attribute settings for a product
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    protected function getProductAttributeSetSettings(
        Mage_Catalog_Model_Product $product
    ) {
        if ($this->attributeSets == null) {
            $this->attributeSets = array(0 => array(
                //default values
                'brandAttribute' => null,
                'catFromMagento' => true,
                'catFromAttributes' => false,
                'categoryAttribute' => null,
                'subcategoryAttribute' => null
            ));

            /* @var Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map_Collection $settings */
            $settings = Mage::getModel('listrak/product_attribute_set_map')
                ->getCollection();

            /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $set */
            foreach ($settings as $set) {
                $this->attributeSets[$set->getAttributeSetId()] = array(
                    'brandAttribute' =>
                        $set->getBrandAttributeCode(),
                    'catFromMagento' =>
                        $set->finalCategoriesSource() == 'default',
                    'catFromAttributes' =>
                        $set->finalCategoriesSource() == 'attributes',
                    'categoryAttribute' =>
                        $set->getCategoryAttributeCode(),
                    'subcategoryAttribute' =>
                        $set->getSubcategoryAttributeCode()
                );
            }
        }

        return array_key_exists($product->getAttributeSetId(), $this->attributeSets)
            ? $this->attributeSets[$product->getAttributeSetId()]
            : $this->attributeSets[0];
    }

    /**
     * Retrieve the category and subcategory for a product
     *
     * @param int                        $storeId Magento store ID
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    protected function getCategoryInformation(
        $storeId, Mage_Catalog_Model_Product $product
    ) {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        $rootLevel = $helper->getCategoryRootIdForStore($storeId);
        $rootPath = array(1);
        if ($rootLevel) {
            $rootPath[] = $rootLevel;
        }

        $categoryLevel = $helper->getCategoryLevel();

        if ($this->skipCategories == null) {
            $this->skipCategories = array_unique(
                array_merge(
                    $helper->getInactiveCategories(),
                    $helper->getCategoriesToSkip()
                )
            );
        }

        /* @var Mage_Catalog_Model_Resource_Category_Collection $categories */
        $categories = $product->getCategoryCollection();
        $path = $this->getFirstPathByPosition(
            $categories, $categoryLevel + 1, $rootPath
        );

        $result = array();
        if (isset($path[$categoryLevel - 1])) {
            $result['category']
                = $this->getCategoryField($path[$categoryLevel - 1], 'name');
        }
        if (isset($path[$categoryLevel])) {
            $result['sub_category']
                = $this->getCategoryField($path[$categoryLevel], 'name');
        }

        return $result;
    }

    /**
     * Retrieve the first active category
     *
     * @param mixed $categoryCollection All product categories
     * @param int   $maxLevel           Defines the depth of search
     * @param int[] $underPath          Partial, known good path
     *
     * @return array
     */
    protected function getFirstPathByPosition(
        $categoryCollection, $maxLevel, $underPath
    ) {
        $underPathSize = sizeof($underPath);

        if ($underPathSize >= $maxLevel) {
            return $underPath;
        }

        $nextCategory = array();

        /* @var Mage_Catalog_Model_Category $category */
        foreach ($categoryCollection as $category) {
            $pathIds = $category->getPathIds();

            if (sizeof(array_intersect($pathIds, $this->skipCategories)) > 0) {
                // the category tree contains a category
                // that we want skipped or is not active
                continue;
            }

            if (sizeof($pathIds) > $underPathSize
                && !in_array($pathIds[$underPathSize], $nextCategory)
            ) {
                $isUnderPath = true;
                for ($i = 0; $i < $underPathSize; $i++) {
                    if ($pathIds[$i] != $underPath[$i]) {
                        $isUnderPath = false;
                        break;
                    }
                }

                if ($isUnderPath) {
                    $nextCategory[] = $pathIds[$underPathSize];
                }
            }
        }

        if (sizeof($nextCategory) == 0) {
            return $underPath;
        }

        $winnerPath = array();
        $winnerPathPosition = 0;
        foreach ($nextCategory as $categoryId) {
            $testPath = $underPath;
            $testPath[] = $categoryId;

            $testPathPosition = $this->getCategoryField(
                $categoryId, 'position'
            );

            if (sizeof($winnerPath) == 0
                || $winnerPathPosition > $testPathPosition
            ) {
                $winnerPath = $testPath;
                $winnerPathPosition = $testPathPosition;
            }
        }

        return $this->getFirstPathByPosition(
            $categoryCollection, $maxLevel, $winnerPath
        );
    }

    /**
     * Retrieve data from a category
     *
     * @param int    $categoryId Category ID
     * @param string $field      Category field/attribute by name
     *
     * @return mixed|null
     */
    protected function getCategoryField($categoryId, $field)
    {
        $category = $this->getCategory($categoryId);
        if ($category != null) {
            return $category->getData($field);
        }

        return null;
    }

    /**
     * Retrieve a category by ID
     *
     * @param int $categoryId Category ID
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function getCategory($categoryId)
    {
        if (array_key_exists($categoryId, $this->categories)) {
            return $this->categories[$categoryId];
        } else {
            $category = Mage::getModel('catalog/category');

            $category->load($categoryId);
            if ($category != null) {
                $this->categories[$categoryId] = $category;
                return $category;
            }
        }

        return null;
    }

    /**
     * Get all linked products
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    protected function getProductLinks(Mage_Catalog_Model_Product $product)
    {
        if (Mage::getStoreConfig(
            'remarketing/productcategories/product_links'
        ) != '1') {
            return null;
        }

        static $productTable = null;
        if ($productTable == null) {
            // this is done because a query shows up in MySQL
            // with 'SET GLOBAL SQL_MODE = ''; SET NAMES utf8;'
            // that is very costly in a loop

            /* @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getModel('core/resource');

            $productTable = $resource->getTableName('catalog/product');
        }

        static $productAttrTable = null;
        if ($productAttrTable == null) {
            /* @var Mage_Catalog_Model_Product_Link $linkModel */
            $linkModel = Mage::getModel('catalog/product_link');

            $productAttrTable = $linkModel->getAttributeTypeTable('int');
        }

        $linkTypes = $this->getLinkTypes();

        /* @var Mage_Catalog_Model_Resource_Product_Link_Collection $links */
        $links = Mage::getModel('catalog/product_link')
            ->getCollection();

        $select = $links->getSelect();

        $select->where('main_table.product_id = ?', $product->getId())
            ->where('main_table.product_id <> main_table.linked_product_id')
            ->where('main_table.link_type_id IN (?)', array_keys($linkTypes));

        $select->join(
            array('product' => $productTable),
            'main_table.linked_product_id = product.entity_id',
            'sku'
        );

        $positionJoinOn = array();
        foreach ($linkTypes as $linkTypeId => $linkType) {
            if ($linkType['positionAttributeId'] != null) {
                $adptr = $select->getAdapter();
                $joinStmt
                    = $adptr->quoteInto('main_table.link_type_id  = ?', $linkTypeId)
                    . ' AND '
                    . $adptr->quoteInto(
                        'attributes.product_link_attribute_id = ?',
                        $linkType['positionAttributeId']
                    );

                $positionJoinOn[] = $joinStmt;
            }
        }

        $joinCond
            = 'main_table.link_id = attributes.link_id AND (('
            . implode(') OR (', $positionJoinOn)
            . '))';
        $select->joinLeft(
            array('attributes' => $productAttrTable),
            $joinCond,
            array('position' => 'value')
        );

        $result = array();
        foreach ($links as $link) {
            $result[] = array(
                'link_type' => $linkTypes[$link->getLinkTypeId()]['name'],
                'sku' => $link->getSku(),
                'position' => $link->getPosition()
            );
        }

        return $result;
    }

    /**
     * Retrieve product link types
     *
     * @return array
     */
    protected function getLinkTypes()
    {
        static $types = null;

        if ($types == null) {
            $allLinks = array(
                Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL =>
                    array('name' => 'up_sell', 'positionAttributeId' => null),
                Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL =>
                    array('name' => 'cross_sell', 'positionAttributeId' => null),
                Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED =>
                    array('name' => 'related', 'positionAttributeId' => null),
                Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED =>
                    array('name' => 'grouped', 'positionAttributeId' => null)
            );

            foreach ($allLinks as $linkId => &$link) {
                /* @var Mage_Catalog_Model_Product_Link $linkModel */
                $linkModel = Mage::getModel('catalog/product_link');

                $linkAttributes = $linkModel->setLinkTypeId($linkId)
                    ->getAttributes();

                foreach ($linkAttributes as $attribute) {
                    if ($attribute['code'] == 'position'
                        && $attribute['type'] == 'int'
                    ) {
                        $link['positionAttributeId'] = $attribute['id'];
                        break;
                    }
                }
            }

            $types = $allLinks;
        }

        return $types;
    }

    /**
     * Return whether the product type is configurable
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    protected function isConfigurableProduct(Mage_Catalog_Model_Product $product)
    {
        return $this->isConfigurableType($product->getTypeId());
    }

    /**
     * Return whether the product type passed in is configurable
     *
     * @param string $type Product type
     *
     * @return bool
     */
    protected function isConfigurableType($type)
    {
        return Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $type;
    }

    /**
     * Return whether the product type passed in is bundle
     *
     * @param string $type Product type
     *
     * @return bool
     */
    protected function isBundleType($type)
    {
        return Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $type;
    }

    /**
     * Retrieve the meta data for the current product from the meta provider
     *
     * @param int                        $storeId       Magento store ID
     * @param Mage_Catalog_Model_Product $product       Current Product
     * @param Mage_Catalog_Model_Product $parentProduct Parent Product
     *
     * @return array|null
     */
    protected function getMetaData(
        $storeId,
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parentProduct = null
    ) {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        try {
            $provider = $helper->getMetaDataProvider();
            if ($provider) {
                return $provider->product($storeId, $product, $parentProduct);
            }
        }
        catch(Exception $e) {
            $helper->generateAndLogException(
                'Exception retrieving product meta data', $e
            );
        }

        return null;
    }
}
