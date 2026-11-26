<?php
use XoopsModules\Jill_query\Tools;
use XoopsModules\Jill_query\Jill_query;
use XoopsModules\Jill_query\Jill_query_data;

if (!class_exists('XoopsModules\Jill_query\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/jill_query/preloads/autoloader.php';
}

//區塊：指定事件的查詢表單
function jill_query_show($options)
{
    Tools::get_session();
    $query = Jill_query::get($options[0] ?? 0);
    if (empty($query) || empty($query['isEnable']) || !Tools::group_perm($query['read_group_arr'])) {
        return [];
    }
    xoops_loadLanguage('main', 'jill_query');
    $form = Jill_query_data::form_data($query);
    if (empty($form['cols'])) {
        return [];
    }
    return ['form' => $form];
}

//區塊編輯：選擇事件
function jill_query_show_edit($options)
{
    $default = (int) ($options[0] ?? 0);
    $opts = '';
    foreach (Jill_query::menu_options() as $qsn => $title) {
        $selected = ($qsn === $default) ? ' selected' : '';
        $opts .= "<option value='{$qsn}'{$selected}>{$title}</option>";
    }
    return "<div class='mb-2'><label for='jq_show_opt0'>" . _MB_JILL_QUERY__OPT0 . "</label>
        <select name='options[0]' id='jq_show_opt0' class='form-select'><option value=''>" . _MB_JILL_QUERY__OPT0 . "</option>{$opts}</select></div>";
}
