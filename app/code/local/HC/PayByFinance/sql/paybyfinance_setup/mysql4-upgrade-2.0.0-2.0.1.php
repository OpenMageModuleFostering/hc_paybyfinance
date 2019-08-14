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

$updater = $this;

$updater->startSetup();

$resinstaller = Mage::getModel('sales/resource_setup', 'core_setup');

$configValuesMap = array(
    'hc_paybyfinance/general/declined_email'       => 'hc_paybyfinance_general_declined_email',
    'hc_paybyfinance/general/declined_email_guest' =>
        'hc_paybyfinance_general_declined_email_guest',
);

foreach ($configValuesMap as $configPath=>$configValue) {
    $resinstaller->setConfigData($configPath, $configValue);
}

$updater->endSetup();
