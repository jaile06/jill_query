<?php
use XoopsModules\Jill_query\Tools;
use XoopsModules\Jill_query\Jill_query;
use XoopsModules\Jill_query\Jill_query_data;

if (!class_exists('XoopsModules\Jill_query\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/jill_query/preloads/autoloader.php';
}

//區塊：公開事件的前 N 筆資料
function jill_query_public($options)
{
    Tools::get_session();
    $query = Jill_query::get($options[0] ?? 0);
    if (empty($query) || empty($query['isEnable']) || 1 !== (int) $query['ispublic'] || !Tools::group_perm($query['read_group_arr'])) {
        return [];
    }
    xoops_loadLanguage('main', 'jill_query');
    $limit = max(1, min(200, (int) ($options[1] ?? 20)));
    $data  = Jill_query_data::public_rows($query, $limit);

    return [
        'qsn'      => (int) $query['qsn'],
        'title'    => htmlspecialchars($query['title'], ENT_QUOTES, 'UTF-8'),
        'show_col' => $data['show_col'],
        'result'   => $data['result'],
    ];
}

//區塊編輯：選擇公開事件、顯示筆數
function jill_query_public_edit($options)
{
    $default = (int) ($options[0] ?? 0);
    $num     = max(1, (int) ($options[1] ?? 20));
    $opts    = '';
    foreach (Jill_query::menu_options(true) as $qsn => $title) {
        $selected = ($qsn === $default) ? ' selected' : '';
        $opts .= "<option value='{$qsn}'{$selected}>{$title}</option>";
    }
    return "<div class='mb-2'><label for='jq_pub_opt0'>" . _MB_JILL_QUERY_PUBLIC_OPT0 . "</label>
        <select name='options[0]' id='jq_pub_opt0' class='form-select'><option value=''>" . _MB_JILL_QUERY__OPT0 . "</option>{$opts}</select></div>
        <div class='mb-2'><label for='jq_pub_opt1'>" . _MB_JILL_QUERY_PUBLIC_OPT1 . "</label>
        <input type='number' min='1' max='200' name='options[1]' id='jq_pub_opt1' value='{$num}' class='form-control'></div>";
}
