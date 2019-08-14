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
        $message .= "\nFinance: " . $parameters['decision'];
        $message .= "\nApplication: " . isset($parameters['applicationNo'])?$parameters['applicationNo']:"N/A";
        $message .= "\nAuthorization: " . isset($parameters['authorisationcode'])?$parameters['authorisationcode']:"N/A";
        $message .= "\nSURL: " . isset($parameters['sourceurl'])?$parameters['sourceurl']:"N/A";
        $message .= $this->getParamText('Errreason', 'Reason', $parameters);
        $message .= $this->getParamText('Errtext', 'Message', $parameters);

        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $financeStatus = $order->getFinanceStatus();
        if ($financeStatus == 'ACCEPT') {
            $financeStatus = 'ACCEPTED';
        }

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
            $order->setState($state, $status);
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
        $message = 'id: ' . $parameters['id'];
        $message .= ' id2: ' . $parameters['id2'];
        $message .= "\n" . $parameters['decision'];
        $message .= "\nApplication: " .
            isset($parameters['applicationNo'])?$parameters['applicationNo']:"N/A";
        $message .= "\nAuthorization: " .
            isset($parameters['authorisationcode'])?$parameters['authorisationcode']:"N/A";
        $message .= "\nSURL: " . isset($parameters['sourceurl'])?$parameters['sourceurl']:"N/A";
        $message .= "\nReason: " . $parameters['Errreason'];
        $message .= "\nMessage: " . $parameters['Errtext'];

        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState($state, $status);
        $order->addStatusToHistory($status, nl2br(trim($message)), false);
        $order->save();
    }

}
