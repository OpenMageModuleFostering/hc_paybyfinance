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

$setup = Mage::getModel('customer/entity_setup', 'core_setup');

$setup->addAttribute(
    'customer_address',
    'house_name',
    array(
        'type' => 'varchar',
        'input' => 'text',
        'label' => 'House Name',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'visible_on_front' => 1
    )
);
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'house_name')
    ->setData(
        'used_in_forms',
        array(
            'customer_register_address',
            'customer_address_edit',
            'adminhtml_customer_address'
        )
    )->save();

$setup->addAttribute(
    'customer_address',
    'house_number',
    array(
        'type' => 'varchar',
        'input' => 'text',
        'label' => 'House Number',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'visible_on_front' => 1
    )
);
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'house_number')
    ->setData(
        'used_in_forms',
        array(
            'customer_register_address',
            'customer_address_edit',
            'adminhtml_customer_address'
        )
    )->save();

$setup->addAttribute(
    'customer_address',
    'flat_number',
    array(
        'type' => 'varchar',
        'input' => 'text',
        'label' => 'Flat Number',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'visible_on_front' => 1
    )
);
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'flat_number')
    ->setData(
        'used_in_forms',
        array(
            'customer_register_address',
            'customer_address_edit',
            'adminhtml_customer_address'
        )
    )->save();

$tablequote = $this->getTable('sales/quote_address');
$tableorder = $this->getTable('sales/order_address');
$updater->run(
    "ALTER TABLE  $tablequote ADD  `house_name` varchar(255) NOT NULL"
);
$updater->run(
    "ALTER TABLE  $tableorder ADD  `house_name` varchar(255) NOT NULL"
);
$updater->run(
    "ALTER TABLE  $tablequote ADD  `house_number` varchar(255) NOT NULL"
);
$updater->run(
    "ALTER TABLE  $tableorder ADD  `house_number` varchar(255) NOT NULL"
);
$updater->run(
    "ALTER TABLE  $tablequote ADD  `flat_number` varchar(255) NOT NULL"
);
$updater->run(
    "ALTER TABLE  $tableorder ADD  `flat_number` varchar(255) NOT NULL"
);

$updater->endSetup();
