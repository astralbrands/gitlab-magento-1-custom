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
 * Class DynamicYield_Integration_Model_Event_Subscribedtonewsletter
 */
class DynamicYield_Integration_Model_Event_Subscribedtonewsletter extends DynamicYield_Integration_Model_Event_Abstract
{
    /**
     * @var Mage_Newsletter_Model_Subscriber
     */
    protected $subscriber;

    /**
     * @return mixed
     */
    function getName() {
        return 'Newsletter Subscription';
    }

    /**
     * @return mixed
     */
    function getType() {
        return 'newsletter-subscription-v1';
    }

    /**
     * @return mixed
     */
    function getDefaultProperties() {
        return array('hashedEmail' => '');
    }

    /**
     * @return array
     */
    function generateProperties() {
        return array('hashedEmail' => hash('sha256', $this->subscriber->subscriber_email));
    }

    /**
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     */
    public function setSubscriber(Mage_Newsletter_Model_Subscriber $subscriber) {
        $this->subscriber = $subscriber;
    }
}
