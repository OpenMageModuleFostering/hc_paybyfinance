<?php
/**
 * Hitachi Capital Pay By Finance
 *
 * Hitachi Capital Pay By Finance Extension
 *
 * PHP version >= 5.3.*
 *
 * @category  HC
 * @package   PayByFinance
 * @author    Healthy Websites <support@healthywebsites.co.uk>
 * @copyright 2014 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.healthywebsites.co.uk/
 *
 */

/**
 * Totals value on the cart
 *
 * @uses     Mage_Sales_Model_Order_Invoice_Total_Abstract
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Model_Sales_Order_Invoice_Financeamount
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * collect
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice Invoice object
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $amount = $order->getFinanceAmount();
        if ($amount == 0) {
            return $this;
        }

        // Previous invoices.
        foreach ($order->getInvoiceCollection() as $previusInvoice) {
            if ((float) $previusInvoice->getHcfinanced() != 0 && !$previusInvoice->isCanceled()) {
                return $this;
            }
        }

        $invoice->setFinanceAmount($amount);
        $invoice->setBaseFinanceAmount($order->getBaseFinanceAmount());

        $invoice->setGrandTotal(
            $invoice->getGrandTotal() - abs($invoice->getFinanceAmount())
        );
        $invoice->setBaseGrandTotal(
            $invoice->getBaseGrandTotal() - abs($invoice->getBaseFinanceAmount())
        );

        return $this;
    }
}
