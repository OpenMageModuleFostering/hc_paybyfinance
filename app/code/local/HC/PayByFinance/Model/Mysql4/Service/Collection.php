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
* @license   http://www.healthywebsites.co.uk/license.html HWS License
* @link      http://www.healthywebsites.co.uk/
*
*/

/**
* Hitachi Services Collection
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.healthywebsites.co.uk/license.html HWS License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Mysql4_Service_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Mage constructor.
     *
     * @return mixed Value.
     */
    public function _construct()
    {
        $this->_init('paybyfinance/service');
    }

    /**
     * Add collection filter by price
     *
     * @param float $price Price.
     *
     * @return HC_PayByFinance_Model_Mysql4_Service_Collection
     */
    public function addPriceFilter($price)
    {
        $condition = array('lteq' => $price);
        $this->addFieldToFilter('min_amount', $condition);
        return $this;
    }
}