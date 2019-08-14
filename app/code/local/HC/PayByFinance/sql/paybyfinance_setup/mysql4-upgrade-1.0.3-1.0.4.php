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

$updater = $this;      // $this is class Mage_Eav_Model_Entity_Setup
$updater->startSetup();

$resinstaller = Mage::getModel('sales/resource_setup', 'core_setup');

/**
 * Add 'paybyfinance_enable' attribute for entities.
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $resinstaller->addAttribute($entity, 'paybyfinance_enable', $options);
}

$updater->endSetup();
