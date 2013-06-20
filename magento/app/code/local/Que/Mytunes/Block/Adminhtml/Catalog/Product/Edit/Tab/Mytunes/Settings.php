<?php
/**
 * The settings block in the Mytunes options edit tab
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Adminhtml_Catalog_Product_Edit_Tab_Mytunes_Settings extends Mage_Adminhtml_Block_Widget
{
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('que/mytunes/product/edit/mytunes/settings.phtml');
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
     * get select field for mytunes_enable_player
     *
     * @return string
     */
    public function getEnablePlayerSelect()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setName('product[mytunes_enable_player]')
            ->setId('mytunes_mytunes_enable_player')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray())
            ->setValue($this->getProduct()->getMytunesEnablePlayer());

        return $select->getHtml();
    }

    /**
     * get select field for mytunes_enable_downloads
     *
     * @return string
     */
    public function getEnableDownloadsSelect()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setName('product[mytunes_enable_downloads]')
            ->setId('mytunes_mytunes_enable_downloads')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray())
            ->setValue($this->getProduct()->getMytunesEnableDownloads());

        return $select->getHtml();
    }

    /**
     * get select field for mytunes_enable_albumdownload
     *
     * @return string
     */
    public function getEnableAlbumdownloadSelect()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setName('product[mytunes_enable_albumdownload]')
            ->setId('mytunes_mytunes_enable_albumdownload')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray())
            ->setValue($this->getProduct()->getMytunesEnableAlbumdownload());

        return $select->getHtml();
    }

    /**
     * get the default value for the price of a complete album download
     *
     * @return float
     */
    public function getDefaultAlbumPrice() {
        return (float) Mage::getStoreConfig('mytunes/globals/albumprice');
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
