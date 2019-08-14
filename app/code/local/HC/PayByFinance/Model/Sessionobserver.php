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
* Hitachi Session Observer
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
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

            if ($includeShipping) {
                $shippingCost = $address->getShippingAmount();
                $eligibleAmount += $shippingCost;
            }

            if (($eligibleAmount) < $minAmount) {
                $session->setData('enabled', false);
            }
        }

        return $this;
    }
}
