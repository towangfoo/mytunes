<?php
/**
 * This block shows an ordered item in the Customer Login Area.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Sales_Order_Item_Renderer extends Mage_Sales_Block_Order_Item_Renderer_Default
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
     * Get the name of the product
     */
    public function getProductName() {
        return $this->_getCatalogProduct()->getName();
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
     * Get attributes wrapper.
     * Tries to get attributes from Sales Item first, then from catalog product.
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args) {
        $data = null;
        if ("get" === substr($method, 0, 3)) {
            $key = $this->_underscore(substr($method,3));
            // try quote product first ...
            $data = $this->getItem()->getData($key, isset($args[0]) ? $args[0] : null);
            if (null === $data) {
                // try catalog product now
                $data = $this->_getCatalogProduct()->getData($key, isset($args[0]) ? $args[0] : null);
            }
            if (null !== $data) {
                return $data;
            }
        }

        return parent::__call($method, $args);
    }

    /**
     * Returns the product from catalog.
     * This has none of the options added to the Order Item
     *
     * @return Mage_Catalog_Model_Product
     */
    private function _getCatalogProduct() {
        $sku = Mage::helper('mytunes')->getMageSku($this->getSku());
        if ($sku != $this->_lastProductSku) {
            $this->_lastCatalogProduct = Mage::helper('mytunes')->loadProductBySku($sku);
            $this->_lastProductSku = $sku;
        }
        return $this->_lastCatalogProduct;
    }

}