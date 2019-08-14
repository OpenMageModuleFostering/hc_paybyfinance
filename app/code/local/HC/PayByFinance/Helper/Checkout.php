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
 * Data helper for checkout functions
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
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


        $message .= 'id: ' . $parameters['id'];
        $message .= ' id2: ' . $parameters['id2'];
        $message .= "\n" . $parameters['decision'];
        $message .= "\nApplication: " . $parameters['applicationNo'];
        $message .= "\nAuthorization: " . $parameters['authorisationcode'];
        $message .= "\nSURL: " . $parameters['sourceurl'];
        $message .= "\nReason: " . $parameters['Errreason'];
        $message .= "\nMessage: " . $parameters['Errtext'];

        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState($state, $status);
        $order->addStatusToHistory($status, nl2br(trim($message)), false);
        $order->save();

        return $redirectUrl;
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
        $message .= 'id: ' . $parameters['id'];
        $message .= ' id2: ' . $parameters['id2'];
        $message .= "\n" . $parameters['decision'];
        $message .= "\nApplication: " . $parameters['applicationNo'];
        $message .= "\nAuthorization: " . $parameters['authorisationcode'];
        $message .= "\nSURL: " . $parameters['sourceurl'];
        $message .= "\nReason: " . $parameters['Errreason'];
        $message .= "\nMessage: " . $parameters['Errtext'];

        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState($state, $status);
        $order->addStatusToHistory($status, nl2br(trim($message)), false);
        $order->save();
    }

}
