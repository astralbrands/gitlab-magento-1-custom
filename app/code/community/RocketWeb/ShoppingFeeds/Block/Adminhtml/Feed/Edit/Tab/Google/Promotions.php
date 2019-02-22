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
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @copyright  Copyright (c) 2015 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

/**
 * Class RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Edit_Tab_Google_Promotions
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Edit_Tab_Google_Promotions
    extends RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Edit_Tab_Abstract
{
    protected function _prepareForm()
    {
        $helper= Mage::helper('rocketshoppingfeeds');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $note = new Varien_Data_Form_Element_Note(array(
            'text' => 'Google Promotions settings'));
        $form->addElement($note);

        $fieldset = $form->addFieldset('google_promotions', array('legend' => $helper->__('Google Promotions')));
        $this->setFieldset($fieldset);

        $this->addField('google_promotions_mode', 'select', array(
            'name'      => 'config[google_promotions][mode]',
            'label'     => $helper->__('Enable Google Promotions'),
            'required'  => true,
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'after_element_html' => '<p class="note" style="width:450px;">'. $helper->__(''). '</p><br />'
        ));

        /**$groups = Mage::getResourceModel('customer/group_collection')
            ->load()
            ->toOptionArray();

        $this->addField('google_promotions_groups', 'multiselect', array(
            'name'      => 'config[google_promotions][groups]',
            'label'     => $helper->__('Select Customer groups'),
            'required'  => true,
            'values'    => $groups,
            'after_element_html' => '<p class="note" style="width:450px;">'. $helper->__('Google requires only Public Customer Groups (Default: NOT LOGGED IN)') . '</p>
                                    <p class="note" style="width:450px;">'. $helper->__('You need to save the feed to get the correct Cart Rules list bellow.') . '</p>
            '
        ));**/

        $this->getFieldset()->addType('google_promotions_grid', Mage::getConfig()->getBlockClassName('rocketshoppingfeeds/adminhtml_form_element_google_promotions'));
        $this->addField('google_promotions_grid', 'google_promotions_grid', array(
            'name'      => 'config[google_promotions]',
            'label'     => 'Google Promotions'
        ));

        /** Custom values set */
        $configs = Mage::registry('rocketshoppingfeeds_feed')->getConfig();
        $promotions = isset($configs['google_promotions']) ? $configs['google_promotions'] : array();
        $values = array_merge(
            $configs,
            array(
                'google_promotions_mode'    => isset($promotions['mode']) ? $promotions['mode'] : 0,
//                'google_promotions_groups'  => isset($promotions['groups']) ? $promotions['groups'] : array()
            )
        );

        $form->setValues($values);
        return parent::_prepareForm();
    }
}