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
* Totals value on the credit memo
*
* @uses     Mage_Sales_Model_Order_Invoice_Total_Abstract
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Sales_Order_Creditmemo_FInanceamount
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * collect
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo Credit Memo object
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $amount = $order->getFinanceAmount();
        if ($amount == 0) {
            return $this;
        }

        // Previous credit Memos.
        foreach ($order->getCreditmemosCollection() as $previusMemo) {
            if ((float) $previusMemo->getHcfinanced() != 0 && !$previusMemo->isCanceled()) {
                return $this;
            }
        }

        $creditmemo->setFinanceAmount($amount);
        $creditmemo->setBaseFinanceAmount($order->getBaseFinanceAmount());

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getFinanceAmount());
        $creditmemo->setBaseGrandTotal(
            $creditmemo->getBaseGrandTotal() - $creditmemo->getBaseFinanceAmount()
        );

        return $this;
    }
}
