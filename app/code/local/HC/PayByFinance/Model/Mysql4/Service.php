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
* Hitachi Service Resource model
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.healthywebsites.co.uk/license.html HWS License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Mysql4_Service
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Magento constructor
     *
     * @return mixed Value.
     */
    public function _construct()
    {
        $this->_init('paybyfinance/service', 'service_id');
    }
}
