<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Address validation source model
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Model_Source_Addressvalidation
{
    /**
     * Disabled
     */
    const DISABLED                = 0;

    /**
     * Enabled + Prevent order
     */
    const ENABLED_PREVENT_ORDER    = 1;

    /**
     * Enabled + Allow order
     */
    const ENABLED_ALLOW_ORDER    = 2;

    /**
     * Gets the list of address validation for the admin config dropdown
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::DISABLED,
                'label' => Mage::helper('avatax')->__('Disable')
            ),
            array(
                'value' => self::ENABLED_PREVENT_ORDER,
                'label' => Mage::helper('avatax')->__('Enable + Prevent Order')
            ),
            array(
                'value' => self::ENABLED_ALLOW_ORDER,
                'label' => Mage::helper('avatax')->__('Enable + Allow Order')
            )
        );
    }
}
