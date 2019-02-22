<?php
$installer = $this;

$installer->run("
 
DROP TABLE IF EXISTS {$this->getTable('weltpixel_attributeimages/attributeimages')};
CREATE TABLE {$this->getTable('weltpixel_attributeimages/attributeimages')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `attribute_id` int(11) NOT NULL,
  `attribute_code` varchar(100) NOT NULL,
  `attribute_image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
 
$installer->endSetup();
