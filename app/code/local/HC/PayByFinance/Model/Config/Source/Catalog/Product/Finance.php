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
 * Provides the list of config values to enable finance
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Config_Source_Catalog_Product_Finance
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const VALUES_CONFIG = 0;
    const VALUES_ENABLE = 1;
    const VALUES_DISABLE = 2;

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $helper = Mage::helper('paybyfinance');
            $this->_options = array(
                array(
                    'label' => $helper->__('Based on configuration'),
                    'value' => self::VALUES_CONFIG
                ),
                array(
                    'label' => $helper->__('Enable'),
                    'value' => self::VALUES_ENABLE
                ),
                array(
                    'label' => $helper->__('Disable'),
                    'value' => self::VALUES_DISABLE
                ),
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Get flat column names
     *
     * @return array Additional names
     */
    public function getFlatColums()
    {

        $columns = array(
            $this->getAttribute()->getAttributeCode() => array(
                'type'      => 'int',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            )
        );
        return $columns;
    }

    /**
     * Get select update for additional column
     *
     * @param integer $store Store
     *
     * @return Varien_Db_Select
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }

}
