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
 * Controller for previewing redirect forms
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Adminhtml_Paybyfinance_RedirectController
    extends Mage_Adminhtml_Controller_Action
{
    public $_publicActions = array('view');

    /**
     * _initAction
     *
     * @return mixed Value.
     */
    protected function _initAction()
    {
        $helper = Mage::helper('paybyfinance');
        $this->loadLayout()
            ->_setActiveMenu('paybyfinance')
            ->_addBreadcrumb(
                $helper->__('Pay By Finance'),
                $helper->__('Redirect')
            );
        return $this;
    }

    /**
     * indexAction
     *
     * @return void.
     */
    public function viewAction()
    {
        $this->_title($this->__('Pay By Finance'))
            ->_title($this->__('Redirect'));
        $logId = $this->getRequest()->getParam('id');
        $log = Mage::getModel('paybyfinance/log')->load($logId);

        $this->_initAction();
        $this->getLayout()->getBlock('pbf_redirect')->setLog($log);
        $this->renderLayout();
    }
}
