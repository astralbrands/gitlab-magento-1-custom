<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('born_cms_hooks')};
CREATE TABLE {$this->getTable('born_cms_hooks')} (
  `hook_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default 1,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`hook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('born_cms_hook_sections')};
CREATE TABLE {$this->getTable('born_cms_hook_sections')} (
  `section_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `hook_id` int(11) NOT NULL,
  `section_order` int(11) NOT NULL,
  `status` smallint(6) NOT NULL default 1,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
DROP TABLE IF EXISTS {$this->getTable('born_cms_hook_fields')};
CREATE TABLE {$this->getTable('born_cms_hook_fields')} (
  `field_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `hook_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `field_order` int(11) NOT NULL,
  `status` smallint(6) NOT NULL default 1,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('born_cms_hook_field_forms')};
CREATE TABLE {$this->getTable('born_cms_hook_field_forms')} (
  `form_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `hook_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `status` smallint(6) NOT NULL default 1,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('born_cms_hook_field_rows')};
CREATE TABLE {$this->getTable('born_cms_hook_field_rows')} (
  `row_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `hook_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `row_order` int(11) NOT NULL,
  `store_id` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default 1,
  `start_date` datetime NULL,
  `end_date` datetime NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('born_cms_hook_field_form_types')};
CREATE TABLE {$this->getTable('born_cms_hook_field_form_types')} (
  `type_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `element_order` int(11) NOT NULL,
  `hook_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('born_cms_hook_field_form_values')};
CREATE TABLE {$this->getTable('born_cms_hook_field_form_values')} (
  `value_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` TEXT NOT NULL,
  `hook_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `row_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup(); 