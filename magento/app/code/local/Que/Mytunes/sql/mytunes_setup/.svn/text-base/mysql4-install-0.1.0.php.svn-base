<?php
/**
 * Database Schema migration setup
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */



$installer = $this;
/* @var $installer Que_Mytunes_Model_Mysql4_Resource_Setup */

$type = Que_Mytunes_Model_Product_Type::TYPE_MYTUNES;

$installer->startSetup();

// add table mytunes_albums
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('mytunes_albums')}`;
CREATE TABLE `{$installer->getTable('mytunes_albums')}` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `downloads` smallint(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`album_id`),
  UNIQUE KEY `UNIQUE_MYTUNES_ALBUMS_PRODUCT` (`product_id`),
  CONSTRAINT `FK_MYTUNES_ALBUMS_PRODUCT` FOREIGN KEY (`product_id`)
    REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

// add table mytunes_tracks
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('mytunes_tracks')}`;
CREATE TABLE `{$installer->getTable('mytunes_tracks')}` (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL,
  `sample_file_name` varchar(255) NOT NULL,
  `artist` varchar(255) DEFAULT NULL,
  `trackname` varchar(255) NOT NULL DEFAULT '',
  `track_number` smallint(3) NOT NULL DEFAULT '1',
  `single_download` tinyint(1) NOT NULL DEFAULT '1',
  `price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `downloads` smallint(4) unsigned DEFAULT NULL,
  `sample_start` varchar(8) DEFAULT NULL,
  `sample_end` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`track_id`),
  UNIQUE KEY `UNIQUE_MYTUNES_ALBUM_TRACK` (`album_id`,`track_number`),
  KEY `MYTUNES_TRACKS_ALBUM` (`album_id`),
  CONSTRAINT `FK_MYTUNES_TRACKS_ALBUM` FOREIGN KEY (`album_id`)
    REFERENCES `{$installer->getTable('mytunes_albums')}` (`album_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

// install entity attributes
$installer->installEntities();

// make these attributes applicable to mytunes products
$fieldList = array(
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'minimal_price',
    'cost',
    'tier_price',
    'tax_class_id'
);
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (!in_array($type, $applyTo)) {
        $applyTo[] = $type;
        $installer->updateAttribute('catalog_product', $field, 'apply_to', join(',', $applyTo));
    }
}

$installer->endSetup();
