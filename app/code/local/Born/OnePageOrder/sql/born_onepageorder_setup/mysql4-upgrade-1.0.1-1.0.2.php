<?php
$installer = $this;

$installer->startSetup();
$installer->run("
            DROP TABLE IF EXISTS `{$this->getTable('born_onepageorder/navigation')}`;
        ");
$installer->endSetup();
