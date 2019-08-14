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
    ADD `store_id` SMALLINT(5) UNSIGNED NULL;
ALTER TABLE {$this->getTable('paybyfinance_service')}
    ADD CONSTRAINT `FK_paybyfinance_serice_core_store_store_id` FOREIGN KEY(`store_id`)
        REFERENCES `{$this->getTable('core_store')}` (`store_id`);
    "
);

$updater->endSetup();
