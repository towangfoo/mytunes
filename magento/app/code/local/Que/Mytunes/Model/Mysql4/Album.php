<?php
/**
 * Mytunes Product - resource model for an album table entry
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Mysql4_Album extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize connection and define resource
     *
     */
    protected function  _construct()
    {
        $this->_init('mytunes/album', 'album_id');
    }
}