<?php
class Born_BornIntegration_Model_Adminhtml_Source_Host
{
    /**
    * Options getter
    *
    * @return array
    */
    public function toOptionArray()
    {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Adminhtml_Source_Host_'.date('Ymd').'.log');
        return array(
            array('value'=>'', 'label'=>''),
            array('value' => 'ftp', 'label' => 'FTP'),
            array('value' => 'sftp', 'label' => 'SFTP'),

        );
    }
}
?>
