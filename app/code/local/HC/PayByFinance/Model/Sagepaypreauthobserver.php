<?php
/**
 * Hitachi Capital Pay By Finance
 *
 * Hitachi Capital Pay By Finance modifgications for Sagepaypreauth
 *
 * PHP version >= 5.4.*
 *
 * @category  HC
 * @package   Sagepaypreauth
 * @author    Cohesion Digital <support@cohesiondigital.co.uk>
 * @copyright 2015 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.cohesiondigital.co.uk/
 *
 */

/**
 * Observer Model
 *
 * @category HC
 * @package  Sagepaypreauth
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Sagepaypreauthobserver
{
    /**
     * Get payment action
     *
     * @param Object $observer Observer object
     *
     * @see Ebizmarts_SagePaySuite_Model_Api_Payment::getConfigData()
     * @return void
     */
    public function getPaymentAction($observer)
    {
        if (!$this->isModuleEnabled()) {
            return;
        }

        if (!Mage::getSingleton('paybyfinance/session')->getData('enabled')) {
            return;
        }

        $confobject = $observer->getConfobject();
        $confobject->value = Ebizmarts_SagePaySuite_Model_Api_Payment::REQUEST_TYPE_DEFERRED;
    }

    /**
     * Shipment event to create invoice, capture online for financed orders
     *
     * @param Object $observer Observer object
     *
     * @return void
     */
    public function orderSaveAfter($observer)
    {
        if (!$this->isModuleEnabled()) {
            return;
        }

        $order = $observer->getOrder();

        if ($order->getFinanceStatus() == 'ACCEPT'
            && $order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING
            && !$order->hasInvoices()
        ) {
            if (!$order->canInvoice()) {
                Mage::log(
                    'Can\'t create invoice automatically for '
                    . $order->getIncrementId(), null, 'invoice.log'
                );
                return;
            }
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::log(
                    'Can\'t create invoice without products '
                    . $order->getIncrementId(), null, 'invoice.log'
                );
                return;
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)

                ->addObject($invoice->getOrder());

            $transactionSave->save();
            Mage::log(
                'Invoiced automatically: ' . $order->getIncrementId()
                . ' Invoice: ' . $invoice->getIncrementId(), null, 'invoice.log'
            );
        } else {
            Mage::log(
                'Not a financed order or finance is not "ACCEPT": '
                . $order->getIncrementId(), null, 'invoice.log'
            );
        }
    }

    /**
     * Is SagePay preauth submodule enabled in config?
     *
     * @return bool
     */
    private function isModuleEnabled()
    {
        $helper = Mage::helper('paybyfinance');
        return (((boolean) Mage::getStoreConfig($helper::XML_PATH_SAGEPAY_PREAUTH)) == true) ;
    }

}
