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
 * @copyright Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */

/**
 * Class RocketWeb_ShoppingFeeds_Block_Adminhtml_Catalog_Taxonomy_Category
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_Catalog_Google_Promotions
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Setting the template
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate('rocketshoppingfeeds/catalog/google/promotions.phtml');
    }

    /**
     * Fetch magento cart rules collection to display
     *
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function getMagentoCartRules()
    {
        return $this->getPromotionModel()->getMagentoCartRules();
    }

    /**
     * Fetch the promotion data from feed configuration
     * Before fetching it validates saved data and updates it if needed
     * (so no orphaned row data will exists)
     *
     * @param $key
     * @param string $default
     * @return string
     */
    public function getPromotionData($key, $default = '')
    {
        if (!$this->hasData('promotion')) {
            $this->getPromotionModel()->validateGooglePromotions();
            $this->setData('promotion', $this->getFeed()->getConfig('google_promotions'));
        }
        $data = $this->getData('promotion');
        return !empty($data[$key]) ? $data[$key] : $default;
    }

    /**
     * Merges the saved data with default data
     * Handles dates from timestamp/date to LOCALE date format
     *
     * @param Mage_SalesRule_Model_Rule $rule
     * @param array $row
     * @return array
     */
    public function getPromotionRow(Mage_SalesRule_Model_Rule $rule, $row = array())
    {
        $fromDate = isset($row['date']) && isset($row['date']['from']) ? $row['date']['from'] : '';
        $fromDate = $this->getPromotionModel()->prepareDate($rule->getFromDate(), $fromDate);
        $toDate = isset($row['date']) && isset($row['date']['to']) ? $row['date']['to'] : '';
        $toDate = $this->getPromotionModel()->prepareDate($rule->getToDate(), $toDate, $fromDate);

        if (!isset($row['date'])) {
            $row['date'] = array();
        }
        $row['date']['from'] = $fromDate;
        $row['date']['to'] = $toDate;

        if (!isset($row['display'])) {
            $row['display'] = array();
        }
        if (isset($row['display']['from'])) {
            $row['display']['from'] = $this->getPromotionModel()->prepareDate('', $row['display']['from']);
        } else {
            $row['display']['from'] = $fromDate;
        }
        if (isset($row['display']['to'])) {
            $row['display']['to'] = $this->getPromotionModel()->prepareDate('', $row['display']['to']);
        } else {
            $row['display']['to'] = $toDate;
        }

        $default = array(
            'include' => 0,
            'title' => $rule->getName(),
            'date' => array(
                'from' => $fromDate,
                'to' => $toDate
            ),
            'display' => array(
                'from' => $fromDate,
                'to' => $toDate
            )
        );
        return array_replace_recursive($default, $row);
    }

    /**
     * Checks if cart rule could be related to shipping and
     * coupon code is not set (which is not allowed by
     * Google Promotions Program Policies)
     *
     * @param Mage_SalesRule_Model_Rule $rule
     * @return bool
     */
    public function isShippingWithoutCoupon(Mage_SalesRule_Model_Rule $rule)
    {
        if (($rule->getApplyToShipping() || $rule->getSimpleFreeShipping()) && $rule->getCouponType() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Getter for Promotions model
     *
     * @return RocketWeb_ShoppingFeeds_Model_Provider_Google_Promotions
     */
    public function getPromotionModel()
    {
        return Mage::getSingleton('rocketshoppingfeeds/provider_google_promotions');
    }

    /**
     * Getter for feed
     *
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

    public function getCalendarDate()
    {
        return '%d/%m/%Y';
    }
}