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

    protected $statuses = array(
        'PENDING' => 'An Application decision has not yet been reached.',
        'ACCEPT' => 'The application has been accepted.',
        'DECLINE' => 'The application has been declined.',
        'REFER' => 'The application has been referred to the underwriting
            department.',
        'CONDITIONAL_ACCEPT' => 'The application has been accepted but further
            information is needed before it can be completed.',
        'FATAL_ERROR' => 'A fatal error has occured.',
        'SUPPLIER_CANCELLED' => 'The supplier/retailer has cancelled the application.',
        'HCCF_CANCELLED' => 'Hitachi Capital has cancelled the application.',
        'CONSUMER_CANCELLED' => 'The customer has cancelled the application.',
        'AMEND_CANCELLED' => 'This application has been cancelled since a new
            one has been created sa an amendment of this application.',
        'PAID' => 'The application has been paid.',
        'COOLING_OFF' => 'The application is in a cooling off period.',
        'ON_HOLD' => 'The application is on hold.',
        'SEND_BACK' => 'Documents have been sent back; awaiting their return',
    );

    // These statuses will cancel the order.
    protected $cancelStatus = array(
        'SUPPLIER_CANCELLED',
        'HCCF_CANCELLED',
        'CONSUMER_CANCELLED',
        'AMEND_CANCELLED',
    );

    /**
     * Get notification statuses
     *
     * @return array Statuses
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

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
        $helper = Mage::helper('paybyfinance');
        $orderStatus = $order->getStatus();
        $orderState = $order->getState();
        $applicationNo = $parameters['applicationNumber'];
        $status = strtoupper($parameters['status']);

        if (!array_key_exists($status, $this->statuses)) {
            $message = 'Status Unknown: "' . $parameters['status'].'"';
        } else {
            $message = $this->statuses[$status];
        }

        if (in_array($status, $this->cancelStatus)) {
            $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_CANCELED;
        }

        switch ($status) {
            case 'ACCEPT':
            case 'CONDITIONAL_ACCEPT':
                if (array_key_exists('goodsDispatched', $parameters)
                    && $parameters['goodsDispatched'] == 'N'
                ) {
                    $message .= ' Awaiting dispatch of goods. authorisationNumber: '
                        . $parameters['authorisationNumber'];
                    $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
                    $order->setTotalPaid(
                        $order->getTotalPaid() + abs($order->getFinanceAmount())
                    );
                    $order->setBaseTotalPaid(
                        $order->getBaseTotalPaid() + abs($order->getFinanceAmount())
                    );
                } else {
                    $orderStatus = Mage::getStoreConfig($helper::XML_PATH_STATUS_ACCEPTED);
                }
                break;
            case 'DECLINE':
                $orderStatus = Mage::getStoreConfig($helper::XML_PATH_STATUS_DECLINED);
                break;
            case 'REFER':
                $orderStatus = Mage::getStoreConfig($helper::XML_PATH_STATUS_REFERRED);
                break;
            case 'FATAL_ERROR':
                $orderStatus = Mage::getStoreConfig($helper::XML_PATH_STATUS_ERROR);
                break;
        }

        $message = "<strong>Hitachi Capital Pay By Finance"
            . "</strong> notification received: " . $status . ': ' . $message;
        $order->setFinanceStatus($status)
            ->setFinanceApplicationNo($applicationNo)
            ->setState($orderState, $orderStatus)
            ->addStatusToHistory($orderStatus, $message, false);
        return $order->save();
    }

}
