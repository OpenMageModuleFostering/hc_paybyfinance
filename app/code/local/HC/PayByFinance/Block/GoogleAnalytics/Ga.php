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
 * @copyright 2016 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.cohesiondigital.co.uk/
 *
 */


/**
 * Send financed amount and grand total as value of order to Google Analytics
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Block_GoogleAnalytics_Ga extends Mage_GoogleAnalytics_Block_Ga
{

    /**
     * Older Magento versions has this method only
     *
     * @return string|void
     */
    protected function _getOrdersTrackingCode()
    {
        $helper = $this->helper('googleanalytics');
        if ($helper && method_exists($helper, 'isUseUniversalAnalytics')) {
            if ($helper->isUseUniversalAnalytics()) {
                return $this->_getOrdersTrackingCodeUniversal();
            }
        }

        return $this->_getOrdersTrackingCodeAnalytics();
    }

    /**
     * Generates code for Universal GA
     *
     * @return string
     */
    protected function _getOrdersTrackingCodeUniversal()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        $result[] = "ga('require', 'ecommerce')";
        foreach ($collection as $order) {
            $baseGrandTotal = $order->getBaseGrandTotal();
            //Adding financed amount in case of financed order
            $financeAmount = $order->getFinanceAmount();
            if ($financeAmount) {
                $baseGrandTotal += $financeAmount;
            }

            $result[] = sprintf(
                "ga('ecommerce:addTransaction', {
                    'id': '%s',
                    'affiliation': '%s',
                    'revenue': '%s',
                    'tax': '%s',
                    'shipping': '%s'
                    });",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $baseGrandTotal,
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount()
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf(
                    "ga('ecommerce:addItem', {
                    'id': '%s',
                    'sku': '%s',
                    'name': '%s',
                    'category': '%s',
                    'price': '%s',
                    'quantity': '%s'
                    });",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice(),
                    $item->getQtyOrdered()
                );
            }
            $result[] = "ga('ecommerce:send');";
        }
        return implode("\n", $result);
    }

    /**
     * Generates code for Analytics version of GA
     *
     * @return string|void
     */
    protected function _getOrdersTrackingCodeAnalytics()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }

            $baseGrandTotal = $order->getBaseGrandTotal();
            //Adding financed amount in case of financed order
            $financeAmount = $order->getFinanceAmount();
            if ($financeAmount) {
                $baseGrandTotal += $financeAmount;
            }

            $result[] = sprintf(
                "_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $baseGrandTotal,
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount(),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCity())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getRegion())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCountry()))
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf(
                    "_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice(), $item->getQtyOrdered()
                );
            }
            $result[] = "_gaq.push(['_trackTrans']);";
        }
        return implode("\n", $result);
    }


}
