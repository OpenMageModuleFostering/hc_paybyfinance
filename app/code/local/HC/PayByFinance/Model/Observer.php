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
* Hitachi Observer Model
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Observer
{
    /**
     * Clearing enterprise full page cache after saving a service
     *
     * @param Object $observer Observer object
     *
     * @return void
     */
    public function enterpriseCacheClear($observer)
    {
        $cache = Mage::getModel('enterprise_pagecache/cache');
        if ($cache) {
            $cache::getCacheInstance()->clean();
        }
    }

    /**
     * Converting address data into a quote
     *
     * @param Object $observer Observer object
     *
     * @return void
     */
    public function convertQuoteAddressToOrder(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('paybyfinance/session')->getData('enabled')) {
            return;
        }

        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();
        $financeAmount = $address->getFinanceAmount();
        $serviceId = Mage::getSingleton('paybyfinance/session')->getData('service');
        $deposit = Mage::getSingleton('paybyfinance/session')->getData('deposit');
        $items = $address->getAllItems();
        $eligibleAmount = Mage::helper('paybyfinance/cart')->getEligibleAmount($items);

        $helper = Mage::helper('paybyfinance');
        $includeShipping = Mage::getStoreConfig(
            $helper::XML_PATH_INCLUDE_SHIPPING
        );
        if ($includeShipping) {
            $shippingCost = $address->getShippingAmount();
            $eligibleAmount += $shippingCost;
        }

        $calculator = Mage::getModel('paybyfinance/calculator');
        $calculator->setService($serviceId)
            ->setDeposit($deposit)
            ->setAmount($eligibleAmount)
            ->setDiscount($address->getDiscountAmount())
            ->setGiftcard($address->getGiftCardsAmount());
        $finance = $calculator->getResults();

        $amt = $finance->getFinanceAmount() * -1;
        if ($amt) {
            $order->setFinanceAmount($amt)
                ->setBaseFinanceAmount($amt)
                ->setFinanceService($serviceId)
                ->setFinanceDeposit($deposit)
                ->setBaseTotalDue($order->getBaseGrandTotal())
                ->setTotalDue($order->getGrandTotal())
                ->setFromQuote(true);
        }
    }

    /**
     * Order save event. Responds only if it's the first save after converted from quote
     * See $order->setFromQuote() in function convertQuoteAddressToOrder()
     *
     * @param Object $observer Observer object
     *
     * @return void
     */
    public function orderAfterSave(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $serviceId = $order->getFinanceService();

        if (!$order->getFromQuote() || empty($serviceId)) {
            return;
        }

        $service = Mage::getModel('paybyfinance/service')->load($serviceId);

        $order_id = $order->getId();
        $order->setStatus('pending');
        $order->setFromQuote(false);
        $order->save();
    }

    /**
     * Checkout success event to redirect to Hitachi
     *
     * @param Object $observer Observer object
     *
     * @return void
     */
    public function checkoutSuccess(Varien_Event_Observer $observer)
    {
        $lastOrderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
        if (!is_numeric($lastOrderId)) {
            return;
        }

        $order = Mage::getModel('sales/order');
        $order->load($lastOrderId);

        if (!$order->getFinanceService()) {
            return;
        }

        $payment = $order->getPayment();

        if (!$payment) {
            return;
        }

        Mage::getSingleton('paybyfinance/session')->setData('order_id', $lastOrderId);

        Mage::app()->getResponse()->setRedirect(
            Mage::getUrl('paybyfinance/checkout/redirectform', array('_secure' => true))
        );
    }

    /**
     * Get Finance information to the Payment Information block.
     * Note this only works with a few payment methods like cc, but not with checkmo.
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return HC_PayByFinance_Model_Observer
     */
    public function getPaymentInfo(Varien_Event_Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $transport = $observer->getEvent()->getTransport();
        $order = $payment->getOrder();

        if ($order->getFinanceService()) {
            $serviceId = $order->getFinanceService();
            $service = Mage::getModel('paybyfinance/service')->load($serviceId);
            $transport->setData(
                'Finance Service',
                'ID:' . $serviceId . ' - ' . $service->getName()
            );
            $transport->setData('Finance Deposit', $order->getFinanceDeposit() . '%');
            $transport->setData('Finance Amount', $order->getFinanceAmount());
            $transport->setData('Finance Status', $order->getFinanceStatus());
            $transport->setData('Finance applicationNo', $order->getFinanceApplicationNo());
        }
        return $this;
    }

    /**
     * PayPal arbitrary items, total modifications (adds finance amount)
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return HC_PayByFinance_Model_Observer
     */
    public function paypalPrepareLineItems(Varien_Event_Observer $observer)
    {
        $cart = $observer->getPaypalCart();
        $order = $cart->getSalesEntity();
        $address = $order->getShippingAddress();
        $cart->addItem(
            Mage::helper('paybyfinance')->__('Financed Amount'),
            1,
            $address->getFinanceAmount()
        );
        return $this;
    }

}
