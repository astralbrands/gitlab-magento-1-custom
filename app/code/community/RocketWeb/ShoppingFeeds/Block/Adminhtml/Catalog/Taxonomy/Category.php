<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */

/**
 * Class RocketWeb_ShoppingFeeds_Block_Adminhtml_Catalog_Taxonomy_Category
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_Catalog_Taxonomy_Category
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_fieldValues = null;

    /**
     * Setting the template
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate('rocketshoppingfeeds/catalog/taxonomy/category.phtml');
        $this->isTaxonomyEnabled();
    }
    /**
     * Encode taxonomy values to JSON
     *
     * @return string
     */
    public function getJsonValues()
    {
        $values = $this->getFieldValues();
        return Mage::helper('core')->jsonEncode($values);
    }

    /**
    /**
     * Generates HTML output for all taxonomy mappings
     *
     * @return string
     */
    public function getTaxonomyMappings()
    {
        $feed = $this->getFeed();
        $rootId = Mage::helper('rocketshoppingfeeds')->getRootCategoryId($feed);
        $categories = Mage::helper('rocketshoppingfeeds')->getAllCategories($feed);

        $generatedRows = $this->_generateRows($categories, $rootId);
        return $generatedRows['html'];
    }

    /**
     * The internal html generator called by getTaxonomyMappings()
     *
     * @param array $categories
     * @param int $rootId
     * @param array $names
     * @return string
     */
    protected function _generateRows(array &$categories, $rootId = 0, $names = array())
    {
        // We set the pointer to the first item so we don't get stuck somewhere in the middle
        reset($categories);
        $html = '';
        $rowEnabled = false;
        $values = $this->getFieldValues($rootId);

        foreach ($categories as $id => $category) {
            if ($category['parent_id'] == $rootId || $category['entity_id'] == $rootId) {
                unset($categories[$id]);
                $catNames = $names;
                $catNames[] = $category['name'];

                $children = $this->_generateRows($categories, $id, $catNames);
                $row = $this->getLayout()->createBlock('rocketshoppingfeeds/adminhtml_catalog_taxonomy_category_row')
                    ->setCategory($category)
                    ->setChildren($children)
                    ->setNames($catNames)
                    ->setParent($this);
                $html .= $row->toHtml();

                if (!empty($children['html']) && $children['enabled']) {
                    // If child is enabled, the parent must be too!
                    $rowEnabled = true;
                } else if (!$rowEnabled) {
                    // IF child is not enabled, neither no siblings (yet)
                    $categoryValues = $this->getFieldValues($id);
                    if (!$categoryValues['d']) {
                        $rowEnabled = true;
                    }
                }

                reset($categories);
            }
        }

        return array(
            'html' => $this->getLayout()->createBlock('rocketshoppingfeeds/adminhtml_catalog_taxonomy_category_outer')
                ->setHtml($html)
                ->setValues($values)
                ->setEnabled($rowEnabled)
                ->setLevel(count($names))
                ->toHtml(),
            'enabled' => $rowEnabled
        );
    }

    /**
     * Returns the category-taxonomy values
     * i.e. category_id, value (taxonomy line)
     *
     * @param int $categoryId
     * @return array
     */
    public function getFieldValues($categoryId = 0)
    {
        // Cache the values
        if (!is_array($this->_fieldValues)) {
            $values = array();
            $feed = $this->getFeed();
            $configValues = $feed->getConfig('categories_provider_taxonomy_by_category');
            if (is_array($configValues)) {
                foreach ($configValues as $value) {
                    $values[$value['id']] = $value;
                }
            }
            $this->_fieldValues = $values;
        }
        // Return all values if no category provided
        if ($categoryId <= 0) {
            return $this->_fieldValues;
        }

        // New feed, has all categories enabled by default
        if (!$this->getFeed()->getId() || !isset($this->_fieldValues[$categoryId])) {
            return array('id' => $categoryId, 'tx'=> '', 'ty' => '', 'p' => 0, 'd' => 0);
        }

        if (!array_key_exists('d', $this->_fieldValues[$categoryId])) {
            $this->_fieldValues[$categoryId]['d'] = 0;
        }

        return $this->_fieldValues[$categoryId];
    }

    /**
     * Default values for changing category status (enabled/disabled)
     *
     * @return array
     */
    public function getJsStrings()
    {
        return array(
            'row_disabled'         => '<span class="icon-cancel red"> </span>',
            'row_enabled'          => '<span class="icon-check green"> </span>',
            'enable_all'           => 'Enable All',
            'disable_all'          => 'Disable All',
            'expand_all'           => 'Expand All',
            'collapse_all'         => 'Collapse All'
        );
    }

    /**
     * Autocomplete Url
     *
     * @return string
     */
    public function getAutocompleteUrl()
    {
        $feed = $this->getFeed();
        $params = array();
        if (!$feed->getId()) {
            $params['type'] = $feed->getType();
            $params['locale'] = $feed->getConfig('categories_locale');
        } else {
            $params['id'] = $feed->getId();
        }
        return Mage::helper('adminhtml')->getUrl('*/*/autocomplete', $params);
    }

    /**
     * Checks if the current feed type support taxonomy autocomplete
     *
     * @return bool
     */
    public function isTaxonomyEnabled()
    {
        if (is_null(Mage::registry('taxonomy_enabled'))) {
            $feed = $this->getFeed();
            $enabled = Mage::getSingleton('rocketshoppingfeeds/feed_taxonomy')->getProvider($feed)->isTaxonomyEnabled();
            Mage::register('taxonomy_enabled', $enabled);
        }
        return Mage::registry('taxonomy_enabled');
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Feed
     */
    public function getFeed()
    {
        return Mage::registry('rocketshoppingfeeds_feed');
    }

    /**
     * Modified _toHtml() to take care of adminhtml table
     *
     * @return string
     */
    public function _toHtml()
    {
        $element = $this->getElement();
        return '<td colspan="2" id="' . $element->getId() . '">' . parent::_toHtml() . '</td>';
    }
}
