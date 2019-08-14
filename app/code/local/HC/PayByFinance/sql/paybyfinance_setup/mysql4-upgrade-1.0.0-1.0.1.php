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

$updater->endSetup();
