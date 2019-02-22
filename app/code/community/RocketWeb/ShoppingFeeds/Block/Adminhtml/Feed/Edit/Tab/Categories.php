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
 * @copyright  Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Edit_Tab_Categories
    extends RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Edit_Tab_Abstract
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('rocketshoppingfeeds');
        $feed = Mage::registry('rocketshoppingfeeds_feed');

        $directives = $feed->getData('directives');
        $taxonomyDirective = array('label' => 'Taxonomy by Magento Category');
        foreach ($directives as $name => $directive) {
            if (strpos($name, 'category_by_category') !== false) {
                $taxonomyDirective = $directive;
            }
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $note = new Varien_Data_Form_Element_Note(array(
            'text' => '<ul class="messages"><li class="notice-msg"><ul><li>'
                . $helper->__('Category definitions here, apply to the directive called <strong style="color:green;">Taxonomy By Magento Category</strong> and <strong style="color:green;">Type By Magento Category</strong>.')
                . '</li></ul></li></ul>'));
        $form->addElement($note);

        $fieldset = $form->addFieldset('categories', array('legend' => Mage::helper('rocketshoppingfeeds')->__('Categories Map')));
        $this->setFieldset($fieldset);

        $this->addField('categories_locale', 'select', array(
            'name'      => 'config[categories_locale]',
            'label'     => $helper->__('Feed Localization'),
            'required'  => true,
            'values'    => Mage::getModel('rocketshoppingfeeds/locale')->toArray(),
            'after_element_html' => '<p class="note" style="width:450px;">'. $helper->__('Changing the language of your feed affects how Apparels are matched using Google taxonomies. Your products should also be in the same language. Refer to \'Google Category of the Item\' attribute. This setting does not affect price formatting, assure proper store language for that.'). '</p>'
        ));

        $this->addField('categories_include_all_products', 'select', array(
            'name'      => 'config[categories_include_all_products]',
            'label'     => $helper->__('Include products w/o category'),
            'required'  => true,
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'after_element_html' => '<p class="note" style="width:450px;">'. $helper->__('If enabled, products that do not belog to a category will be added to the feed. Note that the taxonomy will be missing in this case, so you can capture that in a Replace Empty rule under Filters.'). '</p>'
        ));

        $this->addField('categories_sort_mode', 'select', array(
            'name'      => 'config[categories_sort_mode]',
            'label'     => $helper->__('Categories priority mode'),
            'required'  => true,
            'values'    => Mage::getModel('rocketshoppingfeeds/source_category_mode')->toOptionArray(),
            'after_element_html' => '<p class="note" style="width:450px;">'. $helper->__('If set to use priority of categories of the same level, deeper level categories are mateched first, than apply the priority of categories at the same level to detemine which one will be matched for a product with multiple categories.'). '</p>',
        ));

        $this->getFieldset()->addType('provider_taxonomy_by_category_type', Mage::getConfig()->getBlockClassName('rocketshoppingfeeds/adminhtml_form_element_category_fill'));
        $this->addField('categories_provider_taxonomy_by_category', 'provider_taxonomy_by_category_type', array(
            'name'      => 'config[categories_provider_taxonomy_by_category]',
            'label'     => $taxonomyDirective['label'],
            'description' => isset($taxonomyDirective['description']) ? $taxonomyDirective['description'] : ''
        ));

        $form->setValues($feed->getConfig());
        return parent::_prepareForm();
    }
}
