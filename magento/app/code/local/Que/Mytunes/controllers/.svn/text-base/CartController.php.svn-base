<?php
/**
 * Requests that interact with the cart.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_CartController extends Mage_Core_Controller_Front_Action
{

    /**
     * Add a mytunes product to the cart.
     */
    public function addAction() {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $product = $this->_initProduct();
        $this->_deleteDuplicateMytunesProducts($product, $cart->getQuote());

        $cart->addProduct($product, $params);
        $cart->save();
        $this->_getSession()->setCartWasUpdated(true);

        // propagate event
        Mage::dispatchEvent('checkout_cart_add_product_complete',
            array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
        );

        if (!$this->_getSession()->getNoCartRedirect(true)) {
            if (!$cart->getQuote()->getHasError()){
                $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                $this->_getSession()->addSuccess($message);

                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
                $this->_redirect('checkout/cart');
            }
        }

        // redirect back when there was a problem
        $this->getResponse()->setRedirect($this->_getRefererUrl());
    }

    /**
     * send an error response.
     */
    public function errorAction($errorCode)
    {
        $this->getRequest()->setHttpResponseCode($errorCode);
        $this->getRequest()->sendHeaders();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $downloadSku = $this->getRequest()->getParam('sku');
        $productSku = Mage::helper('mytunes')->getMageSku($downloadSku);

        $pCol = Mage::getModel('catalog/product')->getCollection()
                ->setFlag('require_stock_items', true)
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', $productSku)
                ->setPage(1, 1)
                ->load();
        $product = current($pCol->getIterator());

        if (!$product) {
            return false;
        }

        $helper = Mage::helper('mytunes');
        $type = $helper->getDownloadTypeBySku($downloadSku);
        $album = $product->getTypeInstance(true)->getAlbum($product);
        $cart = $this->_getCart();

        // add mytunes download type
        $product->addCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE, $type);

        // add mytunes download album id
        $product->addCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID, $album->getId());

        // check for a tracks product already in the cart and get its track IDs
        $existingTracks = array();
        $quoteCollection = $cart->getQuote()->getItemsCollection();
        foreach ($quoteCollection as $item) {
            $itemType = $item->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
            if ($itemType != null) {
                if ($itemType->getValue() == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK
                && $item->getProduct()->getId() == $product->getId()) {
                    // the product to add is already in the cart and is a list of tracks
                    $existing = $item->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS);
                    $existingTracks = explode(",", $existing->getValue());
                }
            }
        }

        // add single track downloads  together with preexisting track selection
        if ($type === Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
            $trackNumber = $helper->getTrackNumberFromDownloadSku($downloadSku);
            $trackIds = array_merge($existingTracks, array($album->getTrack($trackNumber)->getId()));

            // NOTE: using the (unique) track_id instead of the track number for further processing!
            $product->addCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS,
                    implode(',', $trackIds));
        }

        return $product;
    }

    /**
     * Delete Mytunes products with the same product id from quote.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return void
     */
    protected function _deleteDuplicateMytunesProducts(Mage_Catalog_Model_Product $product, Mage_Sales_Model_Quote $quote) {
        $productId = $product->getId();
        foreach ($quote->getItemsCollection() as $item) {
            $type = $item->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
            if ($type != null) {
                // this is a mytunes product
                if ($item->getProduct()->getId() == $productId) {
                    $quote->removeItem($item->getId());
                }
            }
        }
    }

}