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
* @copyright 2014 Healthy Websites
* @license   http://www.gnu.org/copyleft/gpl.html GPL License
* @link      http://www.healthywebsites.co.uk/
*
*/

/**
* Redirect debug preview block
*
* @uses     Mage_Adminhtml_Block_Template
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Block_Adminhtml_Paybyfinance_Redirect
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor
     *
     * @return mixed Value.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_paybyfinance_redirect';
        $this->_blockGroup = 'paybyfinance';
        $helper = Mage::helper('paybyfinance');
        $this->_headerText = $helper->__('Redirect');
        parent::__construct();
    }

}