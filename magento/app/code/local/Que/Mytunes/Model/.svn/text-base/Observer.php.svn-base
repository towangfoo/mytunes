<?php
/**
 * Mytunes Events Observer
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Observer
{
    const ORDER_STATUS_PROCESSING  = Mage_Sales_Model_Order::STATE_PROCESSING;
    const ORDER_STATUS_COMPLETE    = Mage_Sales_Model_Order::STATE_COMPLETE;
    const ORDER_STATUS_PENDING     = 'pending';

    /**
     * Mytunes observer for event "catalog_product_prepare_save"
     * Called when saving a Mytunes product after editing in Backend.
     * Set Mytunes options from POST data as an attribute of the product to save.
     * Actual saving is then done in Que_Mytunes_Model_Product_Type::save()
     *
     * @param   Varien_Object $observer
     * @return  Mage_Downloadable_Model_Observer
     */
    public function catalog_product_prepare_save($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();

        if($mytunes = $request->getPost('mytunes')) {
            $product->setMytunesSaveDataArray($mytunes);
        }

        return $this;
    }

    /**
     * Mytunes observer for event "sales_order_save_after".
     * Activate download links when the order is marked as paid (e.g. via PayPal).
     *
     * @param   Varien_Object $observer
     * @return  Mage_Downloadable_Model_Observer
     */
    public function sales_order_save_after($observer) {
        $request = $observer->getEvent()->getRequest();
        $order = $observer->getEvent()->getOrder();

        $status = $order->getStatus();
        if ($status === Que_Mytunes_Model_Observer::ORDER_STATUS_PROCESSING ||
            $status === Que_Mytunes_Model_Observer::ORDER_STATUS_COMPLETE
        ) {
            try {
                Mage::helper('mytunes/download')->activateDownloadsforOrder($order);
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Mytunes observer for event "sales_order_invoice_pay"
     * Activate download links when the order is markesd paid.
     *
     * @param   Varien_Object $observer
     * @return  Mage_Downloadable_Model_Observer
     */
    public function sales_order_invoice_pay($observer) {
        $invoice = $observer->getEvent()->getInvoice();

        if ($order = $invoice->getOrder()) {
            try {
                Mage::helper('mytunes/download')->activateDownloadsforOrder($order);
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Mytunes observer for event "sales_order_item_save_after".
     * Called just after a quote item has been ordered. Create the
     * download link to the product.
     *
     * @param   Varien_Object $observer
     * @return  Mage_Downloadable_Model_Observer
     */
    public function sales_order_item_save_after($observer) {
        $orderItem = $observer->getEvent()->getItem();
        $product = $orderItem->getProduct();

        try {
            if ($product && $product->getTypeId() != Que_Mytunes_Model_Product_Type::TYPE_MYTUNES) {
                return $this;
            }
            if (!$product) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($orderItem->getOrder()->getStoreId())
                    ->load($orderItem->getProductId());
            }

            if ($product->getTypeId() == Que_Mytunes_Model_Product_Type::TYPE_MYTUNES) {
                if ($downloadType = $orderItem->getProductOptionByCode(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE)) {

                    $tracks = array();
                    if ($downloadType == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
                        // full album
                        $albumId = $orderItem->getProductOptionByCode(Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID);
                        $tracks = Mage::getModel('mytunes/track')->getCollection()->getTracksForAlbum($albumId);
                    }
                    elseif($downloadType == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
                        // album tracks
                        $trackIds = $orderItem->getProductOptionByCode(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS);
                        foreach($trackIds as $id) {
                            $tracks[] = Mage::getModel('mytunes/track')->load($id);
                        }
                    }

                    // get customer id
                    $session = Mage::getSingleton('customer/session');
                    $customerId = ($session->isLoggedIn())? $session->getCustomerId() : 0;

                    $salt = (string) microtime() . "-" . (string) $orderItem->getOrder()->getId() . "-" . (string) $customerId;

                    // save link
                    foreach ($tracks as $track) {
                        // create symlink
                        $symlink = Mage::helper('mytunes/download')->createPurchasedTrackSymlink($track, $customerId, $salt);
                        // save link item
                        $link = Mage::getModel('mytunes/link')
                            ->setOrderId($orderItem->getOrder()->getId())
                            ->setOrderItemId($orderItem->getId())
                            ->setCustomerId($customerId)
                            ->setTrackId($track->getId())
                            ->setLink($symlink)
                            ->setStatus(Que_Mytunes_Model_Link::STATUS_PENDING)  // activation will be done in sales_order_save_after
                            ->setNumberOfDownloadsBought($track->getDownloads())
                            ->setNumberOfDownloadsUsed(0)
                            ->setCreatedAt($orderItem->getCreatedAt())
                            ->setUpdatedAt($orderItem->getUpdatedAt())
                            ->save();
                    }
                }
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Mytunes observer for event "payment_method_is_active".
     * Check if a payment method is available.
     *
     * @param   Varien_Object $observer
     * @return  Mage_Downloadable_Model_Observer
     */
    public function payment_method_is_active($observer)
    {
        $checkResult = $observer->getEvent()->getResult();
        $method = $observer->getEvent()->getMethodInstance();

        // check if there is a download product in the cart
        $downloadInCart = false;
        $quoteItems = Mage::getSingleton('checkout/cart')->getQuote()->getAllItems();
        foreach ($quoteItems as $item) {
            if (Mage::helper('mytunes')->hasDownloadOption($item->getProduct())) {
                $downloadInCart = true;
                break;
            }
        }

        if ($downloadInCart && $checkResult->isAvailable) {
            $excludeSome = ! (bool) Mage::getStoreConfig('mytunes/payment/allowall');
            if ($excludeSome) {
                // exclude forbidden payment methods
                $forbidden = explode(",", Mage::getStoreConfig('mytunes/payment/restrict'));
                if (in_array($method->getCode(), $forbidden)) {
                    $checkResult->isAvailable = false;
                }
            }
        }

        return $this;
    }
}