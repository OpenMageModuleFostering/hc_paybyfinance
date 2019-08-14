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
* Hitachi Service Model
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Post extends Mage_Core_Model_Abstract
{
    private $_adapter;

    /**
     * Set post adapter
     *
     * @param string $state test, simulation or live
     *
     * @return HC_PayByFinance_Model_Post_Abstract Adapter object
     */
    public function setPostAdapter($state)
    {
        $this->_adapter = Mage::getModel('paybyfinance/post_'.$state);
        return $this->_adapter;
    }

    /**
     * Gets the adapter object
     *
     * @return HC_PayByFinance_Model_Post_Abstract Adapter object
     */
    public function getPostAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Posts to the PBF via CURL
     *
     * @return string CURL response
     */
    public function post()
    {
        return $this->_adapter->post();
    }

    /**
     * Get the redirect form
     *
     * @return string HTML form to redirect the user to PBF
     */
    public function getRedirectForm()
    {
        return $this->_adapter->getRedirectForm();
    }

    /**
     * Set and prepare data for quote calls
     *
     * @param array $data Array of key and fields
     *
     * @return void
     */
    public function setQuoteData($data)
    {
        $helper = Mage::helper('paybyfinance');
        $adapter = $this->getPostAdapter();

        $addressChecked = Mage::getStoreConfig($helper::XML_PATH_ADDRESS_CHECKED);
        $wizard = Mage::getStoreConfig($helper::XML_PATH_WIZARD);

        $fields = array(
            'id' => trim((string) Mage::getStoreConfig($helper::XML_PATH_PBF_ACCOUNT_ID1)),
            'id2' => trim((string) Mage::getStoreConfig($helper::XML_PATH_PBF_ACCOUNT_ID2)),
            'ver' => $adapter::PROTOCOL_VERSION,
            'eea' => Mage::getStoreConfig($helper::XML_PATH_ERROR_NOTIFY_EMAIL),
            'eurl' => Mage::getUrl(
                'paybyfinance/checkout/response', array("_secure" => true)
            ),
            'acceptedURL' => Mage::getUrl(
                'paybyfinance/checkout/response', array("_secure" => true)
            ),
            'referredURL' => Mage::getUrl(
                'paybyfinance/checkout/response', array("_secure" => true)
            ),
            'declinedURL' => Mage::getUrl(
                'paybyfinance/checkout/response', array("_secure" => true)
            ),
            'toStoreURL' => Mage::getUrl(
                'paybyfinance/checkout/response', array("_secure" => true)
            ),
            'notificationURL' => Mage::getUrl(
                'paybyfinance/notification', array("_secure" => true)
            ),
            'address_checked' => $addressChecked == "1" ? 'Y' : 'N',
            'wizard' => $wizard == "1" ? 'Y' : 'N',
        );
        $data = array_merge($data, $fields);

        $this->_adapter->setPostData($data);
    }

    /**
     * Set and prepare data for notification post calls
     *
     * @param array $data Array of keys and fields
     *
     * @return void
     */
    public function setNotificationData($data)
    {
        $helper = Mage::helper('paybyfinance');
        $adapter = $this->_adapter;

        $fields = array(
            'id' => trim((string) Mage::getStoreConfig($helper::XML_PATH_PBF_ACCOUNT_ID1)),
            'id2' => trim((string) Mage::getStoreConfig($helper::XML_PATH_PBF_ACCOUNT_ID2)),
        );
        $data = array_merge($data, $fields);

        $this->_adapter->setPostData($data);
    }
}
