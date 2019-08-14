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
 * Hitachi Services Collection
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
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
     * @param float $minimumAmount minimum amount that service should offer
     *
     * @param float $maximalAmount maximal amount that service should offer
     *
     * @return HC_PayByFinance_Model_Mysql4_Service_Collection
     */
    public function addPriceFilter($minimumAmount, $maximalAmount = null)
    {
        $this->addFieldToFilter('min_amount', array('lteq' => $minimumAmount));

        if ($maximalAmount !== null) {
            $this->addFieldToFilter(
                array('max_amount', 'max_amount'), array(
                    array('gteq' => $maximalAmount), array('null' => true)
                )
            );
        }

        return $this;
    }

    /**
     * Add store filter to the collection
     *
     * @param integer $store Store Id
     *
     * @return HC_PayByFinance_Model_Mysql4_Service_Collection
     */
    public function storeFilter($store)
    {
        $condition = array(array('null' => true), array('eq' => $store));
        $this->addFieldToFilter('store_id', $condition);

        return $this;
    }
}
