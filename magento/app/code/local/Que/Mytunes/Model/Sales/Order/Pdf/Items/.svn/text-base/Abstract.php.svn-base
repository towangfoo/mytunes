<?php
/**
 * Order Downloadable Pdf Items renderer
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
abstract class Que_Mytunes_Model_Sales_Order_Pdf_Items_Abstract extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    /**
     * Get a string containing the download type
     *
     * @return String | false
     */
    protected function getDownloadType()
    {
        $item = $this->getItem();
        if ($this->hasDownloadOptions($item)) {
            $options = $item->getOrderItem()->getProductOptions();
            $type = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE];

            $label = "";
            if ($type == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
                $label = 'Complete Album Download';
            }
            elseif ($type == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
                $label = 'Download of Track(s):';
            }
            return Mage::helper('mytunes')->__($label);
        }
        return false;
    }

    /**
     * Does this order item have download options?
     * (Or is it a Mytunes product, but was ordered as the physical product?)
     *
     * @return boolean
     */
    protected function hasDownloadOptions()
    {
        $item = $this->getItem();
        $options = $item->getOrderItem()->getProductOptions();
        return isset($options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE]);
    }

    /**
     * Has this item a list of download tracks?
     *
     * @return boolean
     */
    protected function hasTracks()
    {
        $item = $this->getItem();
        $options= $item->getOrderItem()->getProductOptions();
        $type = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE];
        if ($type !== Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
            return false;
        }
        return isset($options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS]);
    }

    /**
     * Get the track list
     *
     * @return array Que_Mytunes_Model_Track | false
     */
    protected function getTracks()
    {
        $item = $this->getItem();
        if (!$this->hasTracks($item)) {
            return false;
        }
        $options = $item->getOrderItem()->getProductOptions();
        $trackIds = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS];
        $result = array();
        foreach ($trackIds as $id) {
            $result[] = Mage::getModel('mytunes/track')->load($id);
        }
        return $result;
    }
}
