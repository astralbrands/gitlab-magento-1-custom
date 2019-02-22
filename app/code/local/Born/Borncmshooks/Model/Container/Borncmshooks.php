<?php

/**
 * Born.com
 *
 * PHP Version 5
 *
 * @category  Born
 * @package   Born_Borncmshooks
 * @author    Your Name <your.name@born.com>
 * @copyright 2012 born.com
 * @license   http://www.born.com/license.txt Your license
 * @link      N/A
 */

/**
 * Cache container
 *
 * @category Born
 * @package  Born_Borncmshooks
 * @author   Your Name <your.name@born.com>
 * @license  http://www.born.com/license.txt Your license
 * @link     N/A
 */
class Born_Borncmshooks_Model_Container_Borncmshooks extends Enterprise_PageCache_Model_Container_Abstract
{
   /**
     * Get container individual cache id
     *
     * Override to return false to cause the block to never get cached
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return false;
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_placeholder->getAttribute('block');
        $block = new $block;
        //Mage::log($block->getLayout(), null, 'cache.log');
        // only needed if the block uses a template
        $block->setTemplate($this->_placeholder->getAttribute('template'));

        return $block->toHtml();
    }

    /**
     * Generate placeholder content before application was initialized and
     * apply to page content if possible
     *
     * Override to enforce calling {@see _renderBlock()}
     *
     * @param string &$content The content
     *
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        return false;
    }
   
}