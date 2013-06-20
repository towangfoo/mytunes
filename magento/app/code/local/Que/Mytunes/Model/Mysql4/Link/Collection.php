<?php
/**
 * Mytunes download link model collection
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Mysql4_Link_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize collection
     */
    protected function  _construct()
    {
        $this->_init('mytunes/link');
    }

}