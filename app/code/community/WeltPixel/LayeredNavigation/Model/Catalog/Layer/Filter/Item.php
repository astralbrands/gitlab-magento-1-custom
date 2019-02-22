<?php

class WeltPixel_LayeredNavigation_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item {

    public function getRemoveUrl() {
        $resetOption = $this->getResetoption();
        if (isset($resetOption) && (strlen($resetOption))):
            $query = array($this->getFilter()->getRequestVar() => $this->getResetoption());
        else:
            $query = array($this->getFilter()->getRequestVar() => $this->getFilter()->getResetValue());
        endif;

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;
        return Mage::getUrl('*/*/*', $params);
    }

    public function getUrl() {
        $newUrloption = $this->getNewurloption();
        if (isset($newUrloption) && (strlen($newUrloption))):
            if ($newUrloption == 'exclude') :
                $query = array(
                    $this->getFilter()->getRequestVar() => $this->getResetoption(),
                    Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
                );
            else:
                $query = array(
                    $this->getFilter()->getRequestVar() => $newUrloption,
                    Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
                );
            endif;
        else:
            $query = array(
                $this->getFilter()->getRequestVar() => $this->getValue(),
                Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
            );
        endif;

        return Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_query' => $query));
    }

}
