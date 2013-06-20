<?php
/**
 * Mytunes Product - resource model for an audio track table collection
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Mysql4_Album_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize collection
     *
     */
    protected function  _construct()
    {
        $this->_init('mytunes/album');
    }

    /**
     * get all tracks contained in an album.
     *
     * @param int $productId
     *
     * @return Que_Mytunes_Model_Mysql4_Album_Collection
     */
    public function getAlbumByProduct($productId) {
        $this->addFieldToFilter('product_id', $productId);
        return $this;
    }
}