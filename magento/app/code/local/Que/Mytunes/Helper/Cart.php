<?php
/**
 * A helper that creates URLs to interact with the cart.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Helper_Cart extends Mage_Core_Helper_Url {

    /**
     * URL anchor for the mytunes player
     * @var string
     */
    private $_anchorPrelisten = "prelisten";

    /**
     * Retrieve cart instance
     *
     * @return Mage_Checkout_Model_Cart
     */
    public function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * build the url to add a download track to the cart.
     *
     * @param Que_Mytunes_Model_Track $track
     *
     * @return string
     */
    public function getAddTrackUrl(Que_Mytunes_Model_Track $track) {
        $params = array(
            'sku' => $track->getSku()
        );
        return $this->_getCartAddUrl($params);
    }

    /**
     * build the url to add a download album to the cart.
     *
     * @param Que_Mytunes_Model_Album $album
     *
     * @return string
     */
    public function getAddAlbumUrl(Que_Mytunes_Model_Album $album) {
        $params = array(
            'sku' => $album->getSku()
        );
        return $this->_getCartAddUrl($params);
    }

    /**
     * Is this track already in the cart?
     *
     * @param Que_Mytunes_Model_Track $track
     *
     * @return boolean
     */
    public function isTrackInCart(Que_Mytunes_Model_Track $track) {
        $quoteItems = $this->getCart()->getQuote()->getAllItems();
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            $type = $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
            if ($type === null) continue;
            if ($type->getValue() === Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
                $albumId = (int) $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID)->getValue();
                if ($albumId != $track->getAlbumId()) continue;
                $tracks = $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS)->getValue();
                if (in_array((string) $track->getId(), explode(",", $tracks))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is this album already in the cart?
     *
     * @param Que_Mytunes_Model_Album $album
     *
     * @return boolean
     */
    public function isAlbumInCart(Que_Mytunes_Model_Album $album) {
        $quoteItems = $this->getCart()->getQuote()->getAllItems();
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            $type = $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
            if ($type === null) continue;
            if ($type->getValue() === Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
                $id = (int) $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID)->getValue();
                if ($id == $album->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * get the URL to add a mytunes product to the cart.
     *
     * @param array $params
     *
     * @return array
     */
    private function _getCartAddUrl($params) {
        $currUrl = $this->getCurrentUrl() . "#" . $this->_anchorPrelisten;

        $defaults = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core')->urlEncode($currUrl)
        );

        $routeParams = array_merge($defaults, $params);
        return $this->_getUrl('mytunes/cart/add', $routeParams);
    }
}
?>