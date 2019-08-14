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
 * Data helper for checkout functions
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Helper_Checkout extends Mage_Core_Helper_Data
{

    /**
     * Processes return status from the CheckoutController
     *
     * @param Mage_Sales_Model_Order $order      order object
     * @param array                  $parameters get/post parameters
     *
     * @throws Exception Invalid decision code
     *
     * @return string Redirect URL.
     */
    // @codingStandardsIgnoreStart -- Cyclomatic complexity, consider refactoring this
    public function processReturnStatus($order, $parameters)
    {
        $helper = Mage::helper('paybyfinance');
        switch ($parameters['decision']) {
            case 'ACCEPTED':
                $status = Mage::getStoreConfig($helper::XML_PATH_STATUS_ACCEPTED);
                if ($order->getStatus() == 'processing') {
                    $status = 'processing'; // HC-261 Race condition on previously REFERRED orders
                }
                $redirectUrl = 'paybyfinance/status/accepted';
                break;
            case 'DECLINED':
                $status = Mage::getStoreConfig($helper::XML_PATH_STATUS_DECLINED);
                $redirectUrl = 'paybyfinance/status/declined';
                break;
            case 'REFERRED':
                $status = Mage::getStoreConfig($helper::XML_PATH_STATUS_REFERRED);
                $redirectUrl = 'paybyfinance/status/referred';
                break;
            case 'VALIDATION_ERROR':
                $status = Mage::getStoreConfig($helper::XML_PATH_STATUS_ERROR);
                $redirectUrl = 'paybyfinance/status/error';
                break;
            case 'ABANDON':
                $status = Mage::getStoreConfig($helper::XML_PATH_STATUS_ABANDONED);
                $redirectUrl = 'paybyfinance/status/abandoned';
                break;
            default:
                throw new Exception("Invalid decision code", 1);
        }

        $message = $this->getParamText('id', 'id', $parameters);
        $message .= $this->getParamText('id2', 'id2', $parameters);
        $message .= $this->getParamText('decision', 'decision', $parameters);
        $message .= $this->getParamText('applicationNo', 'applicationNo', $parameters);
        $message .= $this->getParamText('authorisationcode', 'authorisationcode', $parameters);
        $message .= $this->getParamText('sourceurl', 'sourceurl', $parameters);
        $message .= $this->getParamText('Errreason', 'Errreason', $parameters);
        $message .= $this->getParamText('Errtext', 'Errtext', $parameters);

        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $financeStatus = $order->getFinanceStatus();
        if ($financeStatus == 'ACCEPT') {
            $financeStatus = 'ACCEPTED';
        }

        //Proceed with emails and saving order changes only in case the status change is allowed
        if (Mage::helper('paybyfinance/checkout')->setOrderStateSafe($order, $state, $status)) {

            if ($parameters['decision'] == 'ACCEPTED') {
                $order->sendNewOrderEmail();
                $helper->log(
                    'New order email has been sent for order: ' . $order->getIncrementId(),
                    'post'
                );
            }

            if ($parameters['decision'] != $financeStatus // Don't change status if not modified.
                && !$order->getPaybyfinanceEnable() // Don't change status on second return.
            ) {
                $order->setFinanceStatus($parameters['decision']);
                if ($parameters['decision'] != 'ACCEPTED') {
                    $orderHelper = Mage::helper('paybyfinance/order');
                    $orderHelper->sendFailedEmail($order, $parameters['decision']);
                    $helper->log(
                        'Failed order email has been sent for order: ' . $order->getIncrementId() . "\n"
                        . 'Finance status: ' . $parameters['decision'],
                        'post'
                    );
                }
            }
            $order->addStatusHistoryComment(nl2br(trim($message), false));
            $order->setPaybyfinanceEnable(true);

            $order->save();
        } else {
            $order->addStatusHistoryComment(nl2br(trim($message), false));

            $order->save();
        }


        return $redirectUrl;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get parmeter by it's id with text and semicolon.
     *
     * @param string $id         Id
     * @param string $text       text
     * @param array  $parameters Parameters array
     *
     * @return string String representation
     */
    protected function getParamText($id, $text, $parameters)
    {
        if (isset($parameters[$id]) && $parameters[$id]) {
            return "\n" . $text . ': ' . $parameters[$id];
        }

    }

    /**
     * Processes unexpected return from the CheckoutController
     *
     * @param Mage_Sales_Model_Order $order      order object
     * @param array                  $parameters get/post parameters
     *
     * @throws Exception Invalid decision code
     *
     * @return nil
     */
    public function setUnexpectedError($order, $parameters)
    {
        $helper = Mage::helper('paybyfinance');
        $status = Mage::getStoreConfig($helper::XML_PATH_STATUS_ABANDONED);
        $message = $this->getParamText('id', 'id', $parameters);
        $message .= $this->getParamText('id2', 'id2', $parameters);
        $message .= $this->getParamText('decision', 'decision', $parameters);
        $message .= $this->getParamText('applicationNo', 'applicationNo', $parameters);
        $message .= $this->getParamText('authorisationcode', 'authorisationcode', $parameters);
        $message .= $this->getParamText('sourceurl', 'sourceurl', $parameters);
        $message .= $this->getParamText('Errreason', 'Errreason', $parameters);
        $message .= $this->getParamText('Errtext', 'Errtext', $parameters);

        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->addStatusToHistory($status, nl2br(trim($message)), false);
        //Save order changes only in case the status change is allowed
        if (Mage::helper('paybyfinance/checkout')->setOrderStateSafe($order, $state, $status)) {
            $order->save();
        }
    }

    /**
     * Sets order state and status and checks all security restrictions before it.
     * Does not save order.
     *
     * @param Mage_Sales_Model_Order $order  order
     * @param string                 $state  state
     * @param string                 $status status
     *
     * @return bool
     */
    public function setOrderStateSafe($order, $state, $status)
    {
        $helper = Mage::helper('paybyfinance');

        if ($order->getStatus() == HC_PayByFinance_Helper_Data::STATUS_ADDRESS_INCONSISTENT) {
            $helper->log(
                'Something tried to change order status to ' . $status
                . ' while order is in address inconsistent status. Change rejected.',
                'post'
            );
            return false;
        }

        if ($order->getState() != $state || $order->getStatus() != $status) {
            $order->setState($state, $status);
        }

        return true;
    }

}
