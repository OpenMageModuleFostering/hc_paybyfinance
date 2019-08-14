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
* @license   http://www.gnu.org/copyleft/gpl.html GPL License
* @link      http://www.healthywebsites.co.uk/
*
*/

$installer = $this;
$setup = Mage::getModel('eav/entity_setup', 'core_setup');

$installer->startSetup();

//set up finance amount fields
$setup->run(
    "
ALTER TABLE {$setup->getTable('sales_flat_quote_address')} ADD COLUMN finance_amount decimal(12,4);
ALTER TABLE {$setup->getTable('sales_flat_order')} ADD COLUMN finance_amount decimal(12,4);
ALTER TABLE {$setup->getTable('sales_flat_invoice')} ADD COLUMN finance_amount decimal(12,4);

ALTER TABLE {$setup->getTable('sales_flat_quote_address')}
                                ADD COLUMN base_finance_amount decimal(12,4);
ALTER TABLE {$setup->getTable('sales_flat_order')} ADD COLUMN base_finance_amount decimal(12,4);
ALTER TABLE {$setup->getTable('sales_flat_invoice')} ADD COLUMN base_finance_amount decimal(12,4);

ALTER TABLE {$setup->getTable('sales_flat_quote_address')} ADD COLUMN finance_deposit decimal(12,4);
ALTER TABLE {$setup->getTable('sales_flat_order')} ADD COLUMN finance_deposit decimal(12,4);
ALTER TABLE {$setup->getTable('sales_flat_invoice')} ADD COLUMN finance_deposit decimal(12,4);

ALTER TABLE {$setup->getTable('sales_flat_quote_address')} ADD COLUMN finance_service int(11);
ALTER TABLE {$setup->getTable('sales_flat_order')} ADD COLUMN finance_service int(11);
ALTER TABLE {$setup->getTable('sales_flat_invoice')} ADD COLUMN finance_service int(11);

ALTER TABLE {$setup->getTable('sales_flat_order')} ADD COLUMN finance_status varchar(1);
ALTER TABLE {$setup->getTable('sales_flat_order')} ADD COLUMN finance_application_no varchar(50);
    "
);

$installer->run(
    "
DROP TABLE IF EXISTS {$this->getTable('paybyfinance_service')};
CREATE TABLE {$this->getTable('paybyfinance_service')} (
  `name` varchar(255) NOT NULL default '',
  `service_id` int(11) unsigned NOT NULL auto_increment,
  `type` smallint(6) NOT NULL default '0',
  `apr` double(10,8) NOT NULL default '0',
  `term` smallint(6) NOT NULL default '0',
  `defer_term` smallint(6) NOT NULL default '0',
  `option_term` smallint(6) NOT NULL default '0',
  `deposit` float(7,4) NOT NULL default '0',
  `fee` decimal(9,4) NOT NULL default '0',
  `min_amount` decimal(9,4) NOT NULL default '0',
  `multiplier` double(10,8) NOT NULL default '0',
  `rpm` double(10,8) NOT NULL default '0',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    "
);

$installer->run(
    "
DROP TABLE IF EXISTS {$this->getTable('paybyfinance_log')};
CREATE TABLE {$this->getTable('paybyfinance_log')} (
  `api_id` int(11) unsigned NOT NULL auto_increment,
  `type` varchar(255) NOT NULL default '',
  `flow` varchar(255) NOT NULL default '',
  `time` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  PRIMARY KEY (`api_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    "
);

// Add test service.
$service = Mage::getModel('paybyfinance/service');
$service->setName('Interest bearing')
    ->setType(31)
    ->setApr(0)
    ->setTerm(6)
    ->setDeferTerm(0)
    ->setOptionTerm(0)
    ->setDeposit(0)
    ->setFee(0)
    ->setMinAmount(350)
    ->setMultiplier(0.16666667)
    ->setRpm(0)
    ->save();

$service = Mage::getModel('paybyfinance/service');
$service->setName('Interest bearing')
    ->setType(31)
    ->setApr(0)
    ->setTerm(12)
    ->setDeferTerm(0)
    ->setOptionTerm(0)
    ->setDeposit(10)
    ->setFee(0)
    ->setMinAmount(1000)
    ->setMultiplier(0.091680)
    ->setRpm(0)
    ->save();

$service = Mage::getModel('paybyfinance/service');
$service->setName('Interest free')
    ->setType(32)
    ->setApr(0)
    ->setTerm(18)
    ->setDeferTerm(0)
    ->setOptionTerm(0)
    ->setDeposit(10)
    ->setFee(0)
    ->setMinAmount(1000)
    ->setMultiplier(0.091680)
    ->setRpm(0)
    ->save();

// Blocks.
$blocks = array(
    'information' => 'Finance Information',
    'accepted' => 'Finance Accepted',
    'referred' => 'Finance Referred',
    'declined' => 'Finance Declined',
    'abandoned' => 'Finance Abandoned',
    'error' => 'Finance Error',
);

foreach ($blocks as $key => $title) {
    $content = file_get_contents(
        'app/code/local/HC/PayByFinance/sql/paybyfinance_setup/html/'.
        $key.'.html'
    );
    $block = Mage::getModel('cms/block');
    $block->setTitle($title);
    $block->setIdentifier('paybyfinance-' . $key);
    $block->setStores(array(0));
    $block->setIsActive(1);
    $block->setContent($content);
    $block->save();

    // Set default config values.
    $installer->setConfigData(
        'hc_paybyfinance/blocks_info/'.$key,
        $block->getId()
    );
}

// Order statuses.
$statuses = array(
    'finance_accepted' => 'Finance Accepted',
    'finance_referred' => 'Finance Referred',
    'finance_declined' => 'Finance Declined',
    'finance_abandoned' => 'Finance Abandoned',
    'finance_error' => 'Finance Error',
);

foreach ($statuses as $key => $value) {
    $status = Mage::getModel('sales/order_status');
    $status->setStatus($key)->setLabel($value)
        ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
        ->save();
}

$setup->addAttribute(
    'catalog_product', 'paybyfinance_enable', array(
        'group'       => 'Hitachi Capital - Pay By Finance',
        'type'        => 'int',
        'backend'     => '',
        'frontend'    => '',
        'label'       => 'Enable Finance',
        'input'       => 'select',
        'class'       => '',
        'source'      => 'paybyfinance/config_source_catalog_product_finance',
        'global'      => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'     => true,
        'required'    => false,
        'user_defined' => false,
        'default'     => '',
        'searchable'  => false,
        'filterable'  => true,
        'comparable'  => true,
        'visible_on_front' => false,
        'visible_in_advanced_search' => false,
        'used_in_product_listing' => false,
        'used_for_sort_by' => false,
        'unique'      => false,
        'apply_to'    => '',
    )
);

$content = file_get_contents(
    'app/code/local/HC/PayByFinance/sql/paybyfinance_setup/html/page-finance-options.html'
);
$cmsPage = Array (
    'title' => 'Finance Options',
    'root_template' => 'one_column',
    'identifier' => 'finance-options',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0),
    'sort_order' => 0
);

Mage::getModel('cms/page')->setData($cmsPage)->save();

$installer->endSetup();
