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
 * Helper for Order object manipulations
 *
 * @uses     Mage_Core_Helper_Order
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Helper_Order extends Mage_Core_Helper_Data
{

    /**
     * Send email based on Magento version
     *
     * @param Mage_Sales_Model_Order $order         Order object
     * @param String                 $financeStatus Finance status
     *
     * @return HC_PayByFinance_Helper_Order
     */
    public function sendFailedEmail($order, $financeStatus)
    {
        if (Mage::getEdition() == "Enterprise") {
            $this->queueFailedOrderEmail($order, $financeStatus, true);
        } else {
            $this->sendFailedOrderEmail($order, $financeStatus);
        }
    }

    /**
     * Queue email with new order data (Magento EE)
     *
     * @param Mage_Sales_Model_Order $order         Order object
     * @param String                 $financeStatus Finance status
     * @param bool                   $forceMode     Force to send email
     *
     * @return HC_PayByFinance_Helper_Order
     * @throws Exception
     */
    public function queueFailedOrderEmail($order, $financeStatus, $forceMode = false)
    {
        $helper = Mage::helper('paybyfinance'); // @codingStandardsIgnoreStart /** @var HC_PayByFinance_Helper_Data $helper */ // @codingStandardsIgnoreEnd
        $storeId = $order->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(
            Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_METHOD, $storeId
        );

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package
            // (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($financeStatus == 'REFERRED') {
            // Retrieve corresponding email template id and customer name
            if (!$order->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig($helper::XML_PATH_EMAIL_REFERRED, $storeId);
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig(
                    $helper::XML_PATH_EMAIL_REFERRED_GUEST, $storeId
                );
                $customerName = $order->getCustomerName();
            }
        } else {
            // Retrieve corresponding email template id and customer name
            if (!$order->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig($helper::XML_PATH_EMAIL_TEMPLATE, $storeId);
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig(
                    $helper::XML_PATH_EMAIL_TEMPLATE_GUEST, $storeId
                );
                $customerName = $order->getCustomerName();
            }
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(
            Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId)
        );
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'financeerror' => $this->getFinanceErrorText($financeStatus)
            )
        );

        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($order->getId())
            ->setEntityType(Mage_Sales_Model_Order::ENTITY)
            ->setEventType(Mage_Sales_Model_Order::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)->send();

        $order->setEmailSent(true);
        $order->getResource()->saveAttribute($order, 'email_sent');

        return $this;
    }

    /**
     * Send email with order data (Magento CE)
     *
     * @param Mage_Sales_Model_Order $order         Order object
     * @param String                 $financeStatus Finance status
     *
     * @return HC_PayByFinance_Helper_Order
     * @throws Exception
     */
    // @codingStandardsIgnoreStart
    public function sendFailedOrderEmail($order, $financeStatus)
    {
        $storeId = $order->getStore()->getId();
        $helper = mage::helper('paybyfinance'); // @codingStandardsIgnoreStart /** @var HC_PayByFinance_Helper_Data $helper */ // @codingStandardsIgnoreEnd

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $order;
        }

        $emailSentAttributeValue = $order->hasEmailSent()
            ? $order->getEmailSent()
            : Mage::getModel('sales/order')->load($order->getId())->getData('email_sent');
        $order->setEmailSent((bool) $emailSentAttributeValue);
        if ($order->getEmailSent()) {
            return $order;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(
            Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_METHOD, $storeId
        );

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package
            // (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($financeStatus == 'REFERRED') {
            // Retrieve corresponding email template id and customer name
            if (!$order->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig($helper::XML_PATH_EMAIL_REFERRED, $storeId);
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig(
                    $helper::XML_PATH_EMAIL_REFERRED_GUEST, $storeId
                );
                $customerName = $order->getCustomerName();
            }
        } else {
            // Retrieve corresponding email template id and customer name
            if (!$order->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig($helper::XML_PATH_EMAIL_TEMPLATE, $storeId);
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig(
                    $helper::XML_PATH_EMAIL_TEMPLATE_GUEST, $storeId
                );
                $customerName = $order->getCustomerName();
            }
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(
            Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId)
        );
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'financeerror' => $this->getFinanceErrorText($financeStatus)
            )
        );
        $mailer->send();

        $order->setEmailSent(true);
        Mage::getResourceSingleton('sales/order')->saveAttribute($order, 'email_sent');

        return $this;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Finance status text to be used in the email
     *
     * @param string $financeStatus Status
     *
     * @return string Status text
     */
    protected function getFinanceErrorText($financeStatus)
    {
        $helper = mage::helper('paybyfinance');
        switch ($financeStatus) {
            case 'ABANDON':
                $financeError = $helper->__('been Abandoned');
                break;
            case 'DECLINED':
                $financeError = $helper->__('been Declined');
                break;
            case 'REFERRED':
                $financeError = $helper->__('REFERRED');
                break;
            default:
                $financeError = $helper->__('experienced an error');
                break;
        }

        return $financeError;
    }

    /**
     * Get emails to send order confirmation email
     *
     * @param string $configPath Config path
     *
     * @return array Emails or false
     */
    protected function _getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
}
