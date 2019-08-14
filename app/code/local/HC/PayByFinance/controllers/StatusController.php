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
 * Controller for status pages
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
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
        $this->loadLayout();
        Mage::dispatchEvent('paybyfinance_status_controller_accepted');
        $this->renderLayout();
    }

    /**
     * referred
     *
     * @return void
     */
    public function referredAction()
    {
        $this->loadLayout();
        Mage::dispatchEvent('paybyfinance_status_controller_referred');
        $this->renderLayout();
    }

    /**
     * declined
     *
     * @return void
     */
    public function declinedAction()
    {
        $this->loadLayout();
        Mage::dispatchEvent('paybyfinance_status_controller_declined');
        $this->renderLayout();
    }

    /**
     * Abandoned. This will be never loaded.
     *
     * @return void
     */
    public function abandonedAction()
    {
        $this->loadLayout();
        Mage::dispatchEvent('paybyfinance_status_controller_abandoned');
        $this->renderLayout();
    }

    /**
     * Error. This might be never used.
     *
     * @return void
     */
    public function errorAction()
    {
        $this->loadLayout();
        Mage::dispatchEvent('paybyfinance_status_controller_error');
        $this->renderLayout();
    }

}
