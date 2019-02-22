<?php

class WeltPixel_LayeredNavigation_Helper_Positioning extends Mage_Core_Helper_Data {

    /**
     * 0 LEFT //default
     * 1 TOP
     * 2 RIGHT
     *
     * if mobile detection is enabled and request comes from a mobile device move to top! <- high priority
     */
    public function getPositioningXmlUpdate() {
        $xmlUpdate = '';
        $position = Mage::helper('weltpixel_layerednavigation')->getNavigationPosition();
        $moveToTop = Mage::helper('weltpixel_layerednavigation')->moveNavigationToTop();

        /**
         * Set to top if detection was set
         */
        if ($moveToTop) {
            $position = 1;
        }
        switch ($position) {
            case 1:
                $category = Mage::registry('current_category');
                if (!$category) {
                    $isAnchor = true;
                } else {
                   $isAnchor = $category->getData('is_anchor');
                }

                if ($isAnchor) {
                    $xmlUpdate = $this->_getTopLayoutUpdates();
                }
                break;
            case 2:
                $xmlUpdate = $this->_getRightLayoutUpdates();
                break;
            default:
                break;
        }

        return $xmlUpdate;
    }

    private function _getTopLayoutUpdates() {
        return '
                    <reference name="left">
                        <action method="unsetChild">
                            <name>catalog.leftnav</name>
                        </action>
                    </reference>
                    <reference name="content">
                        <action method="insert">
                            <child>catalog.leftnav</child>
                        </action>
                    </reference>
                    <reference name="catalog.leftnav">
                       <action method="setTemplate">
                           <template>weltpixel/layerednavigation/catalog/layer/topview.phtml</template>
                       </action>
                   </reference>
                   <reference name="left">
                        <action method="unsetChild">
                            <name>enterprisecatalog.leftnav</name>
                        </action>
                    </reference>
                    <reference name="content">
                        <action method="insert">
                            <child>enterprisecatalog.leftnav</child>
                        </action>
                    </reference>
                    <reference name="enterprisecatalog.leftnav">
                       <action method="setTemplate">
                           <template>weltpixel/layerednavigation/catalog/layer/topview.phtml</template>
                       </action>
                   </reference>

                    <reference name="left">
                        <action method="unsetChild">
                            <name>catalogsearch.leftnav</name>
                        </action>
                    </reference>
                    <reference name="content">
                        <action method="insert">
                            <child>catalogsearch.leftnav</child>
                        </action>
                    </reference>
                    <reference name="catalogsearch.leftnav">
                        <action method="setTemplate">
                            <template>weltpixel/layerednavigation/catalog/layer/topview.phtml</template>
                        </action>
                    </reference>
                    <reference name="left">
                        <action method="unsetChild">
                            <name>enterprisesearch.leftnav</name>
                        </action>
                    </reference>
                    <reference name="content">
                        <action method="insert">
                            <child>enterprisesearch.leftnav</child>
                        </action>
                    </reference>
                    <reference name="enterprisesearch.leftnav">
                        <action method="setTemplate">
                            <template>weltpixel/layerednavigation/catalog/layer/topview.phtml</template>
                        </action>
                    </reference>
                ';
    }

    private function _getRightLayoutUpdates() {
        return '
                    <reference name="left">
                        <action method="unsetChild">
                            <name>catalog.leftnav</name>
                        </action>
                    </reference>
                    <reference name="right">
                        <action method="insert">
                            <child>catalog.leftnav</child>
                        </action>
                    </reference>
                    <reference name="left">
                        <action method="unsetChild">
                            <name>enterprisecatalog.leftnav</name>
                        </action>
                    </reference>
                    <reference name="right">
                        <action method="insert">
                            <child>enterprisecatalog.leftnav</child>
                        </action>
                    </reference>


                    <reference name="left">
                        <action method="unsetChild">
                            <name>catalogsearch.leftnav</name>
                        </action>
                    </reference>
                    <reference name="right">
                        <action method="insert">
                            <child>catalogsearch.leftnav</child>
                        </action>
                    </reference>
                    <reference name="left">
                        <action method="unsetChild">
                            <name>enterprisesearch.leftnav</name>
                        </action>
                    </reference>
                    <reference name="right">
                        <action method="insert">
                            <child>enterprisesearch.leftnav</child>
                        </action>
                    </reference>
                ';
    }

}
