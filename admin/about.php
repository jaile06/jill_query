<?php
require_once dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php';
xoops_cp_header();

$adminObject = \Xmf\Module\Admin::getInstance();
$adminObject->displayNavigation(basename(__FILE__));
$adminObject->displayAbout(false);

xoops_cp_footer();
