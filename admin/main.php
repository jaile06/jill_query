<?php
use Xmf\Request;
use XoopsModules\Jill_query\Jill_query;
use XoopsModules\Jill_query\Jill_query_data;
use XoopsModules\Jill_query\Tools;
use XoopsModules\Tadtools\Utility;

require_once __DIR__ . '/header.php';

$op  = Request::getString('op');
$qsn = Request::getInt('qsn');
$url = XOOPS_URL . '/modules/jill_query/admin';

//寫入類動作：只收 POST＋token
if (in_array($op, ['jill_query_store', 'jill_query_update', 'jill_query_destroy', 'jill_query_copy'], true)) {
    Tools::chk_post("{$url}/main.php");
    Utility::xoops_security_check('', '', "{$url}/main.php");
}

/* 不載入後台外框的動作 */
switch ($op) {
    case 'jill_query_store':
        $qsn = Jill_query::store();
        header("Location: {$url}/setcol.php?qsn={$qsn}");
        exit;

    case 'jill_query_update':
        Jill_query::update($qsn);
        header("Location: {$url}/setcol.php?qsn={$qsn}");
        exit;

    case 'jill_query_destroy':
        Jill_query::destroy($qsn);
        header("Location: {$url}/main.php");
        exit;

    case 'jill_query_copy':
        $qsn = Jill_query::copy($qsn);
        redirect_header("{$url}/main.php?op=jill_query_edit&qsn={$qsn}", 3, _MA_JILLQUERY_COPYSUCCESS);

    case 'export':
        Jill_query_data::export($qsn);
        exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_query_adm_main.tpl';
xoops_cp_header();

switch ($op) {
    case 'jill_query_create':
    case 'jill_query_edit':
        Jill_query::create($qsn);
        $op = 'form';
        break;

    default:
        Jill_query::index_admin();
        $op = 'list';
        break;
}

require_once __DIR__ . '/footer.php';
