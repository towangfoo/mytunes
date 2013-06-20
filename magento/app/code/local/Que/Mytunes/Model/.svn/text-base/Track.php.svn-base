<?php
/**
 * Mytunes track model
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Track extends Mage_Core_Model_Abstract
{
    private $_album;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('mytunes/track');
        parent::_construct();
    }

    /**
     * The upload field identifier for track uploads using the Flash Uploader
     *
     * @return string
     */
    public static function getUploadFileIdentifier()
    {
        return 'tracks';
    }

    /**
     * Retrieve base temporary path
     *
     * @return string
     */
    public static function getTmpBasePath()
    {
        return Mage::getBaseDir('media') . DS . 'tmp' . DS . 'mytunes';
    }

    /**
     * Retrieve sample files base path
     *
     * @return string
     */
    public static function getSampleBasePath()
    {
        return Mage::getBaseDir('media') . DS . 'mytunes' . DS . 'sample';
    }

    /**
     * Retrieve sample files request URL
     *
     * @return string
     */
    public static function getSampleBaseUrl()
    {
        return Mage::getBaseUrl('media') . 'mytunes' . "/" . 'sample';
    }

    /**
     * Retrieve Base files path
     *
     * @return string
     */
    public static function getTrackBasePath()
    {
        return Mage::getBaseDir('media') . DS . 'mytunes' . DS . 'full';
    }

    /**
     * Retrieve Base files request URL
     *
     * @return string
     */
    public static function getTrackBaseUrl()
    {
        return Mage::getBaseUrl('media') . 'mytunes' . "/" . 'full';
    }

    /**
     * Retrieve admin symlink path
     *
     * @return string
     */
    public static function getAdminSymlinkPath()
    {
        return Mage::getBaseDir('media') . DS . 'mytunes' . DS . 'tmp';
    }

    /**
     * Retrieve admin symlink URL
     *
     * @return string
     */
    public static function getAdminSymlinkUrl()
    {
        return Mage::getBaseUrl('media') . 'mytunes' . "/" . 'tmp';
    }

    /**
     * Is this track available as a single download?
     * Also checks for global and product-gloabel toggles.
     *
     * @return boolean
     */
    public function isSingleDownloadable() {
        $product = $this->getAlbum()->getProduct();
        if($product->getTypeInstance(true)->isDownloadOptionEnabled($product)) {
            return $this->getData('single_download') == "1";
        }
        return false;
    }

    /**
     * Get the unique SKU for this track download.
     *
     * @return string
     */
    public function getSku() {
        $albumSku = $this->getAlbum()->getSku();
        $trackPrefix = Mage::helper('mytunes')->getTrackPrefix();
        return $albumSku . $trackPrefix . $this->getTrackNumber();
    }

    /**
     * Get the album corresponding to this track.
     *
     * @return Que_Mytunes_Model_Album
     */
    public function getAlbum() {
        if ($this->_album === null) {
            $this->_album = Mage::getModel('mytunes/album')->load($this->getData('album_id'));
        }
        return $this->_album;
    }
}