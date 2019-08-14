<?php
/**
* Hitachi Capital Pay By Finance
*
* Hitachi Capital Pay By Finance Extension
*
* PHP version >= 5.3.*
*
* @category  HC
* @package   PayByFinance
* @author    Healthy Websites <support@healthywebsites.co.uk>
* @copyright 2014 Hitachi Capital
* @license   http://www.healthywebsites.co.uk/license.html HWS License
* @link      http://www.healthywebsites.co.uk/
*
*/

$updater = $this;      // $this is class Mage_Eav_Model_Entity_Setup
$updater->startSetup();

$updater->run(
    "
ALTER TABLE {$this->getTable('paybyfinance_service')}
MODIFY COLUMN `fee` decimal(9,4);

ALTER TABLE {$this->getTable('paybyfinance_service')}
MODIFY COLUMN `min_amount` decimal(9,4);
    "
);

$updater->endSetup();
