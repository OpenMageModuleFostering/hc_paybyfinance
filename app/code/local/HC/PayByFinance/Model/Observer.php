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
 * Hitachi Observer Model
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
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
        if (!method_exists('Mage', 'getEdition') || Mage::getEdition() != 'Enterprise') {
            // Not enterprise, do nothing.
            return;
        }
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
        $serviceId = Mage::getSingleton('paybyfinance/session')->getData('service');
        $deposit = Mage::getSingleton('paybyfinance/session')->getData('deposit');
        $items = $address->getAllItems();
        $eligibleAmount = Mage::helper('paybyfinance/cart')->getEligibleAmount($items);

        $helper = Mage::helper('paybyfinance');
        $includeShipping = Mage::getStoreConfig(
            $helper::XML_PATH_INCLUDE_SHIPPING
        );
        if ($includeShipping) {
            $shippingCost = $order->getShippingInclTax();
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
            $order->setCanSendNewEmailFlag(false);
            $finance->setUpdateTotals(true);
            // Use $finance->setUpdateTotals(false) to avoid Total Due to change
            Mage::dispatchEvent(
                'paybyfinance_totals_pre_update',
                array('order' => $order, 'address' => $address, 'finance' => $finance)
            );
            $order->setFinanceAmount($amt)
                ->setBaseFinanceAmount($amt)
                ->setFinanceService($serviceId)
                ->setFinanceDeposit($deposit)
                ->setFromQuote(true);
            if ($finance->getUpdateTotals()) {
                $order->setBaseTotalDue($order->getBaseGrandTotal())
                    ->setTotalDue($order->getGrandTotal());
            }
        }
    }

    /**
     * Set paybyfinance_enable attribute on quote items based on products attribute
     *
     * @param Object $observer Observer
     *
     * @return void
     */
    public function salesQuoteItemSetPaybyfinanceEnable($observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setPaybyfinanceEnable($product->getPaybyfinanceEnable());
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
     * Sending inbound notification as documented in Section 3 of the specs.
     *
     * @param Varien_Event_Observer $observer Observer object
     *
     * @return void
     */
    public function orderShipmentAfterSave(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getShipment()->getOrder();
        $applicationNo = $order->getFinanceApplicationNo();
        if (empty($applicationNo)) {
            return;
        } else {
            $this->inboundNotification($order, 'G');
        }
    }

    /**
     * Send inbound notification when order is cancelled. Section 3 of the specs.
     *
     * @param Varien_Event_Observer $observer Observer object
     *
     * @return void
     */
    public function orderPaymentCancel(Varien_Event_Observer $observer)
    {
        $order = $observer->getPayment()->getOrder();
        $applicationNo = $order->getFinanceApplicationNo();
        if (empty($applicationNo)) {
            return;
        } else {
            $this->inboundNotification($order, 'C');
        }
    }

    /**
     * Inbound notification to be used from the above functions
     *
     * @param Mage_Sales_Model_Order $order  Order
     * @param string                 $status 'G' or 'C'
     *
     * @return void
     */
    protected function inboundNotification($order, $status)
    {
        $applicationNo = $order->getFinanceApplicationNo();
        $helper = Mage::helper('paybyfinance');
        $data = array(
            'status' => $status,
            'applicationNo' => $order->getFinanceApplicationNo(),
        );
        $post = Mage::getModel('paybyfinance/post');
        $post->setStoreId($order->getStoreId());
        $post->setPostAdapter(
            Mage::getStoreConfig($helper::XML_PATH_CONNECTION_MODE),
            $order->getStoreId()
        );

        $post->setNotificationData($data);
        $response = $post->post();
        $helper->log(
            'Inbound notification for order: ' . $order->getIncrementId() . "\n"
            . $helper->arrayDump($post->getPostAdapter()->getPostData()) . "\n"
            . 'Response: ' . $response,
            'post'
        );
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
        $financed = $order->getFinanceAmount();
        if (!$financed) {
            $financed = $order->getShippingAddress()->getFinanceAmount();
        }
        if ($financed != 0) {
            $cart->addItem(
                Mage::helper('paybyfinance')->__('Financed Amount'),
                1,
                $financed
            );
        }
        return $this;
    }

    /**
     * Calculate lowest finance instalment based on product price and available
     * services.
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return void
     */
    public function collectionLoadAfter(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('paybyfinance');
        if (!$helper->isActive()) {
            return;
        }
        $calculator = Mage::getSingleton('paybyfinance/calculator');

        Varien_Profiler::start('hc_paybyfinance_collection_load_after');
        $collection = $observer->getCollection();
        $count = 0;
        foreach ($collection as $product) {
            // Exit the loop at 30 to avoid long page load times on large collections.
            if ($count++ > 30) {
                break;
            }
            if ($helper->isProductEligible($product)) {
                $product->setData('has_finance', true);
                $price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
                $minInstallment = $calculator->getLowestMonthlyInstallment($price);
                $product->setData('finance_from_price', $minInstallment);
            }
        }
        Varien_Profiler::stop('hc_paybyfinance_collection_load_after');
    }

    /**
     * Set Analyitics Order ID.
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return void
     */
    public function googleAnalyticsSetOrderId(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('paybyfinance/session')->getData('analytics_sent')) {
            return;
        }
        $helper = Mage::helper('paybyfinance');
        $order = $helper->getOrder(Mage::app()->getRequest()->getParams());
        if ($order === null) { // try to fetch order from session, old way, compatibility...
            $order = Mage::getModel('sales/order')->load(
                Mage::getSingleton('paybyfinance/session')->getData('order_id')
            );
            $lastOrderId = $order->getId();
            if (!is_numeric($lastOrderId)) {
                return;
            }
        }


        $block = Mage::app()
            ->getFrontController()
            ->getAction()
            ->getLayout()
            ->getBlock('google_analytics');

        if ($block) {
            $block->setOrderIds(array($order->getId()));
            Mage::getSingleton('paybyfinance/session')->setData('analytics_sent', true);
        }
    }

}
