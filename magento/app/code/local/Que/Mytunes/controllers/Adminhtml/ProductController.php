<?php
/**
 * Admin Controller for Mytunes specific catalog_product actions.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action
{

    /**
     * upload a mytunes track
     */
    public function uploadAction()
    {
        $type = Que_Mytunes_Model_Track::getUploadFileIdentifier();
        $tmpPath = Que_Mytunes_Model_Track::getTmpBasePath();
        $result = array();
        try {
            $uploader = new Varien_File_Uploader($type);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $result = $uploader->save($tmpPath);
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=> "Mytunes upload error: ".$e->getMessage(), 'errorcode'=>$e->getCode());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Get a mytunes track to play in the admin jplayer.
     *
     * Create a symlink to the full file to overcome the htaccess protection.
     * Be sure that it gets deleted when it is not needed anymore, as anyone
     * who knows the symlink path has access to the full file!
     * 
     * @deprecated
     */
    public function playfullAction()
    {
        $trackId = $this->getRequest()->getParam('track');
        $track = Mage::getModel('mytunes/track')->load($trackId);

        $absPath = Mage::helper('mytunes/admin')->getFilePath(
            Que_Mytunes_Model_Track::getTrackBasePath(),
            $track->getFileName(),
            $track->getAlbumId()
        );

        if (file_exists($absPath) && is_readable($absPath)) {
            try {
                $sess = Mage::getSingleton('admin/session')->getSessionId();
                $symlink = Mage::helper('mytunes/admin')->getAdminSymlink($absPath, $sess);
                $url = Mage::helper('mytunes/admin')->getAdminSymlinkUrl($symlink);
                $this->getResponse()->setRedirect($url, 200);
                return;
            } catch (Exception $e) {
                $this->addError(Mage::helper('mytunes')->__('An error occured loading the file') . ": " . $e->getMessage());
            }
        }
        // incorrect request - send a 404 reponse
        $this->_forward('error', 404);
    }

    /**
     * This action transforms a Simple Product to a Mytunes product
     */
    public function transformAction()
    {
        $mytunesParams = $this->getRequest()->getParam('mytunes');
        if (is_null($mytunesParams) || !isset($mytunesParams['transform'])) {
            $this->_forward('error', 404);
        }

        $mytunesTypeId = Que_Mytunes_Model_Product_Type::TYPE_MYTUNES;
        $productId = $mytunesParams['transform']['product_id'];
        $newSku = $mytunesParams['transform']['newsku'];
        $keepSimpleProduct = (boolean) $mytunesParams['transform']['keepsimple'];

        $newProductId = $productId;

        if ($keepSimpleProduct) {
            // duplicate simple product first
            $oldProduct = Mage::getModel('catalog/product')->load($productId);
            try {
                $newProduct = $oldProduct->duplicate();
                $newProductId = $newProduct->getId();
                $this->_getSession()->addSuccess($this->__('The Simple Product has been duplicated.'));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        // simply change sku and product type in catalog_product_entity
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->query("UPDATE `catalog_product_entity` SET `sku`=?, `type_id`=? WHERE `entity_id`=?", array($newSku, $mytunesTypeId, $newProductId));

        $this->_getSession()->addSuccess($this->__('The product was transformed to a Mytunes Product. You can now add Mytunes Options to it.'));
        $this->_redirect("adminhtml/catalog_product/edit", array('_current'=>true, 'id' => $newProductId, 'active_tab' => 'mytunes_options'));

    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }

}