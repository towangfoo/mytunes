<?php
/**
 * The main block to add the Mytunes Options tab to the product edit page.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Adminhtml_Catalog_Product_Edit_Tab_Simple
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
//        $this->setSkipGenerateContent(true);
        $this->setTemplate('que/mytunes/product/edit/simple.phtml');
    }

    /**
     * Retrieve product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('mytunes')->__('Mytunes Options');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('mytunes')->__('Mytunes Options');
    }

    /**
     * Check if tab can be displayed
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')
            ->setId('mytunesTransform');

        $accordion->addItem('transform', array(
            'title'   => Mage::helper('mytunes')->__('Transform Simple Product to Mytunes Product'),
            'content' => $this->getLayout()->createBlock('mytunes/adminhtml_catalog_product_edit_tab_simple_transform')->toHtml(),
            'open'    => true,
        ));

        $this->setChild('accordion', $accordion);

        return parent::_toHtml();
    }

}
