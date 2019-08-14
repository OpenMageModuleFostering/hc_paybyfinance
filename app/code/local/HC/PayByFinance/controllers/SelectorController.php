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
 * Controller for selector operations
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_SelectorController extends Mage_Core_Controller_Front_Action
{
    protected $_services;
    protected $_amount;

    /**
     * Displaying the services as JSON via an AJAX call.
     *
     * @return void.
     */
    public function servicesAction()
    {
        $helper = Mage::helper('paybyfinance');

        $json = $helper->getSelectorJSON(
            $this->getServices(), $this->getAmount()
        );

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }

    /**
     * getAmount
     *
     * @return float Amount.
     */
    protected function getAmount()
    {
        if (!isset($this->_amount)) {
            $cartHelper = Mage::helper('paybyfinance/cart');
            $this->_amount = $cartHelper->getEligibleAmount()
                + $cartHelper->getQuoteAdditionalAmount();
        }

        return $this->_amount;
    }

    /**
     * Get available services
     *
     * @return HC_PayByFinance_Model_Mysql4_Service_Collection Services.
     */
    protected function getServices()
    {
        if (!isset($this->_services)) {
            $amount = $this->getAmount();
            $this->_services = Mage::getModel('paybyfinance/service')
                ->getCollection()
                ->storeFilter(Mage::app()->getStore()->getStoreId())
                ->addPriceFilter($amount, $amount)
                ->load();
        }

        return $this->_services;
    }
}
