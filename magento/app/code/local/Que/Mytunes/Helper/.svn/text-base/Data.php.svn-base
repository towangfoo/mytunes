<?php
/**
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * custom option attribute keys
     */
    const OPTION_MYTUNES_TYPE = 'mytunes_download_type';
    const OPTION_MYTUNES_ALBUM_ID = 'mytunes_download_album_id';
    const OPTION_MYTUNES_TRACKS_IDS = 'mytunes_download_tracks_ids';

    /**
     * mytunes type flag
     */
    const OPTION_MYTUNES_TYPE_ALBUM = 'album';
    const OPTION_MYTUNES_TYPE_TRACK = 'track';

    /**
     * retrieve the SKU of the (physical) Mage product, that is behind the download option.
     *
     * @param string $downloadSku
     *
     * @return string
     */
    public function getMageSku($downloadSku) {
        $p = $this->getSkuPrefix();
        $tp = $this->getTrackPrefix();
        $sku = $downloadSku;

        if (strlen($p) > 0 && strpos($sku, $p) === 0) {
            $sku = substr($sku, strlen($p));
            
            // check for suffix only if download prefix was found
            // need to check with regex here, as the prefix might
            // also be part of the sku string
            if (strlen($tp) > 0) {
            	$matches = array();
            	if (preg_match("/(.*)" . $tp . "[0-9]*$/", $sku, $matches)) {
            		$sku = $matches[1];
            	}
            }
        }

        return $sku;
    }

    /**
     * get a prefix that uniquely indicates the download option of an album.
     *
     * TODO: This must be configurable in backend
     *
     * @return string
     */
    public function getSkuPrefix() {
        return "D-";
    }

    /**
     * get a prefix that uniquely indicates the download option of a track.
     *
     * TODO: This must be configurable in backend
     *
     * @return string
     */
    public function getTrackPrefix() {
        return "-T";
    }

    /**
     * Get the download type for an sku.
     *
     * @param string $sku
     *
     * @return string $type || false
     */
    public function getDownloadTypeBySku($sku) {
        $tp = $this->getTrackPrefix();
    	if (preg_match("/(.*)" . $tp . "[0-9]*$/", $sku)) {
            return Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK;
        }
        else {
            return Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM;
        }
    }

    /**
     * Get a track number out of the download sku.
     *
     * @param string $sku
     *
     * @return int $trackNumber || false
     */
    public function getTrackNumberFromDownloadSku($sku) {
    	$tp = $this->getTrackPrefix();
    	$matches = array();
    	
    	if (preg_match("/(.*)" . $tp . "([0-9]*)$/", $sku, $matches)) {
    		return (int) $matches[2];
    	} else {
    		return false;
    	}
    }

    /**
     * Remove the Track part from the download sku.
     *
     * @param string $sku
     *
     * @return string $sku
     */
    public function stripTrackFromDownloadSku($sku) {
        
    	$tp = $this->getTrackPrefix();
    	$matches = array();
    	 
    	if (preg_match("/(.*)" . $tp . "[0-9]*$/", $sku, $matches)) {
    		return $matches[1];
    	} else {
    		return $sku;
    	}
    }

    /**
     * Does this product have a mytunes download option?
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return boolean
     */
    public function hasDownloadOption(Mage_Catalog_Model_Product $product) {
        return $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE) !== null;
    }

    /**
     * Retrive the Mytunes download options for a product (having custom options,
     * which is the case with Mytunes products from a quote item)
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array | false
     */
     public function getDownloadOptionsArray(Mage_Catalog_Model_Product $product) {
        if (!$this->hasDownloadOption($product)) {
            return false;
        }
        $result = array();
        $result['type'] = $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE)->getValue();
        if ($result['type'] == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
            $result['tracks'] = array();
            $tracks = explode(",", $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS)->getValue());
            foreach ($tracks as $t) {
                $result['tracks'][] = Mage::getModel('mytunes/track')->load($t);
            }
        }
        return $result;
     }

    /**
     * get formatted price
     *
     * @return string
     */
    public function getFormattedPrice($price) {
        return sprintf('%.2f', $price);
    }

    /**
     * Return full Url for a track
     *
     * @param string  $file
     * @param integer $albumId = null
     * @param string  $format
     *
     * @return string
     */
    public function getSampleTrackUrl($file, $albumId = null, $format)
    {
        $url = Que_Mytunes_Model_Track::getSampleBaseUrl();
        $file = str_replace("\\", "/", $file);
        if(substr($url, -1) != "/") {
            $url .= "/";
        }
        if ($albumId != null) {
            $url .= (string) $albumId . "/";
        }
        if(substr($file, 0, 1) == "/") {
            $file = substr($file, 1);
        }
        return $url . $file . "." . $format;
    }

    /**
     * Get a productby Sku.
     *
     * @param string $sku
     *
     * @return Mage_Catalog_Model_Product
     */
    public function loadProductBySku($sku) {
        $pCol = Mage::getModel('catalog/product')->getCollection()
            ->setFlag('require_stock_items', true)
            ->addAttributeToSelect('*')
            ->addFieldToFilter('sku', $sku)
            ->setPage(1, 1)
            ->load();
        return current($pCol->getIterator());
    }

    /**
     * Fetch all configured payment methods for the given store (0 = global
     * config scope) as an options array for select widgets.
     *
     * This is taken from Rico Neitzels Payfilter extension:
     * http://www.magentocommerce.com/magento-connect/Rico+Neitzel/extension/764
     *
     * @param integer $storeId
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getPaymentMethodOptions($storeId, $quote = null)
    {
        if (is_null($quote))
        {
            $quote = Mage::getModel('sales/quote')->setGrandTotal(0);
        }
        $methods = Mage::helper('payment')->getStoreMethods($storeId, $quote);
        $options = array();
        foreach ($methods as $method)
        {
            array_unshift($options, array(
                'value' => $method->getCode(),
                'label' => $method->getTitle(),
            ));
        }
        return $options;
    }

}