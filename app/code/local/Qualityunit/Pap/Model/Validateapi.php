<?php
class Qualityunit_Pap_Model_Validateapi extends Mage_Core_Model_Config_Data {

    public function save() {
        // validation here... try to connect to PAP and throw error if problem occurred
        try {
            $config = Mage::getSingleton('pap/config');
            //$config->includePapAPI();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('An error occurred: '.$e);
            return;
        }

        $server = str_replace('https', 'http', $this->getValue());
        $server = str_replace('http://', '', $server);
        if (substr($server,-1) == '/') {
            $server = substr($server,0,-1);
        }

        $url = 'http://'.$server.'/scripts/server.php';
        $username = $this->_data['groups']['api']['fields']['username']['value'];
        $password = $this->_data['groups']['api']['fields']['password']['value'];

        try {
            $session = Mage::getModel('pap/pap')->getSession($url, $username, $password);
            Mage::getSingleton('core/session')->addSuccess('API Connection tested successfully!');
        } catch (Qualityunit_Pap_Model_Exception $e) { // connection worked
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) { // connection did not work
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            try {
                $url = str_replace('http://', 'https://', $url);
                $session = Mage::getModel('pap/pap')->getSession($url, $username, $password);
                Mage::getSingleton('core/session')->addSuccess('API Connection tested successfully!');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        return parent::save(); // let's save it anyway
    }
}
