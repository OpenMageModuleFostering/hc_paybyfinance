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
 * Overrideing Mage_Paypal_Model_Api_Nvp::call()
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
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
            && $request['PAYMENTACTION'] == 'Authorization'
            && isset($request['ITEMAMT'])
            && isset($request['TOKEN'])
        ) {
            $amt = floatval($request['ITEMAMT']);
            $order = $this->_cart->getSalesEntity();
            $financed = ($order->getFinanceAmount());
            $itemamt = $amt + $financed;
            $request['ITEMAMT'] = sprintf('%.2F', $itemamt);
        }

        return parent::call($methodName, $request);
    }
}
