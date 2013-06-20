<?php
/**
 * The block showing Mytunes GEMA report form.
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Block_Adminhtml_Sales_Report_Gema extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_headerText;

    public function __construct()
    {
        $this->_headerText = Mage::helper('mytunes')->__('Mytunes GEMA Report');
        parent::__construct();
        $this->setTemplate('que/mytunes/sales/report/gema.phtml');
    }

    /**
     * Get the collection of all items
     *
     * @return Que_Mytunes_Model_Sales_Report_Gema
     */
    public function getReportCollection()
    {
        $report = Mage::getModel('mytunes/sales_report_gema');
        $report->setFrom('2011-01-01')->setTo('2011-01-31')->loadReport();
        return $report;
    }

    public function getHeaderHtml()
    {
        return '<h3 class="icon-head head-report-product-viewed">' . $this->_headerText . '</3>';
    }
}