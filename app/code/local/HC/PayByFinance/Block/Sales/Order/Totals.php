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
 * Totals on cart and checkout
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFInance_Block_Sales_Order_Totals extends Mage_Core_Block_Template
{
    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();

        $totals = array();
        $totals['finance_amount'] = array(
            'label' => Mage::helper('paybyfinance')->__('Financed Amount'),
            'before' => 'grand_total'
        );
        foreach ($totals as $code => $total) {
            if (($value = (float) $parent->getSource()->getData($code)) != 0) {
                $this->_addTotal($code, $value, $total);
            }
        }

        return $this;
    }

    /**
     * Add totals to the right place
     *
     * @param string $code   Code
     * @param string $value  Value
     * @param string $params Parameters (before, after)
     *
     * @return $this
     */
    protected function _addTotal($code, $value, $params = null)
    {
        $after = (isset($params['after']) ? $params['after'] : null);
        $before = (isset($params['before']) ? $params['before'] : null);
        $total = new Varien_Object(
            array(
                'code'      => $code,
                'label'     => $params['label'],
                'strong'    => (isset($params['strong']) ? $params['strong'] : false),
                'value'     => $value
            )
        );
        if (is_null($before)) {
            $this->getParentBlock()->addTotal($total, $after);
        } else {
            $this->getParentBlock()->addTotalBefore($total, $before);
        }

        return $this;
    }
}
