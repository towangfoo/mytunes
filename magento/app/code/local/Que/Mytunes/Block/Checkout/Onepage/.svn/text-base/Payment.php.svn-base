<?php
/**
 * Block for a payment notice
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Checkout_Onepage_Payment extends Mage_Checkout_Block_Onepage_Payment
{

    /**
     * Return true if there is a mytunes product with download option in the quote.
     *
     * @return boolean
     */
    public function isShowMytunesPaymentNotice() {
        $items = $this->getQuote()->getAllItems();
        $helper = Mage::helper('mytunes');
        foreach($items as $item) {
            if ($helper->hasDownloadOption($item->getProduct())) {
                return true;
            }
        }
        return false;
    }

}