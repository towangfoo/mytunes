<?php
/**
 * Created on 03.02.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Que_Mytunes_Model_System_Config_Source_Restrictpayments
{

    protected $_options;

    protected $_storeCode = Mage_Core_Model_Store::ADMIN_CODE;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $store = Mage::app()->getStore($this->_storeCode);
            $this->_options = Mage::helper('mytunes')->getPaymentMethodOptions($store->getId());
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }

}