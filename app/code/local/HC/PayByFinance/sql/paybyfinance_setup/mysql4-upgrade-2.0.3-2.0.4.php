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
// @codingStandardsIgnoreFile

//Adding new status for address inconsistency where acceptance notification came and billing and shipping addresses are not same...

$updater = $this;

$updater->startSetup();

$status = Mage::getModel('sales/order_status');
$status->setStatus(HC_PayByFinance_Helper_Data::STATUS_ADDRESS_INCONSISTENT)->setLabel('Address Data Inconsistent')
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();

$updater->endSetup();
