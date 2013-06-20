<?php
/**
 * Mytunes Quote item model. This is used to handle pricing rules for the different
 * incarnations of mytunes products (simple >< download option).
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Quote_Item extends Mage_Sales_Model_Quote_Item {

    /**
     * Overriden method of Mage_Sales_Model_Quote_Item_Abstract.
     * This is where the price calculation for a quote item is done.
     *
     * TODO: It seems hacky to do this right here. Is there a better more elegant way of doing this?
     * Should be possible without rewriting/overriding Mage classes!
     *
     * @return Que_Mytunes_Model_Quote_Item $this
     */
    public function calcRowTotal() {
        // always do this!!!
        parent::calcRowTotal();

        $product = $this->getProduct();
        $typeInstance = $product->getTypeInstance(true);

        if ($typeInstance instanceof Que_Mytunes_Model_Product_Type) {
            // only do these updates for Mytunes download products!!!
            $helper = Mage::helper('mytunes');
            if ($helper->hasDownloadOption($product)) {
                // set quantity to be always 1
                $this->setQty(1);

                // set prices to the calculated price
                $baseTotal = $product->getPriceModel()->getFinalPrice(1, $product);
                $total = $this->getStore()->convertPrice($baseTotal);

                $this->setCustomPrice($this->getStore()->roundPrice($total));
                $this->setRowTotal($this->getStore()->roundPrice($total));
                $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
            }
        }

        return $this;
    }

}