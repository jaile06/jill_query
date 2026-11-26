<?php
use Xmf\Request;
use XoopsModules\Jill_query\Tools;
use XoopsModules\Jill_query\Jill_query_data;
use XoopsModules\Tadtools\Utility;

require_once __DIR__ . '/header.php';

$op  = Request::getString('op');
$qsn = Request::getInt('qsn');
$ssn = Request::getInt('ssn');

//寫入類動作：只收 POST＋token（save_cell 是 AJAX，在下方用不清除 token 的檢查）
if (in_array($op, ['import', 'row_destroy'], true)) {
    $back = XOOPS_URL . "/modules/jill_query/excel.php?qsn={$qsn}";
    Tools::chk_post($back);
    Utility::xoops_security_check('', '', $back);
}

/* 不載入佈景的動作：AJAX 與下載 */
switch ($op) {
    //行內修改儲存格（Jeditable）
    case 'save_cell':
        Tools::raw_output();
        Tools::chk_ajax_token();
        echo Jill_query_data::save_cell($qsn, Request::getString('id'), Request::getString('value'));
        exit;

    //下載空白範本
    case 'sample':
        Jill_query_data::sample($qsn);
        exit;

    //下載會員 Email
    case 'users':
        Jill_query_data::users($qsn);
        exit;

    //匯入資料
    case 'import':
        Jill_query_data::import($qsn);
        header('Location: ' . XOOPS_URL . "/modules/jill_query/excel.php?qsn={$qsn}");
        exit;

    //刪除一列
    case 'row_destroy':
        Jill_query_data::destroy_row($qsn, $ssn);
        header('Location: ' . XOOPS_URL . "/modules/jill_query/excel.php?qsn={$qsn}");
        exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_query_excel.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

Jill_query_data::manage($qsn);
$op = 'manage';

require_once __DIR__ . '/footer.php';
