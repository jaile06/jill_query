<?php
use Xmf\Request;
use XoopsModules\Jill_query\Jill_query;
use XoopsModules\Jill_query\Jill_query_data;

require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'jill_query_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

$op  = Request::getString('op');
$qsn = Request::getInt('qsn');

switch ($op) {
    //送出查詢
    case 'show_result':
        Jill_query_data::show_result($qsn);
        $op = 'search';
        break;

    //公開事件的全部資料
    case 'public_query':
        Jill_query_data::public_query($qsn);
        break;

    default:
        if ($qsn) {
            Jill_query_data::search_form($qsn);
            $op = 'search';
        } else {
            Jill_query::index_front(Request::getInt('tag_sn'));
            $op = 'list';
        }
        break;
}

require_once __DIR__ . '/footer.php';
