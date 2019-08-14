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
 * Controller for checkout actions
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_CheckoutController extends Mage_Core_Controller_Front_Action
{

    /**
     * Redirect form
     *
     * @return void
     */
    public function redirectformAction()
    {
        $session = Mage::getSingleton('paybyfinance/session');
        $helper = Mage::helper('paybyfinance');
        $cartHelper = Mage::helper('paybyfinance/cart');

        $orderId = $session->getData('order_id');
        if (!$orderId) {
            echo "Invalid session";
            return;
        }
        $order = Mage::getModel('sales/order')->load($orderId);
        $eligibleProducts = $cartHelper->getEligibleProducts($order->getAllItems());
        $eligibleAmount = $cartHelper->getEligibleAmount($order->getAllItems());
        $serviceId = $order->getFinanceService();
        $service = Mage::getModel('paybyfinance/service')->load($serviceId);
        $address = $order->getBillingAddress();
        $street = $address->getStreet();
        $includeShipping = Mage::getStoreConfig($helper::XML_PATH_INCLUDE_SHIPPING);
        $shippingCost = 0;
        if ($includeShipping) {
            $shippingCost = $order->getShippingInclTax();
        }
        $mode = Mage::getStoreConfig($helper::XML_PATH_CONNECTION_MODE);

        $calculator = Mage::getModel('paybyfinance/calculator');
        $calculator->setService($serviceId)
            ->setDeposit($order->getFinanceDeposit())
            ->setAmount($eligibleAmount + $shippingCost)
            ->setDiscount($order->getDiscountAmount())
            ->setGiftcard($order->getGiftCardsAmount());
        $financeResult = $calculator->getResults();

        $post = Mage::getModel('paybyfinance/post');
        $post->setPostAdapter(Mage::getStoreConfig($helper::XML_PATH_CONNECTION_MODE));

        $productsInForm = array();
        $productCount = 0;
        $additionalItems = 0;
        foreach ($eligibleProducts as $key => $product) {
            $productName = $helper->sanitizeProductName($product->getName());
            $productsInForm['gc' . $productCount] = $product->getSku();
            $productsInForm['pc' . $productCount] = $product->getSku();
            $productsInForm['gd' . $productCount] = $productName;
            $productsInForm['q' . $productCount] = intval($product->getQtyOrdered());
            $productsInForm['gp' . $productCount] =
                floor($product->getPriceInclTax() * 10000) / 10000;
            $productCount++;
        }

        if ($includeShipping && $order->getShippingInclTax() > 0) {
            $shippingCost = $order->getShippingInclTax();
            $additionalItems++;
            $productsInForm['gc' . ($productCount) ] = 'sc';
            $productsInForm['pc' . ($productCount)] = 'sc';
            $productsInForm['gd' . ($productCount)] = 'sc';
            $productsInForm['q' . ($productCount)] = 1;
            $productsInForm['gp' . ($productCount)] = number_format(
                $shippingCost, 2, '.', ''
            );
            $productCount++;
        }

        $discount = (float) $order->getDiscountAmount();

        if ($discount) {
            $additionalItems++;
            $productsInForm['gc' . ($productCount) ] = 'discount';
            $productsInForm['pc' . ($productCount)] = 'discount';
            $productsInForm['gd' . ($productCount)] = 'discount';
            $productsInForm['q' . ($productCount)] = 1;
            $productsInForm['gp' . ($productCount)] = number_format(
                $discount, 2, '.', ''
            );
            $productCount++;
        }

        $giftcard = 0.0 - (float) $order->getGiftCardsAmount();

        if ($giftcard) {
            $additionalItems++;
            $productsInForm['gc' . ($productCount) ] = 'giftcard';
            $productsInForm['pc' . ($productCount)] = 'giftcard';
            $productsInForm['gd' . ($productCount)] = 'giftcard';
            $productsInForm['q' . ($productCount)] = 1;
            $productsInForm['gp' . ($productCount)] = number_format(
                $giftcard, 2, '.', ''
            );
            $productCount++;
        }
        $deferredServicesProperties = array();
        if (HC_PayByFinance_Model_Config_Source_Type::isDeferredType(
            $service->getType()
        )
        ) {
            $deferredServicesProperties['cdperiod'] = $service->getDeferTerm();
        }


        $post->setQuoteData(
            array_merge(
                array(
                    'cgid' => $service->getType(), // Service type
                    'svtypename' => $service->getName(),
                    'totalCashPrice' => $financeResult->getAmount()
                        - abs($discount)
                        - abs($giftcard),
                    'totalGoodPrice' => $financeResult->getAmount()
                        - abs($discount)
                        - abs($giftcard),
                    'damt' => $financeResult->getDeposit(),
                    'loanAmt' => $financeResult->getFinanceAmount(),
                    'term' => $service->getTerm(),
                    'ratePerMonth' => floor($service->getRpm() * 1000) / 1000,
                    'apr' => floor($service->getApr() * 1000) / 1000,
                    'instalment' => $financeResult->getMonthlyPayment(), // monthly instalment
                    'it' => count($eligibleProducts) + $additionalItems, // number of items
                    'ro' => $order->getIncrementId(), // Order reference
                    'title' => $helper->sanitizeTitle($address->getPrefix()),
                    'firstname' => $helper->sanitizeName($address->getFirstname()),
                    'surname' => $helper->sanitizeName($address->getLastname()),
                    'street' => $helper->sanitizeStreet(
                        trim(
                            $street[0] . ' ' . (isset($street[1]) ? $street[1]
                                : '')
                        )
                    ),
                    'town' => $helper->sanitizeName($address->getCity()),
                    'postcode' => $address->getPostcode(),
                    'email' => $order->getCustomerEmail(),
                ),
                $productsInForm,
                $deferredServicesProperties
            )
        );

        // Generates the forwarder form
        echo $post->getRedirectForm();

        // Log the generated POST data
        $logId = $helper->log(
            "getRedirectForm: \n" . $helper->arrayDump($post->getPostAdapter()->getPostData()),
            'post'
        );

        $formUrl = Mage::helper("adminhtml")->getUrl(
            'adminhtml/paybyfinance_redirect/view',
            array('id' => $logId)
        );
        $formUrl = '<a href="' . $formUrl . '">click here.</a>';

        $order->addStatusHistoryComment('Customer redirected to PBF with this POST: ' . $formUrl)
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false);
        $order->save();
    }

    /**
     * After the user has finished with the Finance forms, He's coming back here.
     *
     * @return void
     */
    public function responseAction()
    {
        $parameters = $this->getRequest()->getParams();
        $coHelper = Mage::helper('paybyfinance/checkout');
        $helper = Mage::helper('paybyfinance');
        $helper->log("Response: \n" . $helper->arrayDump($parameters), 'post');
        $order = null;

        if (array_key_exists('ro', $parameters)) {
            $order = Mage::getModel('sales/order')->load($parameters['ro'], 'increment_id');
        }

        // Unexpected error, PBF didn't send a correct order id (authentication error?)
        if (!$order) {
            $session = Mage::getSingleton('paybyfinance/session');
            $order = Mage::getModel('sales/order')->load($session->getData('order_id'));
            $coHelper->setUnexpectedError($order, $parameters);
            $redirectUrl = 'paybyfinance/status/error';
        } else {
            $redirectUrl = $coHelper->processReturnStatus($order, $parameters);
        }

        $this->_redirect($redirectUrl, array('order_id'=>$order->getId()));
    }

}
