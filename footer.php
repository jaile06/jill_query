<?php
use XoopsModules\Tadtools\Utility;

$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('now_op', $op);
require_once XOOPS_ROOT_PATH . '/footer.php';
