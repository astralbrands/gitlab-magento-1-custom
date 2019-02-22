<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

$this->startSetup();
$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('salesrule/rule');
$cols = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('auto_add_simple', $cols)) {
    $this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}`
    ADD COLUMN `ampromo_auto_add_simple` TINYINT(1) NOT NULL DEFAULT '0'");
}

$this->endSetup();