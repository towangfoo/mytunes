<?php
/**
 * Mytunes album model
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Album extends Mage_Core_Model_Abstract
{
    protected $_tracks;

    private $_product;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('mytunes/album');
        parent::_construct();
    }

    /**
     * is this album available as a complete download?
     * Also checks for global and product-gloabel toggles.
     *
     * @return boolean
     */
    public function isCompleteDownloadable() {
        if ($this->getProduct()->getTypeInstance(true)->isDownloadOptionEnabled($this->getProduct())) {
            return $this->getProduct()->getData('mytunes_enable_albumdownload') === "1";
        }
        return false;
    }

    /**
     * Set the tracklist for the album.
     *
     * @param Que_Mytunes_Model_Mysql4_Track_Collection $col
     *
     * @return Que_Mytunes_Model_Album $this
     */
    public function setTracklist(Que_Mytunes_Model_Mysql4_Track_Collection $col) {
        $this->_tracks = array();
        foreach ($col as $track) {
            array_push($this->_tracks, $track);
        }

        return $this;
    }

    /**
     * get the tracklist.
     *
     * @return array of Que_Mytunes_Model_Mysql4_Track
     */
    public function getTracklist() {
        return $this->_tracks;
    }

    /**
     * get the unique SKU for this album download
     *
     * @return string
     */
    public function getSku() {
        $prefix = Mage::helper('mytunes')->getSkuPrefix();
        return $prefix . $this->getProduct()->getSku();
    }

    /**
     * get the corresponding product for this album.
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getData('product_id'));
        }
        return $this->_product;
    }

    /**
     * get the mytunes product type instance
     *
     * @return Que_Mytunes_Model_Product_Type
     */
    public function getMytunesProduct() {
        return $this->getProduct()->getTypeInstance(true);
    }

    /**
     * Get a track by his track number.
     *
     * @param int $trackNr
     *
     * @return Que_Mytunes_Model_Track $track || false
     */
    public function getTrack($trackNr) {
        foreach($this->getTracklist() as $t) {
            if ($t->getTrackNumber() == $trackNr) {
                return $t;
            }
        }
        return false;
    }
}