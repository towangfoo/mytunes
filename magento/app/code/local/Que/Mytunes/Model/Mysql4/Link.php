<?php
/**
 * Mytunes Download link - resource model for a download link
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Mysql4_Link extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize connection and define resource
     *
     */
    protected function  _construct()
    {
        $this->_init('mytunes/link', 'link_id');
    }
}