<?php
use XoopsModules\Jill_query\Tools;

if (!class_exists('XoopsModules\Jill_query\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/jill_query/preloads/autoloader.php';
}
xoops_loadLanguage('main', 'tadtools');

Tools::get_session();

$interface_menu[_MD_JILLQUERY_SMNAME1] = 'index.php';
$interface_icon[_MD_JILLQUERY_SMNAME1] = 'fa-search';

if (Tools::isAdmin()) {
    $interface_menu[_TAD_TO_ADMIN] = 'admin/main.php';
    $interface_icon[_TAD_TO_ADMIN] = 'fa-sign-in';
}
