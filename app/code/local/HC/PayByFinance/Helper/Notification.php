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
 * Data helper for notification process
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
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
     *
     * @throws Exception when notification received too early for order
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

        if ($status == 'PAID' && $orderState != 'complete') {
            /* There's a race condition when Hitachi sends PAID notification the same
               time we send shipment (inbound) notification. They will re-try later. */
            throw new Exception(
                'PAID Notification received too early for order: ' . $order->getId(), 1
            );
        }

        if (in_array($status, $this->cancelStatus)) {
            $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_CANCELED;
        }

        if ($status == 'ACCEPT' || $status == 'CONDITIONAL_ACCEPT') {
            if (array_key_exists('goodsDispatched', $parameters)
                && $parameters['goodsDispatched'] == 'N'
            ) {
                $message .= ' Awaiting dispatch of goods (WET). authorisationNumber: '
                    . $parameters['authorisationNumber'];
                $this->addTotals($order);
            } elseif (array_key_exists('esignatureStatus', $parameters)
                && array_key_exists('goodsDispatched', $parameters)
                && $parameters['esignatureStatus'] == 'COMPLETED - E-signature was completed'
                && $parameters['goodsDispatched'] == ''
            ) {
                $message .= ' Awaiting dispatch of goods (eSignature). authorisationNumber: '
                    . $parameters['authorisationNumber'];
                $this->addTotals($order);
            }
        }

        if (isset($parameters['esignatureStatus']) && $parameters['esignatureStatus']) {
            $message .= ' esignatureStatus: ' . $parameters['esignatureStatus'] . '. ';
        }
        if (isset($parameters['goodsDispatched']) && $parameters['goodsDispatched']) {
            $message .= ' goodsDispatched: ' . $parameters['goodsDispatched'] . '. ';
        }

        $message = "<strong>Hitachi Capital Pay By Finance"
            . "</strong> notification received: " . $status . ': ' . $message;

        list($orderState, $orderStatus) = $this->getOrderStateAndStatus($parameters);

        $this->financeStatusChange($status, $order, $orderStatus, $orderState);
        $order
            ->setFinanceApplicationNo($applicationNo)
            ->addStatusHistoryComment($message, false);
        return $order->save();
    }

    /**
     * Add totals to the order
     *
     * @param Mage_Sales_Model_Order $order Order object
     *
     * @return void
     *
     * @throws Exception when notification received too early for order
     */
    protected function addTotals($order)
    {
        if (!$order->getPaybyfinanceEnable()) {
            throw new Exception('Notification received too early for order: ' . $order->getId(), 1);
        }
        $finance = new Varien_Object();
        $finance->setUpdateTotals(true);
        Mage::dispatchEvent(
            'paybyfinance_totals_notification_update',
            array('order' => $order, 'finance' => $finance)
        );
        if ($order->getFinanceTotalAdded() != 1) {
            if ($finance->getUpdateTotals()) {
                $order->setGrandTotal(
                    $order->getGrandTotal() + abs($order->getFinanceAmount())
                );
                $order->setBaseGrandTotal(
                    $order->getBaseGrandTotal() + abs($order->getFinanceAmount())
                );
            }
            $order->setFinanceTotalAdded(1);
        }
    }

    /**
     * Change finance order status, but only when status changed in the request
     *
     * @param string                 $status      Finance status
     * @param Mage_Sales_Model_Order $order       Order
     * @param String                 $orderStatus Order status
     * @param String                 $orderState  Order state
     *
     * @return bool True if the status was changed
     */
    protected function financeStatusChange($status, $order, $orderStatus, $orderState)
    {
        $disallowChange = array(
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_PROCESSING
        );
        $origStatus = $order->getFinanceStatus();
        if ($origStatus == 'ACCEPTED') {
            $origStatus = 'ACCEPT';
        }

        if ($status == 'ACCEPT' && !in_array($order->getStatus(), $disallowChange)) {
            $order->setState($orderState, $orderStatus);
        }
        if ($origStatus != $status) {
            $order->setFinanceStatus($status);
            $order->setState($orderState, $orderStatus);
            return true;
        }

        return false;
    }

    /**
     * Calculate order state and status based on notification parameters
     *
     * @param array $parameters request parameters
     *
     * @return array Array of state and status
     *
     * @throws Exception
     */
    protected function getOrderStateAndStatus($parameters)
    {
        $helper = Mage::helper('paybyfinance');
        $status = strtoupper($parameters['status']);

        $orderState = $orderStatus = '';

        if (in_array($status, $this->cancelStatus)) {
            $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_CANCELED;
        }

        switch ($status) {
            case 'ACCEPT':
            case 'CONDITIONAL_ACCEPT':
                if (array_key_exists('goodsDispatched', $parameters)
                    && $parameters['goodsDispatched'] == 'N'
                ) {
                    $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
                } elseif (array_key_exists('esignatureStatus', $parameters)
                    && array_key_exists('goodsDispatched', $parameters)
                    && $parameters['esignatureStatus'] == 'COMPLETED - E-signature was completed'
                    && $parameters['goodsDispatched'] == ''
                ) {
                    $orderState = $orderStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
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
            default:
                $message = "Unknown status value:>" . $status . "<";
                Mage::helper('paybyfinance')->log($message, 'notification');
                throw new Exception($message);
        }

        return array($orderState, $orderStatus);
    }
}
