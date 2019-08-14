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
 * Controller for PBF pingback notifications
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_NotificationController extends Mage_Core_Controller_Front_Action
{
    /**
     * indexAction
     *
     * @return void.
     */
    public function indexAction()
    {
        $parameters = $this->getRequest()->getParams();
        $helper = Mage::helper('paybyfinance');

        if (!array_key_exists('supplierReference', $parameters)
            || !is_numeric($parameters['supplierReference'])
            || !array_key_exists('status', $parameters)
            || !array_key_exists('applicationNumber', $parameters)
        ) {
            $helper->log(
                'Error in notification parameters: ' . $helper->arrayDump($parameters),
                'notification'
            );
            echo "0";
            die();
        }

        $orderId = $parameters['supplierReference'];

        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            $helper->log('Order does not exist: ' . $orderId, 'notification');
            echo "0";
            die();
        }

        $helper->log(
            'Notification for order: ' . $orderId . "\n" . $helper->arrayDump($parameters),
            'notification'
        );

        $notificationHelper = Mage::helper('paybyfinance/notification');
        $result = $notificationHelper->processOrder($order, $parameters);

        if ($result) {
            $helper->log(
                'Notification received successfully for order: ' . $orderId,
                'notification'
            );
            echo "1";
            die(); // Success
        } else {
            echo "0";
            die(); // Error saving the order
        }
    }
}
