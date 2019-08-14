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
 * Data helper for cart functions
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Helper_Cart extends Mage_Core_Helper_Data
{

    protected $_eligibleProducts;

    /**
     * getEligibleProducts
     *
     * @param Collection $items Product collection or null
     *
     * @return array Eligible products.
     */
    public function getEligibleProducts($items = null)
    {
        if (isset($this->_eligibleProducts)) {
            return $this->_eligibleProducts;
        }
        if ($items === null) {
            $cart = Mage::getModel('checkout/cart')->getQuote();
            $items = $cart->getAllItems();
        }
        $helper = Mage::helper('paybyfinance');
        $eligible = array();

        foreach ($items as $item) {
            if ($helper->isProductEligible($item)) {
                $eligible[] = $item;
            }
        }
        $this->_eligibleProducts = $eligible;

        return $eligible;
    }

    /**
     * Get any additional price into the eligible amount
     *
     * @return double Additional price
     */
    public function getQuoteAdditionalAmount()
    {
        $additional = 0;
        $helper = Mage::helper('paybyfinance');

        $shippingAddress = Mage::getModel('checkout/cart')->getQuote()->getShippingAddress();
        if (Mage::getStoreConfig($helper::XML_PATH_INCLUDE_SHIPPING)) {
            $additional += $shippingAddress->getShippingInclTax();
        }
        $additional += $shippingAddress->getDiscountAmount();
        $additional -= $shippingAddress->getGiftCardsAmount();

        return $additional;
    }

    /**
     * getEligibleAmount
     *
     * @param Collection $items Product collection or null
     *
     * @return float Sum of subtotal of eligible products.
     */
    public function getEligibleAmount($items = null)
    {
        $amount = 0;
        $items = $this->getEligibleProducts($items);
        foreach ($items as $item) {
            $amount += $item->getRowTotalInclTax();
        }
        return $amount;
    }

    /**
     * getEligibleAmount for redirect POST. This was needed because of rounding conflict HC-469
     *
     * @param Collection $items Product collection or null
     *
     * @return float Sum of price * qty of eligible products.
     */
    public function getEligibleAmountForRedirect($items = null)
    {
        $amount = 0;
        $items = $this->getEligibleProducts($items);
        foreach ($items as $item) {
            if ($item->getQty() != 0) {
                $qty = $item->getQty();
            } else {
                $qty = $item->getQtyOrdered();
            }
            // Line subtotal is always rounded down for Hitachi POST
            $price = $qty * $item->getPriceInclTax();
            // PHP Float issues workaround: convert to string first.
            // Note it would be better to use BCMath as an additional dependency.
            $amount += intval((string) ($price * 100)) / 100;
        }
        return $amount;
    }

}
