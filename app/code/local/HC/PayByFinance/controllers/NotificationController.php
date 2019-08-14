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
 * Controller for PBF pingback notifications
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_NotificationController extends Mage_Core_Controller_Front_Action
{

    /**
     * Index action
     *
     * @return void.
     */
    public function indexAction()
    {
        $parameters = $this->getRequest()->getParams();
        $helper = Mage::helper('paybyfinance');

        if (!array_key_exists('supplierReference', $parameters)
            || !array_key_exists('status', $parameters)
            || !array_key_exists('applicationNumber', $parameters)
        ) {
            $helper->log(
                'Error in notification parameters: ' . $helper->arrayDump($parameters),
                'notification'
            );
            $this->returnStatus(false);
            die();
        }

        $orderId = $parameters['supplierReference'];

        $order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
        if (!$order->getId()) {
            $helper->log('Order does not exist: ' . $orderId, 'notification');
            $this->returnStatus(false);
        }

        $helper->log(
            'Notification for order: ' . $orderId . "\n" . $helper->arrayDump($parameters),
            'notification'
        );

        $notificationHelper = Mage::helper('paybyfinance/notification');
        try {
            $result = $notificationHelper->processOrder($order, $parameters);
        } catch (Exception $e) {
            $helper->log(
                $e->getMessage(),
                'notification'
            );
        }

        if (isset($result) && $result) {
            $helper->log(
                'Notification received successfully for order: ' . $orderId,
                'notification'
            );
            $this->returnStatus(true); // Success.
        } else {
            $this->returnStatus(false); // Error saving the order.
        }
    }

    /**
     * Set return status
     *
     * @param bool $success Is success
     *
     * @return void
     */
    protected function returnStatus($success)
    {
        if ($success) {
            echo '1';
            die();
        } else {
            header("HTTP/1.0 503 Service unavailable");
            echo '0';
            die();
        }
    }
}
