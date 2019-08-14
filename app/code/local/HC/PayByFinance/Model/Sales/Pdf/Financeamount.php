<?php
/**
 * Hitachi Capital Pay By Finance
 *
 * Hitachi Capital Pay By Finance Extension
 *
 * PHP version >= 5.4.*
 *
 * @category  HC
 * @package   PayByFinance
 * @author    Cohesion Digital <support@cohesiondigital.co.uk>
 * @copyright 2014 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.cohesiondigital.co.uk/
 *
 */

/**
 * Totals value on invoice PDF
 *
 * @uses     Mage_Sales_Model_Order_Pdf_Total_Default
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Sales_Pdf_Financeamount extends Mage_Sales_Model_Order_Pdf_Total_Default
{

    /**
     * Get totals array to display
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        // getAmount will automatically get the value of the total
        // its value id defined source_field in config.xml
        $amount = $this->getAmount();
        $label = Mage::helper('paybyfinance')->__('Financed Amount').':';
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = array(
            'amount'    => Mage::helper('core')->currency($amount, true, false),
            'label'     => $label,
            'font_size' => $fontSize
        );
        return array($total);
    }
}
