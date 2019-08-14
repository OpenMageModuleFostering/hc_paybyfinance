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
* @license   http://www.healthywebsites.co.uk/license.html HWS License
* @link      http://www.healthywebsites.co.uk/
*
*/

/**
* Controller for session storage operations
*
* @uses     Mage_Adminhtml_Controller_Action
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.healthywebsites.co.uk/license.html HWS License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_SessionController extends Mage_Core_Controller_Front_Action
{
    /**
     * indexAction
     *
     * @return void.
     */
    public function indexAction()
    {
        echo 'Hello Index!';
    }

    /**
     * saveAction
     *
     * @throws Exception
     *
     * @return mixed Value.
     */
    public function saveAction()
    {
        $session = Mage::getSingleton('paybyfinance/session');
        $service = (int) $this->getRequest()->getParam('service');
        $deposit = (int) $this->getRequest()->getParam('deposit');
        if ($this->getRequest()->getParam('enabled') == 1) {
            $enabled = true;
        } else {
            $enabled = false;
        }

        if ($deposit > 60) {
            throw new Exception("Deposit can't be bigger than 60%", 1);
        }

        if (!Mage::getModel('paybyfinance/service')->load($service)) {
            throw new Exception("Invalid service id", 1);
        }

        $session->setData('enabled', $enabled);
        $session->setData('service', $service);
        $session->setData('deposit', $deposit);
    }

}
