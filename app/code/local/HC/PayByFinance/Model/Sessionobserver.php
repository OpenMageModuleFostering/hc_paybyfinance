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
 * Hitachi Session Observer
 *
 * PHP version >= 5.4.*
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Sessionobserver
{

    /**
     * Listening to the custom event to prevent stucked sessions
     * See paybyfinance_quote_financeamount_collect_before
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return HC_PayByFinance_Model_Sessionobserver
     */
    public function collectAfter(Varien_Event_Observer $observer)
    {
        $session = $observer->getSession();
        $address = $observer->getAddress();
        $helper = Mage::helper('paybyfinance');
        $items = $address->getAllItems();
        $eligibleAmount = Mage::helper('paybyfinance/cart')->getEligibleAmount($items);
        $includeShipping = Mage::getStoreConfig(
            $helper::XML_PATH_INCLUDE_SHIPPING
        );

        if ($session->getData('enabled')) {

            $service = Mage::getModel('paybyfinance/service')->load(
                $session->getData('service')
            );

            $minAmount = $service->getMinAmount();
            $maxAmount = $service->getMaxAmount();

            if ($includeShipping) {
                $shippingCost = $address->getShippingInclTax();
                $eligibleAmount += $shippingCost;
            }

            $calculator = Mage::getSingleton('paybyfinance/calculator');
            $minInstallment = $calculator->getLowestMonthlyInstallment($eligibleAmount);
            if (!$minInstallment) {
                $session->setData('enabled', false);
            }

            if (($eligibleAmount) < $minAmount) {
                $session->setData('enabled', false);
            }

            if (($maxAmount !== null) && ($eligibleAmount > $maxAmount)) {
                $session->setData('enabled', false);
            }

            if (!$helper->isActive()) {
                $session->setData('enabled', false);
            }
        }

        return $this;
    }

    /**
     * Is the basket eligible based on minimum basket price?
     *
     * @return boolean Eligibility
     */
    protected function minBasketPriceEligible()
    {
        $helper = Mage::helper('paybyfinance');
        $cart = Mage::getModel('checkout/cart')->getQuote();
        $eligibleAmount = Mage::helper('paybyfinance/cart')
            ->getEligibleAmount($cart->getAllItems());

        $minBasketAmount = Mage::getStoreConfig(
            $helper::XML_PATH_MINIMUM_PRICE_BASKET
        );

        return ($eligibleAmount >= $minBasketAmount);
    }

    /**
     * Is finance enabled?
     *
     * @return boolean Enabled
     */
    protected function financeEnabled()
    {
        $session = $session = Mage::getSingleton('paybyfinance/session');
        return $session->getData('enabled');
    }

    /**
     * Displaying a notice on the cart page if finance enabled and financed
     * basket price is below minimum.
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return void.
     */
    public function predispatchCheckoutCartIndex(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('paybyfinance');
        $minBasketAmount = Mage::getStoreConfig(
            $helper::XML_PATH_MINIMUM_PRICE_BASKET
        );
        $minBasketAmountFormatted = Mage::helper('core')->currency($minBasketAmount, true, false);

        if ($this->financeEnabled() && !$this->minBasketPriceEligible()) {
            Mage::getSingleton('checkout/session')->addNotice(
                $helper->__(
                    'The minimum Finance Amount is %s.',
                    $minBasketAmountFormatted
                )
            );
        }
    }

    /**
     * Listening to the custom event to prevent baskets below minimum to
     * proceed to checkout.
     *
     * @param Varien_Event_Observer $observer Observer
     *
     * @return void.
     */
    public function predispatchCheckoutOnepageIndex(Varien_Event_Observer $observer)
    {
        if ($this->financeEnabled() && !$this->minBasketPriceEligible()) {
            $url = Mage::getUrl('checkout/cart/index');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }
    }
}
