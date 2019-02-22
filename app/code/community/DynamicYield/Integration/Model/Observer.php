<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Model_Observer
 */
class DynamicYield_Integration_Model_Observer
{
    const HEAD_BLOCK_NAME = 'dynamicyield_integration/head';

    const API_BLOCK_NAME = 'dynamicyield_integration/events';

    const DYI_EVENT_HEADER = 'Dyi-Event-Data';

    const DYI_CRON_JOB = 'dyi_cron_validator';

    protected $couponCode = '';

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_searchControllerBefore(Varien_Event_Observer $observer) {
        /**
         * @var $action Mage_Core_Controller_Varien_Action
         */
        $action = Mage::app()->getFrontController()->getAction();
        $event = Mage::getModel('dynamicyield_integration/event_search');
        $event->setSearchQuery($action->getRequest()->get('q'));

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_addedToCart(Varien_Event_Observer $observer) {
        $product = $observer->getProduct();
        if($product->getTypeId() == "grouped" || $product->getTypeId() == "bundle") return;
        $event = Mage::getModel('dynamicyield_integration/event_addtocart');
        $event->setProduct($product);

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_removedFromCart(Varien_Event_Observer $observer) {
        $item = $observer->getQuoteItem();

        if($item->getProductType() == "grouped" || $item->getProductType() == "bundle") return;
        $event = Mage::getModel('dynamicyield_integration/event_removefromcart');
        $event->setCartItem($item);

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_emptyCart(Varien_Event_Observer $observer) {

        if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_updatePost')
        {
            if(Mage::app()->getRequest()->getParam('update_cart_action') == 'empty_cart')
            {
                $event = Mage::getModel('dynamicyield_integration/event_emptycart');
                $items = Mage::getSingleton('checkout/cart')->getQuote()->getAllItems();
                $visibleItems = Mage::getSingleton('checkout/cart')->getQuote()->getAllVisibleItems();
                $event->setCartItems($items);
                $event->setVisibleItems($visibleItems);
                $this->buildResponse($event);
            }
        }

    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_addedToWishlist(Varien_Event_Observer $observer) {
        $product = $observer->getProduct();
        if($product->getTypeId() == "grouped" || $product->getTypeId() == "bundle") return;
        $event = Mage::getModel('dynamicyield_integration/event_addtowishlist');
        $event->setProduct($product);

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_afterPlaceOrder(Varien_Event_Observer $observer) {
        /**
         * @var $order Mage_Sales_Model_Order
         */
        $order = $observer->getOrder();
        $event = Mage::getModel('dynamicyield_integration/event_purchase');
        $event->setOrder($order);

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_afterRegister(Varien_Event_Observer $observer) {
        $customer = $observer->getCustomer();
        $event = Mage::getModel('dynamicyield_integration/event_signup');
        $event->setCustomer($customer);

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_afterLogin(Varien_Event_Observer $observer) {
        $customer = $observer->getCustomer();
        $event = Mage::getModel('dynamicyield_integration/event_login');

        $event->setCustomer($customer);

        $this->buildResponse($event);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_validateSalesRule(Varien_Event_Observer $observer) {
        $quote = $observer->getQuote();
        $couponCode = $quote->getCouponCode();
        $session = Mage::getSingleton('checkout/session');
        $cState = array_replace(array('code' => NULL, 'state' => NULL), (array)$session->getData('dyi_coupon_state'));

        if ($cState['state'] == 'active' && $couponCode != $cState['code']) {
            $cState['state'] = 'added';
        }

        if ((!$cState['state'] || $cState['state'] == 'added') and !empty($couponCode)) {
            $event = Mage::getModel('dynamicyield_integration/event_addpromocode');
            $event->setQuote($quote);
            $this->buildResponse($event);
        }

        $cState['state'] = $couponCode ? 'active' : '';
        $cState['code'] = $couponCode;

        $session->setData('dyi_coupon_state', $cState);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function event_subscribedToNewsletter(Varien_Event_Observer $observer) {
        /**
         * @var $subscriber Mage_Newsletter_Model_Subscriber
         */
        $subscriber = $observer->getSubscriber();

        if ($subscriber->isSubscribed() and $subscriber->getIsStatusChanged()) {
            $event = Mage::getModel('dynamicyield_integration/event_subscribedtonewsletter');
            $event->setSubscriber($subscriber);
            $this->buildResponse($event);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function coreBlockAbstractPrepareLayoutBefore(Varien_Event_Observer $observer) {
        /**
         * @var $block Mage_Core_Block_Template
         */
        $block = $observer->getBlock();
        $layout = $block->getLayout();

        if ($block instanceof Mage_Page_Block_Html_Head) {
            $scriptHead = $layout->getBlock(static::HEAD_BLOCK_NAME);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addScripts(Varien_Event_Observer $observer) {
        /**
         * @var $block Mage_Core_Block_Template
         */
        $block = $observer->getBlock();
        $layout = $block->getLayout();

        if ($block instanceof Mage_Page_Block_Html_Head) {
            $block->addJs('dynamicyield/tracking.js');
        }
    }

    /**
     * Appends the custom HTML Block
     *
     * @param Varien_Event_Observer $observer
     */
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer) {
        /**
         * @var $block Mage_Core_Block_Template
         */
        $block = $observer->getBlock();
        $layout = $block->getLayout();
        $transport = $observer->getTransport();

        if ($block instanceof Mage_Page_Block_Html_Head) {
            $html = $transport->getHtml();

            $scriptHead = $layout->getBlock(static::HEAD_BLOCK_NAME);

            if($scriptHead) {
                $transport->setHtml($scriptHead->toHtml() . $html);
            }
        }
    }

    /**
     * TODO: Look in other options
     *
     * @param Varien_Event_Observer $observer
     */
    public function handleContext(Varien_Event_Observer $observer) {
        $action = $observer->getAction();

        $helper = Mage::helper('dynamicyield_integration');

        $storeId = Mage::app()->getStore()->getStoreId();

        $language = $helper->isMultiLanguage() ? (Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_LOCALE,$storeId) ?
                                                    (Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_SELECT,$storeId) ?
                                                        Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE_CUSTOM,$storeId) :
                                                            Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE,$storeId)) :
                                                                Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE,$storeId)) : null;

        $type = DynamicYield_Integration_Model_Context::CONTEXT_OTHER;
        $data = array();

        if ($action instanceof Mage_Catalog_CategoryController) {
            $type = DynamicYield_Integration_Model_Context::CONTEXT_CATEGORY;

            /**
             * @var $category Mage_Catalog_Model_Category
             */
            $category = Mage::registry('current_category');

            foreach (Mage::helper('dynamicyield_integration')->getParentCategories($category) as $child) {
                $data[] = $child->getName();
            }
        } else if ($action instanceof Mage_Catalog_ProductController) {
            $type = DynamicYield_Integration_Model_Context::CONTEXT_PRODUCT;

            /**
             * @var $product Mage_Catalog_Model_Product
             */
            $product = Mage::registry('current_product');
            if($product->getTypeId() != "grouped" && $product->getTypeId() != "bundle")
                $data[] = $helper->getRandomChild($product)->getSku();
        } else if ($action instanceof Mage_Checkout_CartController) {
            $type = DynamicYield_Integration_Model_Context::CONTEXT_CART;

            $cart = Mage::getModel('checkout/cart')->getQuote();
            $prepareItems = array();
            foreach ($cart->getAllItems() as $item) {

                /**
                 * Skip bundle and grouped products (out of scope)
                 */
                if($item->getProductType() == "bundle" || $item->getProductType() == "grouped" || isset($prepareItems[$item->getSku()])) {
                    continue;
                }

                $product = $item->getProduct();

                if(!$product || !Mage::helper('dynamicyield_integration')->validateSku($product)) {
                    continue;
                }

                $prepareItems[$item->getSku()] = $product->getSku();
            }

            foreach ($prepareItems as $prepareItem) {
                $data[] = $prepareItem;
            }

        } else if ($action instanceof Mage_Cms_IndexController) {
            // TODO: Check if its homepage
            $type = DynamicYield_Integration_Model_Context::CONTEXT_HOMEPAGE;
        }

        $layout = $observer->getLayout();

        $block = $layout->createBlock('dynamicyield_integration/head', static::HEAD_BLOCK_NAME);
        $block->setContext($type, $language, $data);
    }

    /**
     * @param DynamicYield_Integration_Model_Event_Abstract $event
     */
    protected function buildResponse(DynamicYield_Integration_Model_Event_Abstract $event) {
        $request = Mage::app()->getRequest();
        $response = Mage::app()->getResponse();
        $queue = Mage::getModel('dynamicyield_integration/queue');
        $event->build();

        if ($request->isAjax()) {
            $encoded = Mage::helper('core')->jsonEncode($event->toArray());
            $response->setHeader(static::DYI_EVENT_HEADER, $encoded);
        } else {
            $queue->addToQueue($event->toArray());
        }
    }

    /**
     * Check if cron is running
     */
    public function checkCron(Varien_Event_Observer $observer)
    {
        $syncRate = explode(',',Mage::helper('dynamicyield_integration')->getUpdateRate());
        $limit = intval($syncRate[0]) * intval($syncRate[1]);
        $lastExecutionTime = Mage::helper('dynamicyield_integration')->getLastExecutionTime(static::DYI_CRON_JOB);
        if ($lastExecutionTime === false) {
            Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('dynamicyield_integration')->__('DynamicYield Integration: Check if cron is configured correctly.'));
        } else {
            $timespan = Mage::helper('dynamicyield_integration')->dateDiff($lastExecutionTime)/60;
            if ($timespan > $limit) {
                Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('dynamicyield_integration')->__('DynamicYield Integration: Last execution time: %s minute(s) ago . Please check your settings and configuration!', round($timespan/60)));
            }
        }
    }

    /**
     * Add a warning message if there are skipped products in the product feed log
     *
     * @param Varien_Event_Observer $observer
     */
    public function productFeedWarnings(Varien_Event_Observer $observer)
    {
        $path = Mage::helper('dynamicyield_integration/feed')->getFeedProductLogFile();
        $file = Mage::getBaseDir('log') .DS. $path;

        if (file_exists($file) &&  0 != filesize($file) ) {
            Mage::getSingleton('adminhtml/session')->addWarning('DynamicYield Integration: Products missing mandatory attributes. Details: '. $path);
        }
    }
}
