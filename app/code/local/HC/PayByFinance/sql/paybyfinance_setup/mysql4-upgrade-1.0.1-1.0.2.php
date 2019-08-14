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

$updater->run(
    "
ALTER TABLE {$this->getTable('paybyfinance_service')}
MODIFY COLUMN `fee` decimal(9,4);

ALTER TABLE {$this->getTable('paybyfinance_service')}
MODIFY COLUMN `min_amount` decimal(9,4);
    "
);

$updater->endSetup();
