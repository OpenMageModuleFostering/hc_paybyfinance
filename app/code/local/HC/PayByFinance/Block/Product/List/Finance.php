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
 * Finance display on product lists
 *
 * @uses     Mage_Core_Block_Template
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Block_Product_List_Finance extends Mage_Core_Block_Template
{
    private $_canDisplayFinance = null;

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

    /**
     * Whether to display finance text under product in search result and category or not
     *
     * @return boolean
     */
    public function canDisplayFinance()
    {
        if ($this->_canDisplayFinance === null) {
            $helper = Mage::helper('paybyfinance');
            $this->_canDisplayFinance = (Mage::getStoreConfig(
                $helper::XML_PATH_IN_BLOCK_DISPLAY
            ) == 1);
        }

        return $this->_canDisplayFinance;
    }
}
