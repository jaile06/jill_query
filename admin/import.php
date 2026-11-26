<?php
use Xmf\Request;
use XoopsModules\Jill_query\Tools;
use XoopsModules\Jill_query\Jill_query_data;
use XoopsModules\Tadtools\Utility;

require_once __DIR__ . '/header.php';

$op = Request::getString('op');

if ('import' === $op) {
    $back = XOOPS_URL . '/modules/jill_query/admin/import.php';
    Tools::chk_post($back);
    Utility::xoops_security_check('', '', $back);
    $qsn = Jill_query_data::import_new();
    header('Location: ' . XOOPS_URL . "/modules/jill_query/admin/setcol.php?qsn={$qsn}");
    exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_query_adm_import.tpl';
xoops_cp_header();

Tools::chk_admin();
Utility::token_form();
$op = 'import';

require_once __DIR__ . '/footer.php';
