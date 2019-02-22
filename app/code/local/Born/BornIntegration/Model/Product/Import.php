<?php
class Born_BornIntegration_Model_Product_Import extends Born_BornIntegration_Model_Import{
    public function productUpdate() {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Product_Import_'.date('Ymd').'.log');
            if($this->helper->isEnabled()) {
            $remote = $this->connect();
            }
    }
}