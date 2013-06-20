<?php
/**
 * Shopping cart downloadable item render block
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{

    /**
     * does this item have a download option?
     *
     * @return boolean
     */
    public function hasMytunesDownloadOption() {
        return Mage::helper('mytunes')->hasDownloadOption($this->getItem()->getProduct());
    }

    /**
     * Get a translated label for the download option type.
     *
     * @return string
     */
    public function getDownloadOptionType() {
        $type = $this->getItem()->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
        if ($type === null) {
            return false;
        } else {
            $label = null;
            if ($type->getValue() == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
                $label = 'Complete Album Download';
            }
            elseif ($type->getValue() == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
                $label = 'Download of Track(s):';
            }
            return Mage::helper('mytunes')->__($label);
        }
    }

    /**
     * Has this item a list of download tracks?
     *
     * @return boolean
     */
    public function hasTracks() {
        $type = $this->getItem()->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
        if ($type->getValue() !== Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
            return false;
        }
        $trackIds = $this->getItem()->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS);
        if ($trackIds === null) {
            return false;
        }
        return true;
    }

    /**
     * Get the list of download tracks for this item or false, when there
     * are no tracks for this item.
     *
     * @return array of Que_Mytunes_Model_Track || false
     */
    public function getTracks() {
        if (!$this->hasTracks()) {
            return false;
        }
        $trackIds = $this->getItem()->getProduct()->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS)
                ->getValue();
        $ids = explode(",", $trackIds);
        $result = array();
        foreach ($ids as $id) {
            $result[] = Mage::getModel('mytunes/track')->load($id);
        }
        return $result;
    }

    /**
     * Get the mytunes artist name from the current item
     *
     * @return string
     */
    public function getMytunesArtist() {
        $item = $this->getItem();
        $product = $item->getProduct()->load($item->getProduct()->getId());
        return $product->getMytunesArtist();
    }

}