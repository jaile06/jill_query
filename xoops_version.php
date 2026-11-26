<?php
$modversion = [];

//---模組基本資訊---//
$modversion['name']        = _MI_JILLQUERY_NAME;
$modversion['version']     = ($_SESSION['xoops_version'] ?? 0) >= 20511 ? '3.0.0-Stable' : '3.0';
$modversion['description'] = _MI_JILLQUERY_DESC;
$modversion['author']      = _MI_JILLQUERY_AUTHOR;
$modversion['credits']     = _MI_JILLQUERY_CREDITS;
$modversion['help']        = 'page=help';
$modversion['license']     = 'GNU GPL 2.0';
$modversion['image']       = 'images/logo.png';
$modversion['dirname']     = basename(__DIR__);

//---模組狀態資訊---//
$modversion['release_date']        = '2026-11-26';
$modversion['module_website_url']  = 'https://github.com/tnjaile/';
$modversion['module_website_name'] = _MI_JILLQUERY_AUTHOR_WEB;
$modversion['module_status']       = 'release';
$modversion['author_website_url']  = 'https://github.com/tnjaile/';
$modversion['author_website_name'] = _MI_JILLQUERY_AUTHOR_WEB;
$modversion['min_php']             = '8.3';
$modversion['min_xoops']           = '2.5.11';

//---paypal資訊---//
$modversion['paypal'] = [
    'business'      => 'tnjaile@gmail.com',
    'item_name'     => 'Donation :' . _MI_JILLQUERY_AUTHOR,
    'amount'        => 0,
    'currency_code' => 'USD',
];

//---安裝設定---//
$modversion['onUpdate'] = 'include/onUpdate.php';

//---後台---//
$modversion['system_menu'] = 1;
$modversion['hasAdmin']    = 1;
$modversion['adminindex']  = 'admin/main.php';
$modversion['adminmenu']   = 'admin/menu.php';

//---資料表架構---//
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables']           = ['jill_query', 'jill_query_col', 'jill_query_col_value', 'jill_query_sn', 'jill_query_tags'];

//---前台---//
$modversion['hasMain'] = 1;

//---樣板設定---//
$modversion['templates'] = [
    ['file' => 'jill_query_index.tpl', 'description' => 'front query'],
    ['file' => 'jill_query_excel.tpl', 'description' => 'undertaker data'],
    ['file' => 'jill_query_adm_main.tpl', 'description' => 'admin events'],
    ['file' => 'jill_query_adm_setcol.tpl', 'description' => 'admin columns'],
    ['file' => 'jill_query_adm_import.tpl', 'description' => 'admin import'],
    ['file' => 'jill_query_adm_tag.tpl', 'description' => 'admin tags'],
];

//---區塊設定---//
$modversion['blocks'] = [
    [
        'file'        => 'jill_query_show.php',
        'name'        => _MI_JILL_QUERY_SHOW_BLOCK_NAME,
        'description' => _MI_JILL_QUERY_SHOW_BLOCK_DESC,
        'show_func'   => 'jill_query_show',
        'edit_func'   => 'jill_query_show_edit',
        'template'    => 'jill_query_show.tpl',
        'options'     => '',
    ],
    [
        'file'        => 'jill_query_public.php',
        'name'        => _MI_JILL_QUERY_PUBLIC_BLOCK_NAME,
        'description' => _MI_JILL_QUERY_PUBLIC_BLOCK_DESC,
        'show_func'   => 'jill_query_public',
        'edit_func'   => 'jill_query_public_edit',
        'template'    => 'jill_query_public.tpl',
        'options'     => '|20',
    ],
];
