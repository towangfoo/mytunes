<?php
/**
 * the block showing mytunes information in the name column od the order details page.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    /**
     *  @var Mage_Catalog_Model_Product
     */
    private $_lastCatalogProduct = null;

    /**
     *  @var String
     */
    private $_lastProductSku = null;

    /**
     * Get the name of the product.
     */
    public function getProductName() {
        return $this->_getCatalogProduct()->getName();
    }

    /**
     * Get the name of the artist.
     */
    public function getArtistName() {
        return $this->_getCatalogProduct()->getMytunesArtist();
    }

    /**
     * Get the download type string
     *
     * @return string | false
     */
    public function getDownloadOptionType() {
        if ($this->hasDownloadOptions()) {
            $options = $this->getItem()->getProductOptions();
            $type = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE];

            $label = "";
            if ($type == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
                $label = 'Complete Album Download';
            }
            elseif ($type == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
                $label = 'Download of Track(s):';
            }
            return Mage::helper('mytunes')->__($label);
        }
        return false;
    }

    /**
     * Has this item a list of download tracks?
     *
     * @return boolean
     */
    public function hasTracks() {
        $options= $this->getItem()->getProductOptions();
        $type = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE];
        if ($type !== Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
            return false;
        }
        return isset($options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS]);
    }

    /**
     * Get the track list
     *
     * @return array Que_Mytunes_Model_Track | false
     */
    public function getTracks() {
        if (!$this->hasTracks()) {
            return false;
        }
        $options = $this->getItem()->getProductOptions();
        $trackIds = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS];
        $result = array();
        foreach ($trackIds as $id) {
            $result[] = Mage::getModel('mytunes/track')->load($id);
        }
        return $result;
    }

    /**
     * Get the mytunes sku from the order options.
     *
     * @return string
     */
    public function getSku() {
        if ($this->hasDownloadOptions()) {
            $options = $this->getItem()->getProductOptions();
            if (isset($options['info_buyRequest']['sku'])) {
                return Mage::helper('mytunes')->stripTrackFromDownloadSku($options['info_buyRequest']['sku']);
            }
        }
        return parent::getSku();
    }

    /**
     * Does this order item have download options?
     * (Or is it a Mytunes product, but was ordered as the physical product?)
     *
     * @return boolean $hasDownload
     */
    public function hasDownloadOptions() {
        $options = $this->getItem()->getProductOptions();
        return isset($options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE]);
    }

    /**
     * Returns the product from catalog.
     * This has none of the options added to the Order Item
     *
     * @return Mage_Catalog_Model_Product
     */
    private function _getCatalogProduct() {
        $sku = Mage::helper('mytunes')->getMageSku($this->getItem()->getSku());
        if ($sku != $this->_lastProductSku) {
            $this->_lastCatalogProduct = Mage::helper('mytunes')->loadProductBySku($sku);
            $this->_lastProductSku = $sku;
        }
        return $this->_lastCatalogProduct;
    }

}
?>