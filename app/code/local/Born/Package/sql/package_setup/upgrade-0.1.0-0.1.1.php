<?php

$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('aw_sarp2/subscription_type')}`
  ADD COLUMN `message` varchar(64) NOT NULL
");
$installer->endSetup();

 ?>