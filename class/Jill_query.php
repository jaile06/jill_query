<?php
namespace XoopsModules\Jill_query;

use Xmf\Request;
use XoopsModules\Tadtools\CkEditor;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\SweetAlert2;
use XoopsModules\Tadtools\Utility;

/**
 * 查詢事件（jill_query）
 */
class Jill_query
{
    public static $filter_arr = [
        'int'  => ['qsn', 'isEnable', 'counter', 'uid', 'ispublic', 'tag_sn'],
        'html' => ['directions'],
        'pass' => ['read_group', 'read_group_arr', 'editor_arr'],
    ];

    //取得單一事件（原始值，read_group 另解成陣列）
    public static function get($qsn): array
    {
        global $xoopsDB;
        $qsn = (int) $qsn;
        if (empty($qsn)) {
            return [];
        }
        $sql    = 'SELECT * FROM `' . $xoopsDB->prefix('jill_query') . "` WHERE `qsn`='{$qsn}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data   = $xoopsDB->fetchArray($result);
        if (empty($data)) {
            return [];
        }
        $groups                 = json_decode((string) $data['read_group'], true);
        $data['read_group_arr'] = is_array($groups) ? array_map('intval', $groups) : [];
        return $data;
    }

    //取得事件並做前台開放檢查（存在、啟用、群組）；不通過直接導回
    public static function get_readable($qsn): array
    {
        $query = self::get($qsn);
        if (empty($query)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 3, _MD_JILLQUERY_EMPTYQSN);
        }
        if (empty($query['isEnable']) && !Tools::isAdmin()) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 3, _MD_JILLQUERY_CLOSED);
        }
        if (!Tools::group_perm($query['read_group_arr'])) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 3, _MD_JILLQUERY_ILLEGAL);
        }
        return $query;
    }

    //前台：列出啟用中且有權限的事件
    public static function index_front($tag_sn = 0)
    {
        global $xoopsDB, $xoopsTpl;
        $tag_sn  = (int) $tag_sn;
        $and_tag = $tag_sn ? " AND `tag_sn`='{$tag_sn}'" : '';
        $sql     = 'SELECT `qsn`, `title`, `read_group`, `tag_sn`, `editorEmail`, `isEnable` FROM `' . $xoopsDB->prefix('jill_query') . "` WHERE `isEnable`='1' {$and_tag} ORDER BY `qsn` DESC";

        $PageBar = Utility::getPageBar($sql, 20, 10);
        $result  = $xoopsDB->query($PageBar['sql']) or Utility::web_error($PageBar['sql'], __FILE__, __LINE__);

        $tags = Jill_query_tags::get_all(true);
        $all  = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            // ponytail: 分頁後才過濾群組，無權限的事件會讓某頁少於 20 筆；要精準分頁需把群組條件寫進 SQL
            if (!Tools::group_perm($data['read_group'])) {
                continue;
            }
            $all[] = [
                'qsn'       => (int) $data['qsn'],
                'title'     => htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8'),
                'tag'       => $tags[$data['tag_sn']] ?? null,
                'undertaker'=> Tools::is_undertaker($data),
            ];
        }

        $xoopsTpl->assign('all_query', $all);
        $xoopsTpl->assign('tags', $tags);
        $xoopsTpl->assign('tag_sn', $tag_sn);
        $xoopsTpl->assign('bar', $PageBar['bar']);
    }

    //後台：列出全部事件
    public static function index_admin()
    {
        global $xoopsDB, $xoopsTpl;

        $sql     = 'SELECT * FROM `' . $xoopsDB->prefix('jill_query') . '` ORDER BY `isEnable` DESC, `qsn` DESC';
        $PageBar = Utility::getPageBar($sql, 20, 10);
        $result  = $xoopsDB->query($PageBar['sql']) or Utility::web_error($PageBar['sql'], __FILE__, __LINE__);

        $groups = Tools::all_groups();
        $tags   = Jill_query_tags::get_all();
        $all    = [];
        $qsns   = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $read_group = json_decode((string) $data['read_group'], true);
            $names      = [];
            foreach ((array) $read_group as $gid) {
                if (isset($groups[$gid])) {
                    $names[] = htmlspecialchars($groups[$gid], ENT_QUOTES, 'UTF-8');
                }
            }
            $qsn    = (int) $data['qsn'];
            $qsns[] = $qsn;
            $all[$qsn] = [
                'qsn'         => $qsn,
                'title'       => htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8'),
                'editorEmail' => htmlspecialchars(implode('; ', Tools::email_list($data['editorEmail'])), ENT_QUOTES, 'UTF-8'),
                'passwd'      => htmlspecialchars($data['passwd'], ENT_QUOTES, 'UTF-8'),
                'isEnable'    => (int) $data['isEnable'],
                'ispublic'    => (int) $data['ispublic'],
                'counter'     => (int) $data['counter'],
                'groups'      => implode(' | ', $names),
                'tag'         => $tags[$data['tag_sn']] ?? null,
                'rows'        => 0,
                'cols'        => 0,
            ];
        }

        // 一次算出各事件資料數、欄位數
        if ($qsns) {
            $in = implode(',', $qsns);
            foreach (['rows' => 'jill_query_sn', 'cols' => 'jill_query_col'] as $key => $table) {
                $sql    = "SELECT `qsn`, COUNT(*) FROM `" . $xoopsDB->prefix($table) . "` WHERE `qsn` IN ({$in}) GROUP BY `qsn`";
                $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
                while (list($qsn, $count) = $xoopsDB->fetchRow($result)) {
                    $all[(int) $qsn][$key] = (int) $count;
                }
            }
        }

        $token = Tools::token();
        $xoopsTpl->assign('csrf_token', $token);
        $SweetAlert = new SweetAlert2();
        $SweetAlert->setVar('method', 'post');
        $SweetAlert->render('jill_query_destroy_func', XOOPS_URL . "/modules/jill_query/admin/main.php?op=jill_query_destroy&XOOPS_TOKEN_REQUEST={$token}&qsn=", 'qsn');

        $xoopsTpl->assign('all_query', array_values($all));
        $xoopsTpl->assign('bar', $PageBar['bar']);
    }

    //事件編輯表單
    public static function create($qsn = 0)
    {
        global $xoopsTpl;
        Tools::chk_admin();

        $qsn   = (int) $qsn;
        $query = $qsn ? self::get($qsn) : [];
        if ($qsn && empty($query)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/admin/main.php', 3, _MD_JILLQUERY_EMPTYQSN);
        }

        $def = [
            'qsn'            => 0,
            'title'          => '',
            'directions'     => '',
            'editorEmail'    => Tools::email(),
            'isEnable'       => 1,
            'counter'        => 0,
            'passwd'         => '',
            'ispublic'       => 0,
            'read_group_arr' => [XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
            'tag_sn'         => 0,
        ];
        $query = $query ?: $def;

        foreach (['qsn', 'title', 'editorEmail', 'isEnable', 'counter', 'passwd', 'ispublic', 'tag_sn'] as $key) {
            $xoopsTpl->assign($key, Tools::filter($key, $query[$key], 'edit', self::$filter_arr));
        }

        $groups = [];
        foreach (Tools::all_groups() as $gid => $name) {
            $groups[] = [
                'id'       => (int) $gid,
                'name'     => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'selected' => in_array((int) $gid, $query['read_group_arr'], true),
            ];
        }
        $xoopsTpl->assign('all_groups', $groups);
        $xoopsTpl->assign('tags', Jill_query_tags::get_all(true));

        $ck = new CkEditor('jill_query', 'directions', $query['directions']);
        $ck->setHeight(200);
        $xoopsTpl->assign('directions_editor', $ck->render());

        $xoopsTpl->assign('next_op', $qsn ? 'jill_query_update' : 'jill_query_store');
        $formValidator = new FormValidator('#myForm', true);
        $formValidator->render();
        Utility::token_form();
    }

    // 從 POST 組出可寫入欄位（已跳脫）
    private static function post_data(): array
    {
        global $xoopsDB;

        $ispublic = Request::getInt('ispublic', 0, 'POST');
        $ispublic = in_array($ispublic, [0, 1, 2], true) ? $ispublic : 0;

        $all_groups = array_map('intval', array_keys(Tools::all_groups()));
        $read_group = array_values(array_intersect(array_map('intval', Request::getArray('read_group', [], 'POST')), $all_groups));
        if (2 === $ispublic) {
            // 以登入 Email 查詢時排除訪客
            $read_group = array_values(array_diff($read_group, [XOOPS_GROUP_ANONYMOUS]));
        }
        $read_group = array_map('strval', $read_group);

        return [
            'title'       => Tools::filter('title', Request::getText('title', '', 'POST'), 'write', self::$filter_arr),
            'directions'  => Tools::filter('directions', Request::getText('directions', '', 'POST'), 'write', self::$filter_arr),
            'editorEmail' => $xoopsDB->escape(implode(';', Tools::email_list(Request::getText('editorEmail', '', 'POST')))),
            'isEnable'    => Request::getInt('isEnable', 0, 'POST') ? 1 : 0,
            'passwd'      => Tools::filter('passwd', Request::getText('passwd', '', 'POST'), 'write', self::$filter_arr),
            'ispublic'    => $ispublic,
            'read_group'  => $xoopsDB->escape(json_encode($read_group)),
            'tag_sn'      => Request::getInt('tag_sn', 0, 'POST'),
        ];
    }

    //新增事件
    public static function store(): int
    {
        global $xoopsDB;
        Tools::chk_admin();

        $d   = self::post_data();
        $uid = Tools::uid();
        $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_query') . "`
            (`title`, `directions`, `editorEmail`, `isEnable`, `counter`, `uid`, `passwd`, `ispublic`, `read_group`, `tag_sn`)
            VALUES ('{$d['title']}', '{$d['directions']}', '{$d['editorEmail']}', '{$d['isEnable']}', '0', '{$uid}', '{$d['passwd']}', '{$d['ispublic']}', '{$d['read_group']}', '{$d['tag_sn']}')";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $qsn = (int) $xoopsDB->getInsertId();

        if (2 === $d['ispublic']) {
            Jill_query_col::ensure_email_col($qsn);
        }
        return $qsn;
    }

    //更新事件
    public static function update($qsn): int
    {
        global $xoopsDB;
        Tools::chk_admin();

        $qsn = (int) $qsn;
        if (empty(self::get($qsn))) {
            redirect_header(XOOPS_URL . '/modules/jill_query/admin/main.php', 3, _MD_JILLQUERY_EMPTYQSN);
        }
        $d   = self::post_data();
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query') . "` SET
            `title` = '{$d['title']}',
            `directions` = '{$d['directions']}',
            `editorEmail` = '{$d['editorEmail']}',
            `isEnable` = '{$d['isEnable']}',
            `passwd` = '{$d['passwd']}',
            `ispublic` = '{$d['ispublic']}',
            `read_group` = '{$d['read_group']}',
            `tag_sn` = '{$d['tag_sn']}'
            WHERE `qsn` = '{$qsn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        if (2 === $d['ispublic']) {
            Jill_query_col::ensure_email_col($qsn);
        }
        return $qsn;
    }

    //刪除事件（連同欄位與所有資料列）
    public static function destroy($qsn)
    {
        global $xoopsDB;
        Tools::chk_admin();

        $qsn = (int) $qsn;
        if (empty($qsn)) {
            return;
        }
        Jill_query_data::delete_rows($qsn);
        foreach (['jill_query_col', 'jill_query'] as $table) {
            $sql = 'DELETE FROM `' . $xoopsDB->prefix($table) . "` WHERE `qsn` = '{$qsn}'";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        }
    }

    //複製事件（只複製設定與欄位，不複製資料）
    public static function copy($qsn): int
    {
        global $xoopsDB;
        Tools::chk_admin();

        $src = self::get($qsn);
        if (empty($src)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/admin/main.php', 3, _MD_JILLQUERY_EMPTYQSN);
        }
        $title      = $xoopsDB->escape('copy_' . $src['title']);
        $directions = $xoopsDB->escape($src['directions']);
        $editor     = $xoopsDB->escape($src['editorEmail']);
        $passwd     = $xoopsDB->escape($src['passwd']);
        $read_group = $xoopsDB->escape($src['read_group']);
        $ispublic   = (int) $src['ispublic'];
        $tag_sn     = (int) $src['tag_sn'];
        $uid        = Tools::uid();

        $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_query') . "`
            (`title`, `directions`, `editorEmail`, `isEnable`, `counter`, `uid`, `passwd`, `ispublic`, `read_group`, `tag_sn`)
            VALUES ('{$title}', '{$directions}', '{$editor}', '1', '0', '{$uid}', '{$passwd}', '{$ispublic}', '{$read_group}', '{$tag_sn}')";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $new_qsn = (int) $xoopsDB->getInsertId();

        $col_table = $xoopsDB->prefix('jill_query_col');
        $sql = "INSERT INTO `{$col_table}` (`qsn`, `qc_title`, `qcsnSearch`, `search_operator`, `isShow`, `qcSort`, `isLike`)
            SELECT '{$new_qsn}', `qc_title`, `qcsnSearch`, `search_operator`, `isShow`, `qcSort`, `isLike` FROM `{$col_table}` WHERE `qsn` = '" . (int) $qsn . "' ORDER BY `qcSort`";
        if (!$xoopsDB->queryF($sql)) {
            // MyISAM 無交易：欄位複製失敗就刪掉剛建的事件
            $del = 'DELETE FROM `' . $xoopsDB->prefix('jill_query') . "` WHERE `qsn` = '{$new_qsn}'";
            $xoopsDB->queryF($del) or Utility::web_error($del, __FILE__, __LINE__);
            Utility::web_error($sql, __FILE__, __LINE__);
        }
        return $new_qsn;
    }

    //瀏覽數 +1
    public static function add_counter($qsn)
    {
        global $xoopsDB;
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query') . "` SET `counter` = `counter` + 1 WHERE `qsn` = '" . (int) $qsn . "'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //事件選單 [qsn => title]，$public 為 true 時只列公開事件
    public static function menu_options(bool $public = false): array
    {
        global $xoopsDB;
        $and = $public ? " AND `ispublic`='1'" : '';
        $sql = 'SELECT `qsn`, `title` FROM `' . $xoopsDB->prefix('jill_query') . "` WHERE `isEnable`='1' {$and} ORDER BY `qsn` DESC";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $options = [];
        while (list($qsn, $title) = $xoopsDB->fetchRow($result)) {
            $options[(int) $qsn] = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        }
        return $options;
    }

    //給樣板用的事件資料（已過濾）
    public static function for_tpl(array $query): array
    {
        $myts = \MyTextSanitizer::getInstance();
        return [
            'qsn'        => (int) $query['qsn'],
            'title'      => htmlspecialchars($query['title'], ENT_QUOTES, 'UTF-8'),
            'directions' => $myts->displayTarea($query['directions'], 1, 1, 1, 1, 0),
            'ispublic'   => (int) $query['ispublic'],
            'isEnable'   => (int) $query['isEnable'],
            'has_passwd' => '' !== (string) $query['passwd'],
        ];
    }
}
