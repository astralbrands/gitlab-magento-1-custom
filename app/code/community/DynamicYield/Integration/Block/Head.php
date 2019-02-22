<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 */

/**
 * Class DynamicYield_Integration_Block_Head
 */
class DynamicYield_Integration_Block_Head extends Mage_Core_Block_Text
{
    /**
     * @return mixed
     */
    protected function _toHtml()
    {

        $helper = Mage::helper('dynamicyield_integration');
        $sectionId = $helper->getSectionId();

        if (!$sectionId) {
            $this->setText("");

            return parent::_toHtml();
        }

        $controllerName = Mage::app()->getFrontController()->getRequest()->getControllerName();

        $context = ($controllerName == 'category' || $controllerName == 'product') ? Mage::helper('core')->jsonEncode($controllerName) : Mage::helper('core')->jsonEncode(null);

        $selectors = Mage::helper('core')->jsonEncode($helper->getTrackingSelectors());

        $loadJquery = $helper->getLoadJquery();

        $baseUrl = Mage::getBaseUrl('js');

        if($loadJquery) {
        $this->addText("<script type=\"text/javascript\" src=\"{$baseUrl}dynamicyield/jquery/jquery-1.10.2.js\"></script>\n");
        $this->addText("<script type=\"text/javascript\" src=\"{$baseUrl}dynamicyield/jquery/noconflict.js\"></script>\n");
        }

        $this->addText("<script type=\"text/javascript\" src=\"//" . $helper->getCDN() . "/api/" . $sectionId . "/api_dynamic.js\"></script>\n");
        $this->addText("<script type=\"text/javascript\" src=\"//" . $helper->getCDN() . "/api/" . $sectionId . "/api_static.js\"></script>\n");
        $this->addText("<script type=\"text/javascript\">//<![CDATA[ 
var DY_SELECTORS = {$selectors};
var DY_PAGETYPE = {$context};
 //]]></script>");
        $this->addText("<script>var DY_CUSTOM_STRUCTURE = {\n");
        foreach ($helper->getTrackingStructure() as $code => $selector) {
            $this->addText("\"$code\":".$selector.",\n");
        }
        $this->addText("}\n</script>\n");

        return parent::_toHtml();
    }

    /**
     * @param $type
     * @param $language
     * @param array $data
     */
    public function setContext($type, $language, $data = array())
    {

        $context = array('type' => $type, 'lng' => $language);

        if (!empty($data) || $type == DynamicYield_Integration_Model_Context::CONTEXT_CART) {
            $context['data'] = $data;
        }

        $json = Mage::helper('core')->jsonEncode(array_filter($context,function($var){return !is_null($var);}));

        $this->addText('<script type="text/javascript">//<![CDATA[ 
 window.DY = window.DY || {}; DY.recommendationContext = ' . $json . ';
 //]]></script>', true);
        $this->addText("\n");
    }
}
