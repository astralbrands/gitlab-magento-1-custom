<?php

$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('aw_sarp2/subscription_type')}`
  ADD COLUMN `information` varchar(255) NOT NULL
");
$installer->endSetup();

 ?>