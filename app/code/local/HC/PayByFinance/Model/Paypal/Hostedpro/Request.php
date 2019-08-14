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
 * Overrideing Mage_Paypal_Model_Hostedpro_Request::_getOrderData()
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Paypal_Hostedpro_Request extends Mage_Paypal_Model_Hostedpro_Request
{
    /**
     * Get order request data as array
     *
     * @param Mage_Sales_Model_Order $order Order
     *
     * @return array
     */
    protected function _getOrderData(Mage_Sales_Model_Order $order)
    {
        $request = array(
            'subtotal'      => $this->_formatPrice(
                (float) $order->getBaseGrandTotal()
                - (float) $order->getBaseShippingAmount()
            ),
            'tax'           => $this->_formatPrice(0),
            'shipping'      => $this->_formatPrice($order->getBaseShippingAmount()),
            'invoice'       => $order->getIncrementId(),
            'address_override' => 'true',
            'currency_code'    => $order->getBaseCurrencyCode(),
            'buyer_email'      => $order->getCustomerEmail(),
            'discount'         => $this->_formatPrice(
                $order->getBaseGiftCardsAmount()
                + abs($order->getBaseDiscountAmount())
                + $order->getBaseCustomerBalanceAmount()
            ),
        );

        // append to request billing address data
        if ($billingAddress = $order->getBillingAddress()) {
            $request = array_merge($request, $this->_getBillingAddress($billingAddress));
        }

        // append to request shipping address data
        if ($shippingAddress = $order->getShippingAddress()) {
            $request = array_merge($request, $this->_getShippingAddress($shippingAddress));
        }

        return $request;
    }
}
