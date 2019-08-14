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
 * Grid widget for services
 *
 * @uses     Mage_Adminhtml_Block_Widget_Grid
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFInance_Block_Adminhtml_Paybyfinance_Service_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     *
     * @return void.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('ServiceGrid');
        // This is the primary key of the database
        $this->setDefaultSort('service_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * _prepareCollection
     *
     * @return mixed Value.
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('paybyfinance/service')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * _prepareColumns
     *
     * @return mixed Value.
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('paybyfinance');
        $this->addColumn(
            'service_id', array(
                'header'    => $helper->__('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'index'     => 'service_id',
            )
        );

        $this->addColumn(
            'type', array(
                'header'    => $helper->__('Type'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'type',
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => $helper->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );

        $this->addColumn(
            'apr', array(
                'header'    => $helper->__('APR'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'apr',
            )
        );

        $this->addColumn(
            'term', array(
                'header'    => $helper->__('Term'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'term',
            )
        );

        $this->addColumn(
            'defer_term', array(
                'header'    => $helper->__('Defer Term'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'defer_term',
            )
        );

        $this->addColumn(
            'option_term', array(
                'header'    => $helper->__('Option Term'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'option_term',
            )
        );

        $this->addColumn(
            'deposit', array(
                'header'    => $helper->__('Deposit (%)'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'deposit',
            )
        );

        $this->addColumn(
            'fee', array(
                'header'    => $helper->__('Fee'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'fee',
            )
        );

        $this->addColumn(
            'minimum_amount', array(
                'header'    => $helper->__('Minimum Loan Amount'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'min_amount',
            )
        );

        $this->addColumn(
            'maximum_amount', array(
                'header'    => $helper->__('Maximum Loan Amount'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'max_amount',
            )
        );

        $this->addColumn(
            'multiplier', array(
                'header'    => $helper->__('Multiplier'),
                'align'     => 'right',
                'index'     => 'multiplier',
            )
        );

        $this->addColumn(
            'rpm', array(
                'header'    => $helper->__('RPM'),
                'align'     => 'right',
                'type'  => 'number',
                'index'     => 'rpm',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id', array(
                    'header'    => $helper->__('Store Id'),
                    'align'     => 'right',
                    'type'  => 'store',
                    'store_view' => true,
                    'index'     => 'store_id',
                )
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Get Row Url
     *
     * @param object $row Row Object.
     *
     * @return string Value.
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Get Grid Url
     *
     * @return string Value.
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
