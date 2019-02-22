<?php 
class Born_Package_Model_Container_Page_Html_Professional extends Enterprise_PageCache_Model_Container_Abstract {
	protected function _getCacheId() {
    	$key = time();
        return 'CACHE_BORN_PACKAGE_PAGE_HTML_PROFESSIONAL' . md5($key);
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }
}