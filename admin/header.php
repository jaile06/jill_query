<?php
require_once dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php';
xoops_loadLanguage('main', 'jill_query');
xoops_loadLanguage('admin_common', 'tadtools');
require_once XOOPS_ROOT_PATH . '/modules/jill_query/interface.php';

if (!isset($xoopsTpl) || !is_object($xoopsTpl)) {
    require_once XOOPS_ROOT_PATH . '/class/template.php';
    $xoopsTpl = new \XoopsTpl();
}
