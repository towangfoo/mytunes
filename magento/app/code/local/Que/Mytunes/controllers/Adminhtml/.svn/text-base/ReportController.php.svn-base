<?php
/**
 * Admin Controller for Mytunes specific actions
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{

    /**
     * The controller action for Mytunes GEMA reports.
     */
    public function gemareportAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('mytunes/adminhtml_sales_report_gema'));
        $this->renderLayout();
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mytunes/adminhtml_reports');
    }

}