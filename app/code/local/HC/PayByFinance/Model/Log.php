<?php
/**
* Hitachi Capital Pay By Finance
*
* Hitachi Capital Pay By Finance Extension f6f5b81
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
* Hitachi Log Model
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.healthywebsites.co.uk/license.html HWS License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Log extends Mage_Core_Model_Abstract
{
    /**
     * Mage Constructor
     *
     * @return mixed Value.
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('paybyfinance/log');
    }
}
