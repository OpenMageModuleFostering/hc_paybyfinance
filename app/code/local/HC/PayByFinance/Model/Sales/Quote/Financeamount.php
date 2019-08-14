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
 * Totals value on the cart
 *
 * @uses     Mage_Sales_Model_Quote_Address_Total_Abstract
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Sales_Quote_Financeamount
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setCode('financeamount');
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
        $address->setFinanceAmount(0);

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        $session = Mage::getSingleton('paybyfinance/session');

        Mage::dispatchEvent(
            'paybyfinance_quote_financeamount_collect_before', array(
                'address' => $address,
                'session' => $session
            )
        );

        if ($session->getData('enabled')) {
            $calculator = Mage::getModel('paybyfinance/calculator');
            $cartHelper = Mage::helper('paybyfinance/cart');

            $helper = Mage::helper('paybyfinance');
            $includeShipping = Mage::getStoreConfig(
                $helper::XML_PATH_INCLUDE_SHIPPING
            );

            $shippingCost = 0;
            if ($includeShipping) {
                $shippingCost = $address->getShippingInclTax();
            }


            $calculator
                ->setService($session->getData('service'))
                ->setDeposit($session->getData('deposit'))
                ->setAmount($cartHelper->getEligibleAmount() + $shippingCost)
                ->setDiscount($address->getDiscountAmount())
                ->setGiftcard($address->getGiftCardsAmount());
            $finance = $calculator->getResults();

            $amt = ($finance->getFinanceAmount() * -1);
            $address->setFinanceAmount($amt);
            $address->setBaseFinanceAmount($amt);
            $address->setGrandTotal($address->getGrandTotal() + $address->getFinanceAmount());
            $address->setBaseGrandTotal(
                $address->getBaseGrandTotal() + $address->getBaseFinanceAmount()
            );
            Mage::dispatchEvent(
                'paybyfinance_quote_financeamount_collect_after', array(
                    'address' => $address,
                    'amt'     => $amt
                )
            );
        }

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
        $amount = $address->getFinanceAmount();
        if ($amount != 0 && $address->getAddressType() == 'shipping') {
            $address->addTotal(
                array(
                    'code' => 'financeamount',
                    'title' => Mage::helper('paybyfinance')->__('Financed Amount'),
                    'value' => $amount
                )
            );
        }

        return $this;
    }
}
