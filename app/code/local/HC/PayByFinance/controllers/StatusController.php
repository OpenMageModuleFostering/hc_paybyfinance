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
* Controller for status pages
*
* @uses     Mage_Adminhtml_Controller_Action
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_StatusController extends Mage_Core_Controller_Front_Action
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
     * accepted
     *
     * @return void
     */
    public function acceptedAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * referred
     *
     * @return void
     */
    public function referredAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * declined
     *
     * @return void
     */
    public function declinedAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * abandoned
     *
     * @return void
     */
    public function abandonedAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * error
     *
     * @return void
     */
    public function errorAction()
    {
        $this->loadLayout()->renderLayout();
    }

}
