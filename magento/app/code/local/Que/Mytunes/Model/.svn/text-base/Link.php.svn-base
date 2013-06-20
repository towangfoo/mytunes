<?php
/**
 * Mytunes download link model.
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Link extends Mage_Core_Model_Abstract
{

    /**
     * The different statuses a link item can have.
     */
    const STATUS_ACTIVE = 1;                // active, ready to download
    const STATUS_DISABLED = 0;              // disabled
    const STATUS_PENDING = 2;               // waiting for payment
    const STATUS_NO_DOWNLOADS_LEFT = 3;     // downloads exceeded, symlink deleted

    /**
     * @var Que_Mytunes_Model_Track
     */
    private $_track = null;

    /**
     * @var Mage_Sales_Model_Order
     */
    private $_order = null;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('mytunes/link');
        parent::_construct();
    }

    /**
     * Retrieve customer symlink path
     *
     * @return string
     */
    public static function getCustomerSymlinkPath()
    {
        return Mage::getBaseDir('media') . DS . 'mytunes' . DS . 'downloads';
    }

    /**
     * Retrieve customer symlink URL
     *
     * @return string
     */
    public static function getCustomerSymlinkUrl()
    {
        return Mage::getBaseUrl('media') . 'mytunes' . "/" . 'downloads';
    }

    /**
     * Get the increment id of the order.
     *
     * @return string
     */
    public function getOrderIncrementId() {
        return $this->_getOrder()->getIncrementId();
    }

    /**
     * Get the name of the track.
     *
     * @return string
     */
    public function getTrackname() {
        return $this->_getTrack()->getTrackname();
    }

    /**
     * Get the number of the track.
     *
     * @return string
     */
    public function getTracknumber() {
        return $this->_getTrack()->getTrackNumber();
    }

    /**
     * Get the artist of the track.
     *
     * @return string
     */
    public function getArtist() {
        $artist = $this->_getTrack()->getArtist();
        if ($artist == null) {
            $artist = $this->_getTrack()->getAlbum()->getProduct()->getMytunesArtist();
        }
        return $artist;
    }

    /**
     * Get the album name of the track.
     *
     * @return string
     */
    public function getAlbumname() {
        return $this->_getTrack()->getAlbum()->getProduct()->getName();
    }

    /**
     * Get the status of an item as human readable string.
     *
     * @return string
     */
    public function getLinkStatus() {
        switch ($this->getStatus()) {
            case Que_Mytunes_Model_Link::STATUS_ACTIVE:
                return Mage::helper('mytunes')->__('Active');
                break;
            case Que_Mytunes_Model_Link::STATUS_DISABLED:
            case Que_Mytunes_Model_Link::STATUS_NO_DOWNLOADS_LEFT:
                return Mage::helper('mytunes')->__('Disabled');
                break;
            case Que_Mytunes_Model_Link::STATUS_PENDING:
            default:
                return Mage::helper('mytunes')->__('Pending');
        }
    }

    /**
     * Check the status of an item. Check if the number of allowed downloads has exceeded.
     * Delete the symlink if the number of downloads has exceeded.
     *
     * @return void
     */
    public function checkStatus() {
        if ($this->getNumberOfDownloadsBought() > 0) {
            if ($this->getNumberOfDownloadsUsed() >= $this->getNumberOfDownloadsBought()) {
                if (Mage::helper('mytunes/download')->deleteSymlink($this)) {
                    $this->setStatus(Que_Mytunes_Model_Link::STATUS_NO_DOWNLOADS_LEFT)->save();
                }
            }
        }
    }

    /**
     * Get the order belonging to this item.
     *
     * @return Mage_Sales_Model_Order
     */
    private function _getOrder() {
        if ($this->_order == null) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    /**
     * Get the Mytunes track belonging to this item.
     *
     * @return Que_Mytunes_Model_Track
     */
    private function _getTrack() {
        if ($this->_track == null) {
            $this->_track = Mage::getModel('mytunes/track')->load($this->getTrackId());
        }
        return $this->_track;
    }

}