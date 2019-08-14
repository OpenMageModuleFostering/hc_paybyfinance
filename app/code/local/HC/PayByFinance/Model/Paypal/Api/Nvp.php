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
 * Overrideing Mage_Paypal_Model_Api_Nvp::call()
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{
    /**
     * Do the API call
     *
     * @param string $methodName Method name
     * @param array  $request    Request array
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    public function call($methodName, array $request)
    {
        if (isset($request['PAYMENTACTION'])
            && in_array($methodName, array('SetExpressCheckoutArray'))
            && isset($request['ITEMAMT'])
        ) {
            $amt = floatval($request['ITEMAMT']);
            $order = $this->_cart->getSalesEntity();
            $financed = $order->getFinanceAmount();
            if (!$financed) {
                $financed = $order->getShippingAddress()->getFinanceAmount();
            }
            $itemamt = $amt + $financed;
            $request['ITEMAMT'] = sprintf('%.2F', $itemamt);
        }
        return parent::call($methodName, $request);
    }
}
