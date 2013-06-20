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
class Que_Mytunes_Model_Mysql4_Track_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize collection
     *
     */
    protected function  _construct()
    {
        $this->_init('mytunes/track');
    }

    /**
     * get all tracks contained in an album.
     *
     * @param int $albumId
     *
     * @return Que_Mytunes_Model_Mysql4_Track_Collection
     */
    public function getTracksForAlbum($albumId) {
        $this->addFieldToFilter('album_id', $albumId);
        return $this;
    }
}