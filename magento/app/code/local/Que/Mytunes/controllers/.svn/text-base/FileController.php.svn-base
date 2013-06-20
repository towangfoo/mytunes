<?php
/**
 * The controller that handles requests for files to play in Mytunes Player.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_FileController extends Mage_Core_Controller_Front_Action
{
    protected $_allowedFormats = array("ogg", "mp3");

    /**
     * the action to handle requests for prelistening of tracks.
     */
    public function prelistenAction()
    {
        $params = array_keys($this->getRequest()->getParams());
        if (1 == count($params)) {
            $fileData = explode(".", $params[0]);
            // $fileData[0] -> the file download sku
            // $fileData[1] -> the file format
            $fileAlias = base64_decode(rawurldecode($fileData[0]));

            if (in_array($fileData[1], $this->_allowedFormats)) {
                try {
                    $mageSku = Mage::helper('mytunes')->getMageSku($fileAlias);
                    $pCol = Mage::getModel('catalog/product')->getCollection()
                        ->setFlag('require_stock_items', true)
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('sku', $mageSku)
                        ->setPage(1, 1)
                        ->load();
                    $product = current($pCol->getIterator());
                    $album = $product->getTypeInstance(true)->getAlbum($product);
                    $trackNr = Mage::helper('mytunes')->getTrackNumberFromDownloadSku($fileAlias);
                    $track = $album->getTrack($trackNr);
                    if ($track->getSampleFileName()) {
                        $url = Mage::helper('mytunes')->getSampleTrackUrl($track->getSampleFileName(), $album->getId(), strtolower($fileData[1]));
                        $this->getResponse()->setRedirect($url, 200);
                        return;
                    }
                }
                catch (Exception $e) {
                    $this->_forward('error', 404);
                    return;
                }
            }
        }
        // incorrect request - send a 404 reponse
        $this->_forward('error', 404);
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
}