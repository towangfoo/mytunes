<?php
/**
 * The main tracks block of the Mytunes Options edit tab
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Adminhtml_Catalog_Product_Edit_Tab_Mytunes_Tracks extends Mage_Adminhtml_Block_Widget
{
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('que/mytunes/product/edit/mytunes/tracks.phtml');
    }

    /**
     * Retrieve Add button HTML
     *
     * @return string $html
     */
    public function getAddButtonHtml()
    {
        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('mytunes')->__('Add New Track'),
                'id' => 'mytunes_btn_add_track',
                'class' => 'add',
            ));
        return $addButton->toHtml();
    }

    /**
     * Retrieve Upload button HTML
     *
     * @return string $html
     */
    public function getUploadButtonHtml()
    {
        $uploadButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('mytunes')->__('Upload Tracks'),
                'id' => 'mytunes_btn_massupload',
                'class' => 'go',
            ));
        return $uploadButton->toHtml();
    }

    /**
     * Retrieve Sample button HTML
     *
     * @return string $html
     */
    public function getSamplesButtonHtml()
    {
        $uploadButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('mytunes')->__('Create Samples'),
                'id' => 'mytunes_btn_createsamples',
                'class' => 'show-hide',
            ));
        return $uploadButton->toHtml();
    }

    /**
     * Get JSON for FileUploader configuration
     *
     * @return string
     */
    public function getFileUploaderConfigJson()
    {
        $this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('mytunes/adminhtml_product/upload', array('_secure' => true)));

        //var_dump($this->getConfig()->getUrl()); exit();

        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField(Que_Mytunes_Model_Track::getUploadFileIdentifier());
        $this->getConfig()->setFilters(array(
            'mp3'    => array(
                'label' => Mage::helper('adminhtml')->__('Mp3 Files'),
                'files' => array('*.mp3')
            )
        ));
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setWidth('32');
        $this->getConfig()->setHideUploadButton(true);
        return Mage::helper('core')->jsonEncode($this->getConfig()->getData());
    }

    /**
     * Get the JSON configuration settings for all JPlayer istances used to play full ans sample versions in Backend.
     */
    public function getJPlayerConfigJson()
    {
        return Mage::helper('core')->jsonEncode(array(
            'baseUrl' => str_replace("/index.php", "", Mage::getBaseUrl()), // remove the index.php, we need the real http URL here
            // 'fullRoute' => Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('mytunesadmin/admin/playfull', array('_secure' => true)),
            'sampleRoute' => 'mytunes/file/prelisten/',
        ));
    }

    /**
     * get existing track data
     *
     * @return array of JSONString
     */
    public function getExistingTracks()
    {
        $product = $this->getProduct();
        if (!$product->getId())
            return array();

        $album = $product->getTypeInstance(true)->getAlbum($product);
        if ($album === false) {
            return array();
        }

        $tracklist = $album->getTracklist();
        $result = array();
        foreach($tracklist as $track) {
            $sampleStart = $track->getSampleStart();
            $sampleEnd = $track->getSampleEnd();
            if (!$sampleStart) {
                $sampleStart = Mage::getStoreConfig('mytunes/sox/trim_start_default');
            }
            if (!$sampleEnd) {
                $sampleEnd = Mage::getStoreConfig('mytunes/sox/trim_end_default');
            }
            // cut hour, if it is "00"
            if (strlen($sampleStart) == 8 && substr($sampleStart, 0, 3) == "00:") {
                $sampleStart = substr($sampleStart, 3);
            }
            if (strlen($sampleEnd) == 8 && substr($sampleEnd, 0, 3) == "00:") {
                $sampleEnd = substr($sampleEnd, 3);
            }

            $resultItem = array(
                'track_id' => $track->getId(),
                'album_id' => $track->getAlbumId(),
                'artist' => $track->getArtist(),
                'trackname' => $track->getTrackname(),
                'track_number' => $track->getTrackNumber(),
                'single_download' => $track->getSingleDownload(),
                'downloads' => $track->getDownloads(),
                'price' => $track->getPrice(),
                'sample_start' => $sampleStart,
                'sample_end' => $sampleEnd
            );
            $fullFile = Mage::helper('mytunes/admin')->getFilePath(
                Que_Mytunes_Model_Track::getTrackBasePath(),
                $track->getFileName(),
                $track->getAlbumId()
            );
            if ($track->getFileName() && is_file($fullFile)) {
                $resultItem['file_save'] = array(
                    array(
                        'track_id' => $track->getId(),
                        'file' => $track->getFileName(),
                        'name' => Mage::helper('mytunes/admin')->getFileFromPathFile($track->getFileName()),
                        'size' => filesize($fullFile),
                        'status' => 'old',
                		'sku' => $track->getSku()
                ));
            }
            $result[] = new Varien_Object($resultItem);
        }
        return $result;
    }

    /**
     * Get default config settings for track items
     *
     * @return JSONArray
     */
    public function getTrackItemsConfigJson()
    {
        return Mage::helper('core')->jsonEncode(array(
            'trackPrice' => (string) Mage::getStoreConfig('mytunes/globals/trackprice'),
            'numDownloads' =>  (string) Mage::getStoreConfig('mytunes/globals/num_downloads_if_limited'),
            'unlimitedDownloads' =>  (boolean) Mage::getStoreConfig('mytunes/globals/unlimited_downloads'),
            'sampleStart' =>  (string) Mage::getStoreConfig('mytunes/sox/trim_start_default'),
            'sampleEnd' =>  (string) Mage::getStoreConfig('mytunes/sox/trim_end_default'),
        ));
    }

    /**
     * Get model of the product that is being edited
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Retrive config object
     *
     * @return Varien_Config
     */
    public function getConfig()
    {
        if(is_null($this->_config)) {
            $this->_config = new Varien_Object();
        }

        return $this->_config;
    }
}
