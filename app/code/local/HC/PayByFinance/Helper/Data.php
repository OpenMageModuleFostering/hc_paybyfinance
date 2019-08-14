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
 * Data helper for storing and getting configuration
 *
 * @uses     Mage_Core_Helper_Data
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Helper_Data extends Mage_Core_Helper_Data
{
    const XML_PATH_PRODUCTTYPES          = 'hc_paybyfinance/general/enable_producttypes';
    const XML_PATH_MINIMUM_PRICE_PRODUCT = 'hc_paybyfinance/general/minimum_price_product';
    const XML_PATH_MINIMUM_PRICE_BASKET  = 'hc_paybyfinance/general/minimum_price_basket';
    const XML_PATH_FIXED_DEPOSIT         = 'hc_paybyfinance/general/fixed_deposit';
    const XML_PATH_INCLUDE_SHIPPING      = 'hc_paybyfinance/general/include_shipping';
    const XML_PATH_ADDRESS_CHECKED       = 'hc_paybyfinance/general/address_checked';
    const XML_PATH_WIZARD                = 'hc_paybyfinance/general/wizard';
    const XML_PATH_STATUS_ACCEPTED       = 'hc_paybyfinance/order_status/accepted';
    const XML_PATH_STATUS_REFERRED       = 'hc_paybyfinance/order_status/referred';
    const XML_PATH_STATUS_DECLINED       = 'hc_paybyfinance/order_status/declined';
    const XML_PATH_STATUS_ABANDONED      = 'hc_paybyfinance/order_status/abandoned';
    const XML_PATH_STATUS_ERROR          = 'hc_paybyfinance/order_status/error';
    const XML_PATH_BLOCK_INFO            = 'hc_paybyfinance/blocks_info/information';
    const XML_PATH_BLOCK_ACCEPTED        = 'hc_paybyfinance/blocks_info/accepted';
    const XML_PATH_BLOCK_REFERRED        = 'hc_paybyfinance/blocks_info/referred';
    const XML_PATH_BLOCK_DECLINED        = 'hc_paybyfinance/blocks_info/declined';
    const XML_PATH_BLOCK_ABANDONED       = 'hc_paybyfinance/blocks_info/abandoned';
    const XML_PATH_BLOCK_ERROR           = 'hc_paybyfinance/blocks_info/error';
    const XML_PATH_PBF_ACCOUNT_ID1       = 'hc_paybyfinance/account/id1';
    const XML_PATH_PBF_ACCOUNT_ID2       = 'hc_paybyfinance/account/id2';
    const XML_PATH_CONNECTION_MODE       = 'hc_paybyfinance/account/connectionmode';
    const XML_PATH_ERROR_NOTIFY_EMAIL    = 'hc_paybyfinance/account/erroremail';
    const XML_PATH_ACCOUNT_RETAILERNAME  = 'hc_paybyfinance/account/retailername';
    const XML_PATH_ACCOUNT_TRADINGNAME   = 'hc_paybyfinance/account/tradingname';
    const ERROR_LOG_PATH_LOG             = 'paybyfinance/paybyfinance-log.log';
    const ERROR_LOG_PATH_POST            = 'paybyfinance/paybyfinance-post.log';
    const ERROR_LOG_PATH_NOTIFICATION    = 'paybyfinance/paybyfinance-notification.log';

    private $_types;

    /**
     * Get JSON for the selector JS data
     *
     * @param _Collection $services Services
     * @param float       $amount   Financeable Amount.
     *
     * @return string JSON.
     */
    public function getSelectorJSON($services, $amount)
    {
        $session = Mage::getSingleton('paybyfinance/session');
        $json = array(
            'services' => array(),
            'amount'   => $amount,
            'enabled'  => $session->getData('enabled'),
            'service'  => $session->getData('service'),
            'deposit'  => $session->getData('deposit'),
        );
        foreach ($services as $service) {
            $types[$service->getType()] = $this->getTypeName((int) $service->getType());
            $json['terms'][] = array(
                'term' => $service->getTerm(),
                'service_id' => $service->getId(),
                'type' => $service->getType(),
            );
            $json['services'][$service->getId()] = array(
                'name' => $service->getName(),
                'type' => $service->getType(),
                'apr' => $service->getApr(),
                'term' => $service->getTerm(),
                'defer_term' => $service->getDeferTerm(),
                'option_term' => $service->getOptionTerm(),
                'deposit' => $service->getDeposit(),
                'fee' => $service->getFee(),
                'min_amount' => $service->getMinAmount(),
                'multiplier' => $service->getMultiplier(),
                'rpm' => $service->getRpm(),
            );
        }

        usort(
            $json['terms'],
            function ($a, $b) {
                if ($a['term'] == $b['term']) {
                    return 0;
                }
                return ($a['term'] < $b['term']) ? -1 : 1;
            }
        );

        foreach ($types as $key => $value) {
            $json['types'][] = array(
                'type' => $key,
                'name' => $value,
            );
            $terms = null;
            foreach ($json['terms'] as $termkey => $termvalue) {
                if ($termvalue['type'] == $key) {
                    $terms[] = $termvalue;
                }
            }
            $json['subterms'][] = $terms;
        }

        return json_encode($json);
    }

    /**
     * Get type name by type id
     *
     * @param integer $type type id, for example 31
     *
     * @return string Type name
     */
    public function getTypeName($type)
    {
        if (!isset($this->_types)) {
            $this->_types = Mage::getModel('paybyfinance/config_source_type')->toFrontendArray();
        }

        return $this->_types[(integer) $type];
    }

    /**
     * Is product eligible for finance?
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Catalog_Model_Product $item Quote item.
     *
     * @return boolean Eligible or not.
     */
    public function isProductEligible($item)
    {
        $options = Mage::getSingleton('paybyfinance/config_source_catalog_product_finance');

        if (   $item instanceof Mage_Sales_Model_Quote_Item
            || $item instanceof Mage_Sales_Model_Order_Item
        ) {
            $product = $item->getProduct();
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        }

        if ($product->getPaybyfinanceEnable() == $options::VALUES_DISABLE) {
            return false;
        }

        switch ($product->getPaybyfinanceEnable()) {
            case $options::VALUES_DISABLE:
                return false;
            case $options::VALUES_ENABLE:
                return true;
            case $options::VALUES_CONFIG:
            default:
                // Calculate based on the configuration in the backend
                break;
        }

        $helper = Mage::helper('paybyfinance');
        $types = explode(',', Mage::getStoreConfig($helper::XML_PATH_PRODUCTTYPES));
        if (!in_array($product->getTypeID(), $types)) {
            return false;
        }
        $minPriceProduct = Mage::getStoreConfig($helper::XML_PATH_MINIMUM_PRICE_PRODUCT);
        if ($product->getFinalPrice() < $minPriceProduct) {
            return false;
        }

        return true;
    }

    /**
     * Logging data to the specified log file
     *
     * @param string|array $data As returned by print_r or any text to log
     * @param string       $type log type
     *
     * @throws Exception
     *
     * @return integer
     */
    public function log($data, $type = 'log')
    {
        if (!is_array($data) && !is_object($data)) {
            $data = $this->_processUserInput($data);
        } else {
            throw new Exception("Usage of array or object is discouraged for security reasons.", 1);
        }

        if (!file_exists(Mage::getBaseDir('var').'/log')) {
            mkdir(Mage::getBaseDir('var').'/log');
        }
        if (!file_exists(Mage::getBaseDir('var').'/log/paybyfinance')) {
            mkdir(Mage::getBaseDir('var').'/log/paybyfinance');
        }
        if ($type == 'log') {
            Mage::log($data, null, self::ERROR_LOG_PATH_LOG);
        }

        if ($type == 'post') {
            Mage::log($data, null, self::ERROR_LOG_PATH_POST);
        }

        if ($type == 'notification') {
            Mage::log($data, null, self::ERROR_LOG_PATH_NOTIFICATION);
        }

        return $this->logDB($data, $type);
    }

    /**
     * Logging data to the database log file
     *
     * @param string $data data as returned by print_r or any text to log
     * @param string $type log type
     *
     * @return integer Id of the saved log.
     */
    public function logDB($data, $type ='log')
    {

        if (!is_null($data)) {
            $currentTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $log = Mage::getModel('paybyfinance/log');
        } else {
            return;
        }

        if ($type == 'log') {
            $log->setType('General')
                ->setFlow('Outgoing')
                ->setTime($currentTime)
                ->setContent($data)
                ->save();
        }

        if ($type == 'post') {
            $log->setType('Post')
                ->setFlow('Outgoing')
                ->setTime($currentTime)
                ->setContent($data)
                ->save();
        }

        if ($type == 'notification') {
            $log->setType('Notification')
                ->setFlow('Incoming')
                ->setTime($currentTime)
                ->setContent($data)
                ->save();
        }

        if ($type == 'notification-productupdate') {
            $log->setType('Notification')
                ->setFlow('Incomming')
                ->setTime($currentTime)
                ->setContent($data)
                ->save();
        }

        return $log->getId();
    }

    /**
     * A basic array dump
     *
     * @param array $arr Array to dump
     *
     * @return string Formatted text for logs
     */
    public function arrayDump($arr)
    {
        $text = '';
        foreach ($arr as $key => $val) {
            $text .= $key . ': ' . $val . PHP_EOL;
        }
        return $text;
    }

    /**
     * Processing user input for extra security, removing php open tags
     *
     * @param string $data User input as string
     *
     * @return string Processed string
     */
    protected function _processUserInput($data)
    {
        return htmlspecialchars($data);
    }
}
