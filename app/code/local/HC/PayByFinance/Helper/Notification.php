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
 * Data helper for notification process
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Helper_Notification extends Mage_Core_Helper_Data
{

    /**
     * Process notification requests
     *
     * @param Mage_Sales_Model_Order $order      Order object
     *
     * @param array                  $parameters Array of parameters
     *
     * @return boolean true if success, false on error
     */
    public function processOrder($order, $parameters)
    {
        $orderStatus = $order->getStatus();
        $orderState = $order->getState();

        switch ($parameters['status']) {
            case 'S':
                $message = 'Status: Awaiting dispatch of goods. authorisationcode: '
                    . $parameters['authorisationcode'];
                $orderStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
                $order->setTotalPaid(
                    $order->getTotalPaid() + abs($order->getFinanceAmount())
                );
                $order->setBaseTotalPaid(
                    $order->getBaseTotalPaid() + abs($order->getFinanceAmount())
                );
                break;
            case 'A':
                $message = 'Status: Accepted';
                break;
            case 'R':
                $message = 'Status: Referred for manual underwriting';
                break;
            case 'D':
                $message = 'Status: Declined';
                break;
            case 'G':
                $message = 'Status: Goods dispatched';
                break;
            case 'C':
                $message = 'Status: Cancelled';
                $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            case 'F':
                $message = 'Status: Fraud';
                $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            default:
                $message = 'Status: Unknown: ' . $parameters['status'];
                break;
        }

        $message = "<strong>Hitachi Capital Pay By Finance"
            . "</strong> notification received: " . $message;
        $order->setFinanceStatus($status)
            ->setFinanceApplicationNo($applicationNo)
            ->setState($orderState, $orderStatus)
            ->addStatusToHistory($orderStatus, $message, false);
        return $order->save();
    }

}
