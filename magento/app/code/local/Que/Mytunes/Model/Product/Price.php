<?php
/**
 * Mytunes products price model
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Product_Price extends Mage_Catalog_Model_Product_Type_Price
{

    /**
     * Retrieve product final price.
     * Override the similar Magento default method.
     * This is called in Product details page to show the final price.
     *
     * TODO: is this necessary or is the final price just used for the base product's price
     * (which is like the price of a simple product)?
     *
     * @param integer $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty=null, $product) {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = parent::getFinalPrice($qty, $product);

        $type = $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE);
        if (!is_null($type) && $type->getValue() === Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
            // use the price of the album
            $finalPrice = (float) $product->getTypeInstance(true)->getAlbum($product)->getPrice();
        }
        elseif (!is_null($type) && $type->getValue() === Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_TRACK) {
            // sum up the cost of all selected tracks
            $tracksIds = explode(",",
                    $product->getCustomOption(Que_Mytunes_Helper_Data::OPTION_MYTUNES_TRACKS_IDS)->getValue());
            $finalPrice = 0;
            foreach ($tracksIds as $t) {
                $track = Mage::getModel('mytunes/track')->load($t);
                if ($track) {
                    $finalPrice += (float) $track->getPrice();
                }
            }
        }

        return max(0, $finalPrice);
    }

}
