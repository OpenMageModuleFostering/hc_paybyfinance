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
 * Controller for services
 *
 * @uses     Mage_Adminhtml_Controller_Action
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Adminhtml_Paybyfinance_ServiceController
    extends Mage_Adminhtml_Controller_Action
{
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
                $helper->__('Service')
            );
        return $this;
    }

    /**
     * indexAction
     *
     * @return void.
     */
    public function indexAction()
    {
        $this->_title($this->__('Pay By Finance'))
            ->_title($this->__('Services'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * editAction
     *
     * @return void.
     */
    public function editAction()
    {
        $helper = Mage::helper('paybyfinance');
        $serviceId = $this->getRequest()->getParam('id');
        $serviceModel = Mage::getModel('paybyfinance/service')
            ->load($serviceId);

        if ($serviceModel->getId() || $serviceId == 0) {

            Mage::register('service_data', $serviceModel);

            $this->loadLayout();
            $this->_setActiveMenu('adminhtml/paybyfinance_service');

            $this->_addBreadcrumb(
                $helper->__('Pay By FInance'), $helper->__('Pay By FInance')
            );
            $this->_addBreadcrumb(
                $helper->__('Service'), $helper->__('Service')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent(
                $this->getLayout()
                    ->createBlock(
                        'paybyfinance/adminhtml_paybyfinance_service_edit'
                    )
            )->_addLeft(
                $this->getLayout()->createBlock(
                    'paybyfinance/adminhtml_paybyfinance_service_edit_tabs'
                )
            );

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('paybyfinance')->__('Item does not exist')
                );
            $this->_redirect('*/*/');
        }
    }

    /**
     * New Service.
     *
     * @return mixed Value.
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save Service.
     *
     * @return mixed Value.
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $postData = $this->getRequest()->getPost();

                if ($postData['store_id'] == 0) {
                    $storeId = null;
                } else {
                    $storeId = $postData['store_id'];
                }
                $serviceModel = Mage::getModel('paybyfinance/service');

                $serviceModel->setId($this->getRequest()->getParam('id'))
                    ->setName($postData['name'])
                    ->setType($postData['type'])
                    ->setApr($postData['apr'])
                    ->setTerm($postData['term'])
                    ->setDeferTerm($postData['defer_term'])
                    ->setOptionTerm($postData['option_term'])
                    ->setDeposit($postData['deposit'])
                    ->setFee($postData['fee'])
                    ->setMinAmount($postData['min_amount'])
                    ->setMultiplier($postData['multiplier'])
                    ->setRpm($postData['rpm'])
                    ->setStoreId($storeId);


                if ($postData['max_amount'] === '') {
                    $serviceModel->setMaxAmount(null); //clear the value
                } else {
                    $serviceModel->setMaxAmount($postData['max_amount']);
                }

                $serviceModel->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('paybyfinance')
                        ->__('Service was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setServiceData(false);
                Mage::dispatchEvent(
                    'paybyfinance_services_modified', array('service'=>$serviceModel)
                );
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')
                    ->setServiceData($this->getRequest()->getPost());
                $this->_redirect(
                    '*/*/edit',
                    array('id' => $this->getRequest()->getParam('id'))
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete Service.
     *
     * @return mixed Value.
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0 ) {
            try {
                $serviceModel = Mage::getModel('paybyfinance/service');

                $serviceModel->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        Mage::helper('paybyfinance')
                            ->__('Service was successfully deleted')
                    );
                Mage::dispatchEvent(
                    'paybyfinance_services_modified', array('service'=>$serviceModel)
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
                $this->_redirect(
                    '*/*/edit',
                    array('id' => $this->getRequest()->getParam('id'))
                );
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Product grid for AJAX request. Sort and filter result for example.
     *
     * @return mixed Value.
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'paybyfinance/adminhtml_paybyfinance_service_grid'
                )->toHtml()
        );
    }
}
