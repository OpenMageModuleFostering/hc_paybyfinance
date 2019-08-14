<?php
/**
 * Initializes new order status by configuration
 *
 * PHP version >= 5.4.*
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */

/**
 * Initializes new order status by configuration
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Sagepayorderstatusobserver
{

    /**
     * Called when order is placed and sets configured status
     *
     * @param Varien_Event_Observer $observer observer
     *
     * @return void
     */
    public function salesOrderPaymentPlaceEnd(Varien_Event_Observer $observer)
    {
        if (!$this->isPBFActiveForThisOrder() || !$this->isModuleEnabled()) {
            return;
        }
        $payment = $observer->getEvent()->getPayment();
        if ($payment) {
            $order = $payment->getOrder();
            if ($order->getFinanceService()) {
                $status = $this->getNewStatusValue();
                $order->setStatus($status);
            }
        }
    }

    /**
     * Sets configured order status value to passed observer object
     *
     * @param Varien_Event_Observer $observer $observer
     *
     * @return void
     */
    public function sagepaysuiteGetConfigvalueOrderStatus(Varien_Event_Observer $observer)
    {
        if (!$this->isPBFActiveForThisOrder() || !$this->isModuleEnabled()) {
            return;
        }

        $confObject = $observer->getConfobject();
        $confObject->value=$this->getNewStatusValue();
    }


    /**
     * Is current order financed? Ugly access to session ;-(
     *
     * @return mixed
     */
    private function isPBFActiveForThisOrder()
    {
        return (Mage::getSingleton('paybyfinance/session')->getData('enabled'));
    }

    /**
     * Is SagePay order status initiator submodule enabled in config?
     *
     * @return bool
     */
    private function isModuleEnabled()
    {
        $helper = Mage::helper('paybyfinance');
        return (((boolean) Mage::getStoreConfig($helper::XML_PATH_SAGEPAY_INITIATOR)) == true) ;
    }

    /**
     * Returns configured value for status of newly created financed orders
     *
     * @return mixed
     */
    protected function getNewStatusValue()
    {
        return Mage::getStoreConfig('payment/sagepayserver/order_status_financed');
    }

}
