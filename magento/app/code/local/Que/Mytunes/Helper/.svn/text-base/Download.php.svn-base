<?php
/**
 * Helper for download -related Mytunes functions.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Helper_Download extends Mage_Core_Helper_Abstract
{

    /**
     * Activate all download link items for an order.
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function activateDownloadsforOrder(Mage_Sales_Model_Order $order = null) {
        $links = Mage::getModel('mytunes/link')->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('order_id', $order->getId())
            ->load();
        foreach($links as $link) {
            $link->setStatus(Que_Mytunes_Model_Link::STATUS_ACTIVE)->save();
        }
    }

    /**
     * Create a symlink for giving a customer access to an ordered track.
     *
     * @param Que_Mytunes_Model_Track   $track
     * @param int                       $customerId (if 0, then customer is a guest)
     * @param string                    $salt to use for symlink path
     *
     * @return string       symlink path
     */
    public function createPurchasedTrackSymlink(Que_Mytunes_Model_Track $track, $customerId, $salt)
    {
        $resource = $this->getFilePath(
            Que_Mytunes_Model_Track::getTrackBasePath(),
            $track->getFileName(),
            $track->getAlbumId()
        );

        $symlinkName = (string) $customerId . DS . md5($salt) . DS . basename($track->getFileName());
        $symlinkPath = Que_Mytunes_Model_Link::getCustomerSymlinkPath();
        $symlink = $this->getFilePath($symlinkPath, $symlinkName);

        if (file_exists($symlink) && is_readable($symlink)) {
            throw new Exception("Symlink already exists: " + $symlinkName);
        }

        // create new symlink
        $io = new Varien_Io_File();
        $destDirectory = dirname($symlink);
        try {
            $io->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $io->mkdir($destDirectory, 0777, true);
            $io->open(array('path'=>$destDirectory));
        }

        if (symlink($resource, $symlink)) {
            return $symlinkName;
        }
        else {
            throw new Exception("Could not create customer symlink for resource " + $resource);
        }
    }

    /**
     * Return full path to file
     *
     * @param string  $path
     * @param string  $file
     * @param integer $albumId
     *
     * @return string
     */
    public function getFilePath($path, $file, $albumId = null)
    {
        $file = $this->_prepareFileForPath($file);

        if(substr($path, -1) == DS) {
            $path = substr($path, 0, -1);
        }

        if ($albumId != null) {
            $path = $this->_appendAlbumIdToPath($path, $albumId);
        }

        if(substr($file, 0, 1) == DS) {
            return $path . DS . substr($file, 1);
        }

        return $path . DS . $file;
    }

    /**
     * Delete a symlink for a purchased link item.
     *
     * @param Que_Mytunes_Model_Link $link
     *
     * @return boolean $success
     */
    public function deleteSymlink(Que_Mytunes_Model_Link $link) {
        $symlinkPath = Que_Mytunes_Model_Link::getCustomerSymlinkPath();
        $symlinkName = $link->getLink();
        $symlink = $this->getFilePath($symlinkPath, $symlinkName);

        $io = new Varien_Io_File();
        try {
            $io->open(array('path'=>dirname($symlink)));
        } catch (Exception $e) {
            return false;
        }
        return $io->rm($symlink) == true;
    }

    /**
     * Replace slashes with directory separator
     *
     * @param string $file
     * @return string
     */
    protected function _prepareFileForPath($file)
    {
        return str_replace('/', DS, $file);
    }

    /**
     * Append the album Id to the end of a path.
     *
     * @param string  $path
     * @param integer $albumId
     *
     * @return string $path
     */
    protected function _appendAlbumIdToPath($path, $albumId) {
        if (substr($path, -1) == DS) {
            $path = substr($path, 0, -1);
        }
        $path .= DS . (string) $albumId;
        return $path;
    }

}