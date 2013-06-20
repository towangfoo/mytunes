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

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('mytunes_links_purchased')}`;
CREATE TABLE `{$installer->getTable('mytunes_links_purchased')}` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `order_item_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `number_of_downloads_bought` int(10) NOT NULL,
  `number_of_downloads_used` int(10) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`link_id`),
  CONSTRAINT `FK_MYTUNES_LINKS_PURCHASED_ORDER_ID` FOREIGN KEY (`order_id`)
    REFERENCES `{$installer->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MYTUNES_LINKS_PURCHASED_ORDER_ITEM` FOREIGN KEY (`order_item_id`)
    REFERENCES `{$installer->getTable('sales_flat_order_item')}` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MYTUNES_LINKS_PURCHASED_TRACK` FOREIGN KEY (`track_id`)
    REFERENCES `{$installer->getTable('mytunes_tracks')}` (`track_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MYTUNES_LINKS_PURCHASED_CUSTOMER` FOREIGN KEY (`customer_id`)
    REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TRIGGER `mytunes_links_purchased_insert_datetime` BEFORE INSERT ON `{$installer->getTable('mytunes_links_purchased')}`
  FOR EACH ROW SET NEW.created_at = NOW();"
);


$installer->endSetup();