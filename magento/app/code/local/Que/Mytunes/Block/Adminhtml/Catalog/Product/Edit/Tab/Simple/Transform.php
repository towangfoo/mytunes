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
class Que_Mytunes_Block_Adminhtml_Catalog_Product_Edit_Tab_Simple_Transform extends Mage_Adminhtml_Block_Widget
{
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('que/mytunes/product/edit/simple/transform.phtml');
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
     * Get HTML code for a select box whether to keep th eoriginal simple product.
     */
    public function getKeepSimpleSelect()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setName('mytunes[transform][keepsimple]')
            ->setId('mytunes_transform_keepsimple')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray())
            ->setValue(false);

        return $select->getHtml();
    }

    /**
     * Get HTML for submit button
     */
    public function getSubmitButton()
    {
        $uploadButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('mytunes')->__('Transform Product'),
                'id' => 'mytunes_transform_btn_submit',
                'class' => 'go',
            ));
        return $uploadButton->toHtml();
    }

    /**
     * Get JSON config settings for transformation Javascript
     */
    public function getTransformConfigJson()
    {
        return Mage::helper('core')->jsonEncode(array(
            'actionUrl' => Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('mytunes/adminhtml_product/transform', array('_secure' => true)),
        ));
    }
}
