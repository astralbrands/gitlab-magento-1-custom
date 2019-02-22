<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Loginbgimage extends Mage_Adminhtml_Model_System_Config_Backend_Image
{
    const UPLOAD_DIR                = 'cleo';

    const UPLOAD_ROOT_TOKEN         = 'system/filesystem/media';

    protected $_maxFileSize         = 2048;

    protected function _getUploadDir()
    {
        $uploadDir  = $this->_appendScopeInfo(self::UPLOAD_DIR);
        $uploadRoot = $this->_getUploadRoot(self::UPLOAD_ROOT_TOKEN);
        $uploadDir  = $uploadRoot . DS . $uploadDir;
        return $uploadDir;
    }

    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    protected function _beforeSave()
    {
        $value       = $this->getValue();
        $deleteFlag  = (is_array($value) && !empty($value['delete']));
        $fileTmpName = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];

        if ($this->getOldValue() && ($fileTmpName || $deleteFlag)) {
            $io = new Varien_Io_File();
            $io->rm($this->_getUploadRoot(self::UPLOAD_ROOT_TOKEN) . DS . self::UPLOAD_DIR . DS . $this->getOldValue());
        }
        return parent::_beforeSave();
    }
}
