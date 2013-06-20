<?php
/**
 * The controller that handles requests in customer login area.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_CustomerController extends Mage_Core_Controller_Front_Action
{

    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * The action to show the list of downloads.
     */
    public function downloadsAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        if ($block = $this->getLayout()->getBlock('que_mytunes_customer_downloads_list')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * The action to download a track.
     */
    public function downloadAction()
    {
        $link = (string) $this->getRequest()->getParam('link');
        $link = base64_decode(strtr($link, '-_,', '+/='));
        $file = Que_Mytunes_Model_Link::getCustomerSymlinkPath() . DS . $link;

        // load purchased link item
        $id = (int) $this->getRequest()->getParam('id');
        $purchasedLink = Mage::getModel('mytunes/link')->load($id);
        $purchasedLink->checkStatus();

        if ($purchasedLink->getStatus() == Que_Mytunes_Model_Link::STATUS_ACTIVE && file_exists($file) && is_readable($file)) {
            // increment number of downloads used
            $purchasedLink->setNumberOfDownloadsUsed((int) $purchasedLink->getNumberOfDownloadsUsed() + 1);
            $purchasedLink->save();

            $url = Que_Mytunes_Model_Link::getCustomerSymlinkUrl() . "/" . $link;
            $this->getResponse()->setRedirect($url, 200);
            return;
        }

        // incorrect request - send a 404 reponse
        $this->_forward('error', 404);
    }

    /**
     * Send an error response.
     */
    public function errorAction($errorCode)
    {
        $this->getRequest()->setHttpResponseCode($errorCode);
        $this->getRequest()->sendHeaders();

        $this->loadLayout();
        $this->renderLayout();
    }
}