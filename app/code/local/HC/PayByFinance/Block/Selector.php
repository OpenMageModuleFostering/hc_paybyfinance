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
 * Selector block class
 *
 * @uses     Mage_Core_Block_Template
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Block_Selector extends Mage_Core_Block_Template
{

    protected $_services;
    protected $_amount;

    /**
     * Get available services
     *
     * @return HC_PayByFinance_Model_Mysql4_Service_Collection Services.
     */
    public function getServices()
    {
        if (!isset($this->_services)) {
            $amount = $this->getAmount();
            $this->_services = Mage::getModel('paybyfinance/service')
                ->getCollection()
                ->storeFilter(Mage::app()->getStore()->getStoreId())
                ->addPriceFilter($amount)
                ->load();
        }

        return $this->_services;
    }

    /**
     * getAmount
     *
     * @return float Amount.
     */
    protected function getAmount()
    {
        if (!isset($this->_amount)) {
            if ($product = Mage::registry('current_product')) {
                // We are on the product page
                $this->_amount = $product->getFinalPrice();
            } else {
                $cartHelper = Mage::helper('paybyfinance/cart');
                $this->_amount = $cartHelper->getEligibleAmount()
                    + $cartHelper->getQuoteAdditionalAmount();
            }
        }

        return $this->_amount;
    }

    /**
     * getJSON
     *
     * @return string Value.
     */
    public function getJSON()
    {
        $helper = Mage::helper('paybyfinance');

        return $helper->getSelectorJSON(
            $this->getServices(), $this->getAmount()
        );
    }

    /**
     * get current product or false if not on product page
     *
     * @return mixed Value.
     */
    public function getProduct()
    {
        $product = Mage::registry('current_product');

        return $product;
    }

    /**
     * Fixed deposit only
     *
     * @return integer 0 if No, 1 if Yes
     */
    public function getIsFixedDepositOnly()
    {
        $helper = Mage::helper('paybyfinance');
        $fixed = Mage::getStoreConfig($helper::XML_PATH_FIXED_DEPOSIT);
        return $fixed;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $helper = Mage::helper('paybyfinance');
        if (!$helper->isActive()) {
            return '';
        }
        if (!$this->getProduct() && $this->getServices()->getSize() == 0) {
            $this->setTemplate('paybyfinance/selector-no.phtml');
        }

        return parent::_toHtml();
    }

    /**
     * Get Retailer Name as appears on FCA register
     *
     * @return string Retailer Name
     */
    public function getRetailerName()
    {
        $helper = Mage::helper('paybyfinance');
        $name = Mage::getStoreConfig($helper::XML_PATH_ACCOUNT_RETAILERNAME);
        return $name;
    }

    /**
     * Get Trading Name
     *
     * @return string Trading Name
     */
    public function getTradingName()
    {
        $helper = Mage::helper('paybyfinance');
        $name = Mage::getStoreConfig($helper::XML_PATH_ACCOUNT_TRADINGNAME);
        return $name;
    }

}
