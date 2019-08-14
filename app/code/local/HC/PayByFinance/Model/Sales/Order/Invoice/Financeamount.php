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
 * Totals value on the cart
 *
 * @uses     Mage_Sales_Model_Order_Invoice_Total_Abstract
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Sales_Order_Invoice_Financeamount
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    protected $_collectMethods = array(
        'paypal_express'
    );

    /**
     * collect
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice Invoice object
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $helper = Mage::helper('paybyfinance');
        $order = $invoice->getOrder();
        $amount = $order->getFinanceAmount();
        if ($amount == 0) {
            return $this;
        }

        // Previous invoices.
        foreach ($order->getInvoiceCollection() as $previusInvoice) {
            if ((float) $previusInvoice->getHcfinanced() != 0
                && !$previusInvoice->isCanceled()
            ) {
                return $this;
            }
        }

        $invoice->setFinanceAmount($amount);
        $invoice->setBaseFinanceAmount($order->getBaseFinanceAmount());

        $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();

        if (Mage::getStoreConfig($helper::XML_PATH_INVOICE_FINANCE)
            || in_array($paymentMethodCode, $this->_collectMethods)
        ) {
            $invoice->setGrandTotal(
                $invoice->getGrandTotal() - abs($invoice->getFinanceAmount())
            );
            $invoice->setBaseGrandTotal(
                $invoice->getBaseGrandTotal() - abs($invoice->getBaseFinanceAmount())
            );
        }

        return $this;
    }
}
