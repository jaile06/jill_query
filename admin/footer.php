<?php
$xoopsTpl->assign('now_op', $op ?? '');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/font-awesome/css/font-awesome.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/xoops_adm' . ($_SESSION['bootstrap'] ?? '5') . '.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/jill_query/css/admin.css');
xoops_cp_footer();
