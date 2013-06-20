<?php
/**
 * The list of downloadable Mytunes tracks.
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Customer_Downloads_List extends Mage_Core_Block_Template
{

    /**
     * Class constructor. Retrieve download item collection.
     * Check for downloads that have exceeded the number of allowed downloads. Disable them.
     */
    public function __construct()
    {
        parent::__construct();
        $session = Mage::getSingleton('customer/session');
        $purchased = Mage::getResourceModel('mytunes/link_collection')
            ->addFieldToFilter('customer_id', $session->getCustomerId())
            ->addOrder('order_id', 'desc')
            ->addOrder('order_item_id', 'asc')
            ->addOrder('track_number', 'asc');
        $this->setItems($purchased);
    }

    /**
     * Prepare the layout.
     *
     * @return Que_Mytunes_Block_Customer_Downloads_List
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'mytunes.customer.downloads.pager')
            ->setCollection($this->getItems());
        $this->setChild('pager', $pager);
        $this->getItems()->load();
        return $this;
    }

    /**
     * Return order view url
     *
     * @param int $orderId
     *
     * @return string $url
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $orderId));
    }

    /**
     * Get the number of downloads left for this item.
     *
     * @param Que_Mytunes_Model_Link $item
     *
     * @return string $number | "Unlimited"
     */
    public function getRemainingDownloads(Que_Mytunes_Model_Link $item) {
        if ($item->getNumberOfDownloadsBought() < 1) {
            return $this->__('Unlimited');
        }
        else if($item->getNumberOfDownloadsUsed() < $item->getNumberOfDownloadsBought()) {
            return $this->__('Remaining') . ": " . (string) ($item->getNumberOfDownloadsBought() - $item->getNumberOfDownloadsUsed());
        }
        else {
            return $this->__('No downloads left');
        }
    }

    /**
     * Get the download url for an item.
     *
     * @param Que_Mytunes_Model_Link $item
     *
     * @return string $url
     */
    public function getDownloadUrl(Que_Mytunes_Model_Link $item) {
        if ($item->getStatus() == Que_Mytunes_Model_Link::STATUS_ACTIVE) {
            $itemLink = strtr(base64_encode($item->getLink()), '+/=', '-_,');
            return $this->getUrl('mytunes/customer/download', array('link' => $itemLink, 'id' => $item->getId()));
        }
        else {
            // download link is not active
            return "#\" onclick=\"alert('" . $this->__('This download is currently not activated') . "'); return false";
        }
    }

    /**
     * Whether to do the download in a new window
     *
     * @return boolean
     */
    public function getIsOpenInNewWindow() {
        return (boolean) Mage::getStoreConfig('mytunes/globals/open_download_in_new_window');
    }

}