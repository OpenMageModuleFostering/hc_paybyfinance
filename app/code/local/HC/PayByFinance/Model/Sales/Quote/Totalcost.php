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
* Total Cost value on the cart
*
* @uses     Mage_Sales_Model_Quote_Address_Total_Abstract
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Sales_Quote_Totalcost
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setCode('paybyfinance_totalcost');
    }

    /**
     * collect
     *
     * @param Mage_Sales_Model_Quote_Address $address Address object
     *
     * @return self $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }

    /**
     * fetch
     *
     * @param Mage_Sales_Model_Quote_Address $address Address object
     *
     * @return self $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $session = Mage::getSingleton('paybyfinance/session');
        $amount = $address->getQuote()->getSubtotal()
            + $address->getShippingAmount()
            + $address->getDiscountAmount()
            - $address->getGiftCardsAmount();
        if ($session->getData('enabled') && $address->getAddressType() == 'shipping') {
            $address->addTotal(
                array(
                    'code' => 'paybyfinance_totalcost',
                    'title' => Mage::helper('paybyfinance')->__('Total Cost'),
                    'value' => $amount
                )
            );
        }

        return $this;
    }
}
