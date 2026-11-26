<?php
namespace XoopsModules\Jill_query;

use Xmf\Request;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Jeditable;
use XoopsModules\Tadtools\SweetAlert2;
use XoopsModules\Tadtools\Utility;

/**
 * 事件欄位（jill_query_col）
 */
class Jill_query_col
{
    // 可用 AJAX 切換的旗標欄位
    const TOGGLES = ['qcsnSearch', 'isShow', 'isLike'];

    //取得某事件全部欄位 [qcsn => row]（原始值）
    public static function get_all($qsn, string $where = ''): array
    {
        global $xoopsDB;
        $qsn = (int) $qsn;
        if (empty($qsn)) {
            return [];
        }
        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_query_col') . "` WHERE `qsn` = '{$qsn}' {$where} ORDER BY `qcSort`, `qcsn`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $cols = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $cols[(int) $data['qcsn']] = $data;
        }
        return $cols;
    }

    // 搜尋欄位
    public static function search_cols($qsn): array
    {
        return self::get_all($qsn, "AND `qcsnSearch` = '1'");
    }

    // 顯示欄位
    public static function show_cols($qsn): array
    {
        return self::get_all($qsn, "AND `isShow` = '1'");
    }

    public static function get($qcsn): array
    {
        global $xoopsDB;
        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_query_col') . "` WHERE `qcsn` = '" . (int) $qcsn . "'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        return $xoopsDB->fetchArray($result) ?: [];
    }

    //事件的搜尋運算子（各搜尋欄共用同一個值）
    public static function operator($qsn): string
    {
        foreach (self::search_cols($qsn) as $col) {
            return ('and' === $col['search_operator']) ? 'and' : 'or';
        }
        return 'or';
    }

    // 是否為「以登入 Email 查詢」的鎖定欄位
    public static function is_email_col(array $query, array $col): bool
    {
        return 2 === (int) $query['ispublic'] && 'email' === $col['qc_title'];
    }

    // 樣板用欄位清單（標題已跳脫）
    public static function for_tpl(array $cols, array $query = []): array
    {
        $out = [];
        foreach ($cols as $qcsn => $col) {
            $out[] = [
                'qcsn'       => (int) $qcsn,
                'qc_title'   => htmlspecialchars($col['qc_title'], ENT_QUOTES, 'UTF-8'),
                'qcsnSearch' => (int) $col['qcsnSearch'],
                'isShow'     => (int) $col['isShow'],
                'isLike'     => (int) $col['isLike'],
                'is_email'   => $query ? self::is_email_col($query, $col) : false,
            ];
        }
        return $out;
    }

    //後台：欄位設定頁
    public static function index($qsn)
    {
        global $xoopsDB, $xoopsTpl;
        Tools::chk_admin();

        $query = Jill_query::get($qsn);
        if (empty($query)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/admin/main.php', 3, _MA_JILLQUERY_EMPTYQSN);
        }
        $qsn  = (int) $qsn;
        $cols = self::get_all($qsn);

        // 已有資料的欄位不可刪
        $used = [];
        if ($cols) {
            $sql = 'SELECT DISTINCT `qcsn` FROM `' . $xoopsDB->prefix('jill_query_col_value') . '` WHERE `qcsn` IN (' . implode(',', array_keys($cols)) . ')';
            $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            while (list($qcsn) = $xoopsDB->fetchRow($result)) {
                $used[(int) $qcsn] = true;
            }
        }
        $list = self::for_tpl($cols, $query);
        foreach ($list as $k => $col) {
            $list[$k]['can_del'] = empty($used[$col['qcsn']]) && !$col['is_email'];
        }

        $token = Tools::token();
        $url   = XOOPS_URL . '/modules/jill_query/admin/setcol.php';
        $xoopsTpl->assign('csrf_token', $token);
        $xoopsTpl->assign('query', Jill_query::for_tpl($query));
        $xoopsTpl->assign('all_col', $list);
        $xoopsTpl->assign('operator', self::operator($qsn));
        $xoopsTpl->assign('search_count', count(self::search_cols($qsn)));
        $xoopsTpl->assign('search_col', self::for_tpl(self::search_cols($qsn), $query));

        $SweetAlert = new SweetAlert2();
        $SweetAlert->setVar('method', 'post');
        $SweetAlert->render('jill_query_col_destroy_func', "{$url}?op=col_destroy&qsn={$qsn}&XOOPS_TOKEN_REQUEST={$token}&qcsn=", 'qcsn');

        $jeditable = new Jeditable();
        $jeditable->setTextCol('.qc_title', $url, '80%', '1.5em', "{'op':'col_save_title','qsn':{$qsn},'XOOPS_TOKEN_REQUEST':'{$token}'}", _TAD_EDIT . _MA_JILLQUERY_QC_TITLE);
        $jeditable->render();
        Utility::get_jquery(true);

        $formValidator = new FormValidator('#myForm', true);
        $formValidator->render();
        Utility::token_form();
    }

    //新增欄位
    public static function store($qsn)
    {
        global $xoopsDB;
        Tools::chk_admin();
        $qsn = (int) $qsn;
        if (empty(Jill_query::get($qsn))) {
            redirect_header(XOOPS_URL . '/modules/jill_query/admin/main.php', 3, _MA_JILLQUERY_EMPTYQSN);
        }

        $title = $xoopsDB->escape(Request::getText('qc_title', '', 'POST'));
        if ('' === $title) {
            return;
        }
        $search   = Request::getInt('qcsnSearch', 0, 'POST') ? 1 : 0;
        $show     = Request::getInt('isShow', 0, 'POST') ? 1 : 0;
        $like     = ($search && Request::getInt('isLike', 0, 'POST')) ? 1 : 0;
        $operator = self::operator($qsn);
        $sort     = self::max_sort($qsn);

        $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_query_col') . "`
            (`qsn`, `qc_title`, `qcsnSearch`, `search_operator`, `isShow`, `qcSort`, `isLike`)
            VALUES ('{$qsn}', '{$title}', '{$search}', '{$operator}', '{$show}', '{$sort}', '{$like}')";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //刪除欄位（已有資料或 Email 鎖定欄不可刪）
    public static function destroy($qsn, $qcsn)
    {
        global $xoopsDB;
        Tools::chk_admin();
        $qsn  = (int) $qsn;
        $qcsn = (int) $qcsn;

        $col   = self::get($qcsn);
        $query = Jill_query::get($qsn);
        if (empty($col) || (int) $col['qsn'] !== $qsn || self::is_email_col($query, $col)) {
            return;
        }
        $sql = 'SELECT COUNT(*) FROM `' . $xoopsDB->prefix('jill_query_col_value') . "` WHERE `qcsn` = '{$qcsn}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        list($count) = $xoopsDB->fetchRow($result);
        if ($count > 0) {
            return;
        }
        $sql = 'DELETE FROM `' . $xoopsDB->prefix('jill_query_col') . "` WHERE `qcsn` = '{$qcsn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //AJAX：切換旗標，回傳新值（0/1），失敗回傳 null
    public static function toggle($qsn, $qcsn, string $name): ?int
    {
        global $xoopsDB;
        Tools::chk_admin();
        if (!in_array($name, self::TOGGLES, true)) {
            return null;
        }
        $col   = self::get($qcsn);
        $query = Jill_query::get($qsn);
        if (empty($col) || (int) $col['qsn'] !== (int) $qsn || self::is_email_col($query, $col)) {
            return null;
        }
        // 關鍵字查詢只對搜尋欄有意義
        if ('isLike' === $name && empty($col['qcsnSearch'])) {
            return null;
        }

        $value = empty($col[$name]) ? 1 : 0;
        $extra = '';
        if ('qcsnSearch' === $name) {
            $extra = (1 === $value) ? ", `search_operator` = '" . self::operator($qsn) . "'" : ", `isLike` = '0'";
        }
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_col') . "` SET `{$name}` = '{$value}' {$extra} WHERE `qcsn` = '" . (int) $qcsn . "'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        return $value;
    }

    //AJAX：修改欄位名稱，回傳顯示用名稱
    public static function save_title($qsn, string $id, string $value): string
    {
        global $xoopsDB;
        Tools::chk_admin();
        $qcsn  = (int) substr($id, strrpos($id, ':') + 1);
        $col   = self::get($qcsn);
        $query = Jill_query::get($qsn);
        $value = trim($value);
        if (empty($col) || (int) $col['qsn'] !== (int) $qsn || self::is_email_col($query, $col) || '' === $value) {
            return htmlspecialchars($col['qc_title'] ?? '', ENT_QUOTES, 'UTF-8');
        }
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_col') . "` SET `qc_title` = '" . $xoopsDB->escape($value) . "' WHERE `qcsn` = '{$qcsn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    //AJAX：拖曳排序
    public static function update_sort($qsn): string
    {
        global $xoopsDB;
        Tools::chk_admin();
        $qsn  = (int) $qsn;
        $sort = 1;
        foreach (Request::getArray('tr', [], 'POST') as $qcsn) {
            $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_col') . "` SET `qcSort` = '{$sort}' WHERE `qcsn` = '" . (int) $qcsn . "' AND `qsn` = '{$qsn}'";
            if (!$xoopsDB->queryF($sql)) {
                return _TAD_SORT_FAIL;
            }
            $sort++;
        }
        return _TAD_SORTED . ' (' . date('Y-m-d H:i:s') . ')';
    }

    //設定搜尋運算子
    public static function update_operator($qsn)
    {
        global $xoopsDB;
        Tools::chk_admin();
        $qsn = (int) $qsn;

        $query    = Jill_query::get($qsn);
        $operator = ('or' === Request::getString('search_operator', '', 'POST') && 2 !== (int) ($query['ispublic'] ?? 0)) ? 'or' : 'and';
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_col') . "` SET `search_operator` = '{$operator}' WHERE `qsn` = '{$qsn}' AND `qcsnSearch` = '1'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //「以登入 Email 查詢」需要的 email 欄位：沒有就建立，有就鎖定為搜尋＋顯示，並把運算子改成 and
    public static function ensure_email_col($qsn)
    {
        global $xoopsDB;
        $qsn   = (int) $qsn;
        $table = $xoopsDB->prefix('jill_query_col');

        $sql = "SELECT `qcsn` FROM `{$table}` WHERE `qsn` = '{$qsn}' AND `qc_title` = 'email'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        if ($xoopsDB->getRowsNum($result)) {
            $sql = "UPDATE `{$table}` SET `qcsnSearch` = '1', `isShow` = '1', `isLike` = '0' WHERE `qsn` = '{$qsn}' AND `qc_title` = 'email'";
        } else {
            $sort = self::max_sort($qsn);
            $sql = "INSERT INTO `{$table}` (`qsn`, `qc_title`, `qcsnSearch`, `search_operator`, `isShow`, `qcSort`, `isLike`)
                VALUES ('{$qsn}', 'email', '1', 'and', '1', '{$sort}', '0')";
        }
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        $sql = "UPDATE `{$table}` SET `search_operator` = 'and' WHERE `qsn` = '{$qsn}' AND `qcsnSearch` = '1'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    public static function max_sort($qsn): int
    {
        global $xoopsDB;
        $sql = 'SELECT MAX(`qcSort`) FROM `' . $xoopsDB->prefix('jill_query_col') . "` WHERE `qsn` = '" . (int) $qsn . "'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        list($sort) = $xoopsDB->fetchRow($result);
        return (int) $sort + 1;
    }
}
