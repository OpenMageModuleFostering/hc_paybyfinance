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
 * Hitachi Post Abstract Model
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
abstract class HC_PayByFinance_Model_Post_Abstract extends Mage_Core_Model_Abstract
{
    const PROTOCOL_VERSION               = '1.0';

    private $_pbfInformation;

    private $_ciphers = array(
        'ECDHE-RSA-AES256-GCM-SHA384',
        'ECDHE-RSA-AES256-SHA384',
        'ECDHE-RSA-AES256-SHA',
        'AES256-GCM-SHA384',
        'AES256-SHA256',
        'AES256-SHA',
        'DES-CBC3-SHA',
        'ECDHE-RSA-AES128-GCM-SHA256',
        'ECDHE-RSA-AES128-SHA256',
        'ECDHE-RSA-AES128-SHA',
        'AES128-GCM-SHA256',
        'AES128-SHA256',
        'AES128-SHA',
    );

    /**
     * Hitachi Post Abstract Model SetPostData
     *
     * @param array $arrayData Post Data Array
     *
     * @return void
     */
    public function setPostData($arrayData)
    {
        foreach ($arrayData as &$value) {
            $value = trim($value);
        }
        $this->_pbfInformation = $arrayData;
    }

    /**
     * Hitachi Post Simulation Model
     *
     * @return post reponse object
     */
    public function post()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this::NOTIFY_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->_pbfInformation));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, implode(':', $this->_ciphers));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response !== false) {
            return $response;
        }

        return false;
    }

    /**
     * Generate the User Redirect Form
     *
     * @return $string of the form html
     */
    public function getRedirectForm()
    {
        $block = Mage::app()->getLayout()->createBlock('paybyfinance/checkout_redirect')
            ->setPostContent($this->_pbfInformation)
            ->setPostUrl($this::POST_URL)
            ->setMode($this::MODE)
            ->setTemplate('paybyfinance/form.phtml');

        return $block->toHtml();
    }

    /**
     * Get generated post data.
     *
     * @return array POST fields to be sent to the PBF servers
     */
    public function getPostData()
    {
        return $this->_pbfInformation;
    }
}
