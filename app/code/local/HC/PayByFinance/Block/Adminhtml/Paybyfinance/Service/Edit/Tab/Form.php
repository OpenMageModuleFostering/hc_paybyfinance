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
 * @copyright 2014 Cohesion Digital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.cohesiondigital.co.uk/
 *
 */

/**
 * Data form for service
 *
 * @uses     Mage_Adminhtml_Block_Widget_Form
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Block_Adminhtml_Paybyfinance_Service_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * _prepareForm
     *
     * @return mixed Value.
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $helper = Mage::helper('paybyfinance');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'service_form',
            array('legend' => $helper->__('Service information'))
        );

        $fieldset->addField(
            'name', 'text', array(
                'label'     => $helper->__('Name'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'name',
            )
        );

        $fieldset->addField(
            'type', 'select', array(
                'label'     => $helper->__('Service Type'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'type',
                'values' => Mage::getSingleton('paybyfinance/config_source_type')->toOptionArray(),
            )
        );

        $fieldset->addField(
            'apr', 'text', array(
                'label'     => $helper->__('APR'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'apr',
            )
        );

        $fieldset->addField(
            'term', 'text', array(
                'label'     => $helper->__('Term'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'term',
            )
        );

        $fieldset->addField(
            'defer_term', 'text', array(
                'label'     => $helper->__('Defer Term'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'defer_term',
            )
        );

        $fieldset->addField(
            'option_term', 'text', array(
                'label'     => $helper->__('Option Term'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'option_term',
            )
        );

        $fieldset->addField(
            'deposit', 'text', array(
                'label'     => $helper->__('Deposit (%)'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'deposit',
            )
        );

        $fieldset->addField(
            'fee', 'text', array(
                'label'     => $helper->__('Fee'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'fee',
            )
        );

        $fieldset->addField(
            'min_amount', 'text', array(
                'label'     => $helper->__('Minimum Loan Amount'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'min_amount',
            )
        );

        $fieldset->addField(
            'max_amount', 'text', array(
                'label'     => $helper->__('Maximum Loan Amount'),
                'required'  => false,
                'name'      => 'max_amount',
            )
        );

        $fieldset->addField(
            'multiplier', 'text', array(
                'label'     => $helper->__('Multiplier'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'multiplier',
            )
        );

        $fieldset->addField(
            'rpm', 'text', array(
                'label'     => $helper->__('Rpm'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'rpm',
            )
        );

        $fieldset->addField(
            'store_id', 'select', array(
                'label'     => $helper->__('Limit to store'),
                'required'  => false,
                'name'      => 'store_id',
                'values' => Mage::getSingleton('adminhtml/system_store')
                    ->getStoreValuesForForm(false, true),
            )
        );



        if (Mage::getSingleton('adminhtml/session')->getServiceData()) {
            $form->setValues(
                Mage::getSingleton('adminhtml/session')->getServiceData()
            );
            Mage::getSingleton('adminhtml/session')->setServiceData(null);
        } elseif (Mage::registry('service_data')) {
            $form->setValues(Mage::registry('service_data')->getData());
        }

        /*
         Show defer_term only for type 25, 34, 35
         */
        $this->setChild(
            'form_after', $this->getLayout()
                ->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('type', 'type')
                ->addFieldMap('defer_term', 'defer_term')
                ->addFieldDependence(
                    'defer_term', 'type',
                    array((string) HC_PayByFinance_Model_Config_Source_Type::TYPE25,
                        (string) HC_PayByFinance_Model_Config_Source_Type::TYPE34,
                        (string) HC_PayByFinance_Model_Config_Source_Type::TYPE35)
                )
        );

        return parent::_prepareForm();
    }
}
