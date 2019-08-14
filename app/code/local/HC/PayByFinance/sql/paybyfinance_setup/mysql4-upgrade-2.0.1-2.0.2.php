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

$updater->run(
    "
ALTER TABLE {$this->getTable('paybyfinance_service')}
ADD COLUMN `max_amount` decimal(9,4) NULL;
    "
);
$updater->endSetup();
