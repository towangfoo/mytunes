<?php
/**
 * The block containing the audio player.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Player extends Mage_Catalog_Block_Product_View
{

    /**
     * get the configuration settings for a mytunes player on the product detail page.
     *
     * @return string JsonObject
     */
    public function getMytunesJsonConfig() {
        $product = $this->getProduct();
        $helper = $this->helper('mytunes/cart');
        $album = $this->_getAlbum($product);

        // build the tracklist
        $tracks = array();
        foreach ($album->getTracklist() as $t) {
            $i = array();
            $i['track'] = $t->getTrackNumber();
            $i['artist'] = (($artist = $t->getArtist()) != null) ? $artist : $product->getMytunesArtist();
            $i['name'] = $t->getTrackname();
            $i['sku'] = $t->getSku();
            $i['buySingle'] = $t->isSingleDownloadable();
            if ($i['buySingle'] === true) {
                $i['price'] = $t->getPrice();
                $i['addToCartUrl'] = $helper->getAddTrackUrl($t);
                $i['inCart'] = $helper->isTrackInCart($t);
            }
            array_push($tracks, $i);
        }

        // build the config array
        $config = array(
            'baseUrl' => Mage::getBaseUrl(),
            'requestUri' => 'mytunes/file/prelisten/',  // TODO: put in some config file
            'buyComplete' => $album->isCompleteDownloadable(),
            'tracks' => $tracks
        );
        if ($album->isCompleteDownloadable()) {
            $config['addToCartUrl'] = $helper->getAddAlbumUrl($album);
            $config['price'] = $album->getPrice();
            $config['sku'] = $album->getSku();
            $config['inCart'] = $helper->isAlbumInCart($album);
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * determine whether the mytunes player should show up at all
     *
     * @return boolean
     */
    public function hasPlayer() {
        return $this->getProduct()->getData('mytunes_enable_player') === "1";
    }

    /**
     * get a mytunes album for the current product
     *
     * @param $product
     *
     * @return Que_Mytunes_Model_Album
     */
    private function _getAlbum($product) {
        return $this->getProduct()->getTypeInstance(true)->getAlbum($product);
    }

}