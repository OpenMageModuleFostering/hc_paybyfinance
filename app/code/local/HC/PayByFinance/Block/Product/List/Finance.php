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
 * Finance display on product lists
 *
 * @uses     Mage_Core_Block_Template
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Block_Product_List_Finance extends Mage_Core_Block_Template
{

    /**
     * Current product has finance?
     *
     * @return bool
     */
    public function hasFinance()
    {
        $product = $this->getProduct();
        $finaceFromPrice = $product->getData('finance_from_price');

        return (bool) $finaceFromPrice;
    }

    /**
     * Format the minimum installment price
     *
     * @return string
     */
    public function getFinanceFromPrice()
    {
        $product = $this->getProduct();
        $finaceFromPrice = $product->getData('finance_from_price');
        $price = Mage::helper('core')->currency($finaceFromPrice, true, false);

        return $price;
    }
}
