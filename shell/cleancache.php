<?php
umask(0);
ini_set('memory_limit','512M');
set_time_limit(0);
$base_path = dirname(dirname(__FILE__));
if(file_exists($base_path.'/app/Mage.php')) require $base_path.'/app/Mage.php';
else require '../../app/Mage.php';
// Init without cache so we get a fresh version
Mage::app('admin','store', array('global_ban_use_cache' => TRUE));
echo "Applying updates...\n";
Mage_Core_Model_Resource_Setup::applyAllUpdates();
Mage_Core_Model_Resource_Setup::applyAllDataUpdates();
echo "Done.\n";
// Now enable caching and save
Mage::getConfig()->getOptions()->setData('global_ban_use_cache', FALSE);
Mage::app()->baseInit(array()); // Re-init cache
Mage::getConfig()->loadModules()->loadDb()->saveCache();
echo "Saved config cache.\n";