<?php
use Xmf\Request;
use XoopsModules\Jill_query\Jill_query_tags;
use XoopsModules\Jill_query\Tools;
use XoopsModules\Tadtools\Utility;

require_once __DIR__ . '/header.php';

$op     = Request::getString('op');
$tag_sn = Request::getInt('tag_sn');
$back   = XOOPS_URL . '/modules/jill_query/admin/tag.php';

//寫入類動作：只收 POST＋token
if (in_array($op, ['tag_store', 'tag_update', 'tag_toggle', 'tag_destroy'], true)) {
    Tools::chk_post($back);
    Utility::xoops_security_check('', '', $back);
}

switch ($op) {
    case 'tag_store':
        Jill_query_tags::store();
        header("Location: {$back}");
        exit;

    case 'tag_update':
        Jill_query_tags::update($tag_sn);
        header("Location: {$back}");
        exit;

    case 'tag_toggle':
        Jill_query_tags::toggle($tag_sn);
        header("Location: {$back}");
        exit;

    case 'tag_destroy':
        Jill_query_tags::destroy($tag_sn);
        header("Location: {$back}");
        exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_query_adm_tag.tpl';
xoops_cp_header();

Jill_query_tags::index($tag_sn);
$op = 'tag';

require_once __DIR__ . '/footer.php';
