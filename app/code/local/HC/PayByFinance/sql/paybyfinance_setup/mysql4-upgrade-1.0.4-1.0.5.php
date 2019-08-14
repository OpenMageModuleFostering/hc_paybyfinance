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

$installer = Mage::getModel('eav/entity_setup', 'core_setup');
$entityTypeId = $installer->getEntityTypeId('catalog_product');

$idAttribute = $installer->getAttribute($entityTypeId, 'paybyfinance_enable', 'attribute_id');
$installer->updateAttribute(
    $entityTypeId,
    $idAttribute,
    array(
        'used_in_product_listing' => 1
    )
);

$updater->endSetup();
