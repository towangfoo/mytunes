<?php
/**
 * Mytunes product type model
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_MYTUNES = 'mytunes';

    /**
     * Get the album for a mytunes product.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Que_Mytunes_Model_Album | false
     */
    public function getAlbum(Mage_Catalog_Model_Product $product) {
        $albumCollection = Mage::getModel('mytunes/album')->getCollection()
                ->getAlbumByProduct($product->getId());
        if ($albumCollection->getSize() == 1) {
            $album = $albumCollection->getFirstItem();
            $album->setTracklist(
                Mage::getModel('mytunes/track')->getCollection()
                        ->getTracksForAlbum($album->getId())
            );
        }
        else {
            $album = false;
        }

        return $album;
    }

    /**
     * is the download option enabled for this product?
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return boolean
     */
    public function isDownloadOptionEnabled(Mage_Catalog_Model_Product $product) {
        return $product->getData('mytunes_enable_downloads') === "1";
    }

    /**
     * Mytunes products get treated like virtual products, only if they have a download option ...
     * Overriden from Mage_Catalog_Model_Product_Type_Abstract
     *
     * @return boolean
     */
    public function isVirtual($product = null)
    {
        return Mage::helper('mytunes')->hasDownloadOption($product);
    }

    /**
     * Get array of options that will be stored with the ordered product.
     *
     * @param Mage_Catalog_Model_Product $product retrieved from quote item
     *
     * @return array
     */
    public function getOrderOptions($product = null) {

        $options = parent::getOrderOptions($product);

        if (Mage::helper('mytunes')->hasDownloadOption($product)) {
            $type = $this->getProduct($product)->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
            $mytunesOptions = array();
            $mytunesOptions[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE] = $type->getValue();
            $mytunesOptions[Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID] = $this->getProduct($product)->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID)->getValue();
            if ($type->getValue() == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
                $mytunesOptions[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS] =
                        explode(",", $this->getProduct($product)->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS)->getValue());
            }
            $options = array_merge($options, $mytunesOptions);
        }

        return $options;
    }

    /**
     * Save a product.
     * Includes all saving and updating activities needed for mytunes_* tables.
     * Mytunes data is set in a product attribute and accessible with "getMytunesSaveDataArray()"
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Que_Mytunes_Model_Product_Type
     */
    public function save($product = null) {
        parent::save($product);
        $product = $this->getProduct($product);

        if ($mytunes = $product->getMytunesSaveDataArray()) {
            $albumModel = Mage::getModel('mytunes/album');
            // save album settings
            if (isset($mytunes['album'])) {
                if (empty($mytunes['album']['album_id'])) {
                    unset($mytunes['album']['album_id']);
                }
                $albumModel->setData($mytunes['album'])
                    ->setProductId($product->getId());

                $albumModel->save();
            }
            // handle tracks
            if (isset($mytunes['tracks'])) {

                $_itemsToDelete = array();
                foreach ($mytunes['tracks'] as $track) {
                    if (empty($track['track_id'])) {
                        unset($track['track_id']);
                    }
                    if ($track['delete_track'] == '1') {
                        if (isset($track['track_id']))
                            $_itemsToDelete[] = $track['track_id'];
                    }
                    else {
                        unset($track['delete_track']);

                        // handle track files
                        $files = array();
                        if (isset($track['file'])) {
                            $files = Mage::helper('core')->jsonDecode($track['file']);
                            unset($track['file']);
                        }

                        $trackModel = Mage::getModel('mytunes/track');
                        if (isset($track['downloads_unlimited']) && $track['downloads_unlimited'] == '1') {
                            $track['downloads'] = null;
                            unset($track['downloads_unlimited']);
                        }
                        $trackModel->setData($track)
                            ->setAlbumId($albumModel->getId());

                        $fileName = null;
                        if (isset($files[0])) {
                            // add full file version
                            // (move from tmp to the storage location)
                            $fileName = Mage::helper('mytunes/admin')->moveFile(
                                Que_Mytunes_Model_Track::getTmpBasePath(),
                                Que_Mytunes_Model_Track::getTrackBasePath(),
                                $albumModel->getId(),
                                $files[0],
                                true
                            );

                            $trackModel->setFileName($fileName);
                        }

                        if (isset($track['create_sample']) && $track['create_sample'] == "1") {
                            if ($fileName == null) {
                                // retrieve file name from data base
                                $fileName = $trackModel->getFileName();
                            }

                            if ($fileName) {
                                $tcStart = $track['sample_start'];
                                $tcEnd = $track['sample_end'];
                                if (!preg_match("/^([0-9]{2}:)?[0-9]{2}:[0-9]{2}/", $tcStart)) {
                                    $tcStart = Mage::getStoreConfig('mytunes_settings/sox/trim_start_default');
                                }
                                if (!preg_match("/^([0-9]{2}:)?[0-9]{2}:[0-9]{2}/", $tcEnd)) {
                                    $tcEnd = Mage::getStoreConfig('mytunes_settings/sox/trim_end_default');
                                }

                                // create sample file
                                $sampleFileName = Mage::helper('mytunes/admin')->createSample(
                                    Que_Mytunes_Model_Track::getTrackBasePath(),
                                    Que_Mytunes_Model_Track::getSampleBasePath(),
                                    $albumModel->getId(),
                                    $fileName,
                                    $tcStart,
                                    $tcEnd,
                                    (int) Mage::getStoreConfig('mytunes_settings/sox/fade_duration_default'),
                                	(boolean) Mage::getStoreConfig('mytunes_settings/sox/autorename_samples')
                                );
                                // transcode sample file to ogg
                                if (Mage::getStoreConfig('mytunes_settings/sox/transcode_ogg')) {
                                    Mage::helper('mytunes/admin')->transcodeOgg(
                                        Que_Mytunes_Model_Track::getSampleBasePath(),
                                        $albumModel->getId(),
                                        $sampleFileName
                                    );
                                }

                                // remove file extension
                                if (strrpos(strtolower($sampleFileName), '.mp3') == strlen($sampleFileName)-4) {
                                    $sampleFileName = substr($sampleFileName, 0, strlen($sampleFileName)-4);
                                }
                                // save filename in database (sample version WITHOUT extension)
                                $trackModel->setSampleFileName($sampleFileName);
                                $trackModel->setSampleStart($tcStart);
                                $trackModel->setSampleEnd($tcEnd);
                            }
                        }

                        $trackModel->save();
                    } // else $track['delete_track'] == '1'
                }
                // delete tracks
                foreach ($_itemsToDelete as $id) {
                    $trackModel = Mage::getModel('mytunes/track');
                    $trackModel->load($id)->delete();
                }
            }
        }

        return $this;
    }
}