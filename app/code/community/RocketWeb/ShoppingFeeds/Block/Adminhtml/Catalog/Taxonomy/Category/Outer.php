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
 * Class RocketWeb_ShoppingFeeds_Block_Adminhtml_Catalog_Taxonomy_Category_Outer
 *
 * @method $this setHtml() setHtml(string $html)
 * @method string getHtml()
 * @method $this setValues() setValues(array $values)
 * @method array getValues()
 * @method $this setLevel() setLevel(int $level)
 * @method int getLevel()
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_Catalog_Taxonomy_Category_Outer
    extends Mage_Core_Block_Template
{
    /**
     * Setting the template
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate('rocketshoppingfeeds/catalog/taxonomy/outer.phtml');
    }


}