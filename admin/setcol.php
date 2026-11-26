<?php
use Xmf\Request;
use XoopsModules\Jill_query\Tools;
use XoopsModules\Jill_query\Jill_query_col;
use XoopsModules\Tadtools\Utility;

require_once __DIR__ . '/header.php';

$op   = Request::getString('op');
$qsn  = Request::getInt('qsn');
$qcsn = Request::getInt('qcsn');
$back = XOOPS_URL . "/modules/jill_query/admin/setcol.php?qsn={$qsn}";

//寫入類動作：只收 POST＋token（AJAX 動作在下方用不清除 token 的檢查）
if (in_array($op, ['col_store', 'col_destroy', 'update_operator'], true)) {
    Tools::chk_post($back);
    Utility::xoops_security_check('', '', $back);
}

switch ($op) {
    //AJAX：切換旗標
    case 'col_toggle':
        Tools::raw_output();
        Tools::chk_ajax_token();
        header('Content-Type: application/json; charset=utf-8');
        $value = Jill_query_col::toggle($qsn, $qcsn, Request::getString('name'));
        echo json_encode(['success' => null !== $value, 'value' => $value]);
        exit;

    //AJAX：改欄位名稱（Jeditable）
    case 'col_save_title':
        Tools::raw_output();
        Tools::chk_ajax_token();
        echo Jill_query_col::save_title($qsn, Request::getString('id'), Request::getString('value'));
        exit;

    //AJAX：拖曳排序
    case 'col_sort':
        Tools::raw_output();
        Tools::chk_ajax_token();
        echo Jill_query_col::update_sort($qsn);
        exit;

    case 'col_store':
        Jill_query_col::store($qsn);
        header("Location: {$back}");
        exit;

    case 'col_destroy':
        Jill_query_col::destroy($qsn, $qcsn);
        header("Location: {$back}");
        exit;

    case 'update_operator':
        Jill_query_col::update_operator($qsn);
        header("Location: {$back}");
        exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_query_adm_setcol.tpl';
xoops_cp_header();

Jill_query_col::index($qsn);
$op = 'setcol';

require_once __DIR__ . '/footer.php';
