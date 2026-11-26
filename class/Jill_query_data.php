<?php
namespace XoopsModules\Jill_query;

use Xmf\Request;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Jeditable;
use XoopsModules\Tadtools\SweetAlert2;
use XoopsModules\Tadtools\Utility;

/**
 * 資料列（jill_query_sn）與儲存格（jill_query_col_value）
 */
class Jill_query_data
{
    //查詢表單需要的資料（前台頁面、區塊、後台預覽共用）
    public static function form_data(array $query): array
    {
        $cols = Jill_query_col::for_tpl(Jill_query_col::search_cols($query['qsn']), $query);
        $op   = Jill_query_col::operator($query['qsn']);
        foreach ($cols as $k => $col) {
            $cols[$k]['required'] = ('and' === $op);
        }
        return [
            'query' => Jill_query::for_tpl($query),
            'cols'  => $cols,
            'email' => htmlspecialchars(Tools::email(), ENT_QUOTES, 'UTF-8'),
        ];
    }

    //前台：顯示查詢表單
    public static function search_form($qsn)
    {
        global $xoopsTpl;
        $query = Jill_query::get_readable($qsn);
        $form  = self::form_data($query);
        if (empty($form['cols'])) {
            $xoopsTpl->assign('no_search_col', true);
        }
        Jill_query::add_counter($qsn);

        $xoopsTpl->assign('form', $form);
        $xoopsTpl->assign('query', $form['query']);
        $xoopsTpl->assign('undertaker', Tools::is_undertaker($query));
        $formValidator = new FormValidator('#myForm', true);
        $formValidator->render();
    }

    //前台：送出查詢
    public static function show_result($qsn)
    {
        global $xoopsDB, $xoopsTpl;
        $query = Jill_query::get_readable($qsn);
        $qsn   = (int) $query['qsn'];
        $back  = XOOPS_URL . "/modules/jill_query/index.php?qsn={$qsn}";

        if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')) {
            header("Location: {$back}");
            exit;
        }

        // 密碼一律比對（不送密碼欄位也擋）
        if ('' !== (string) $query['passwd'] && !hash_equals((string) $query['passwd'], Request::getText('passwd', '', 'POST'))) {
            redirect_header($back, 3, _MD_JILLQUERY_PASSWDERROR);
        }

        $search_cols = Jill_query_col::search_cols($qsn);
        $operator    = Jill_query_col::operator($qsn);
        $input       = (array) Request::getVar('fillValue', [], 'POST', 'array', Request::MASK_ALLOW_RAW);

        $sets = [];
        foreach ($search_cols as $qcsn => $col) {
            if (Jill_query_col::is_email_col($query, $col)) {
                // 以登入 Email 查詢：一律用伺服器端的登入 email，忽略表單送來的值
                $value = Tools::email();
                if ('' === $value) {
                    redirect_header(XOOPS_URL . '/user.php', 3, _MD_JILLQUERY_NEED_LOGIN);
                }
            } else {
                $value = trim((string) ($input[$qcsn] ?? ''));
            }

            if ('' === $value) {
                if ('and' === $operator) {
                    redirect_header($back, 3, _MD_JILLQUERY_EMPTYVALUE);
                }
                continue;
            }

            $v   = $xoopsDB->escape($value);
            $cmp = empty($col['isLike']) ? "a.`fillValue` = '{$v}'" : "a.`fillValue` LIKE '%" . addcslashes($v, '%_') . "%'";
            $sql = 'SELECT a.`ssn` FROM `' . $xoopsDB->prefix('jill_query_col_value') . '` AS a
                JOIN `' . $xoopsDB->prefix('jill_query_sn') . "` AS b ON a.`ssn` = b.`ssn`
                WHERE b.`qsn` = '{$qsn}' AND a.`qcsn` = '" . (int) $qcsn . "' AND {$cmp}";
            $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            $ssns = [];
            while (list($ssn) = $xoopsDB->fetchRow($result)) {
                $ssns[] = (int) $ssn;
            }
            $sets[] = $ssns;
        }

        if (empty($sets)) {
            $ssn_arr = [];
        } elseif ('and' === $operator) {
            $ssn_arr = count($sets) > 1 ? array_intersect(...$sets) : $sets[0];
        } else {
            $ssn_arr = array_unique(array_merge(...$sets));
        }

        $show_cols = Jill_query_col::show_cols($qsn);
        $xoopsTpl->assign('show_col', Jill_query_col::for_tpl($show_cols));
        $result = self::rows_values(array_values($ssn_arr), array_keys($show_cols));
        $xoopsTpl->assign('result', $result);
        $xoopsTpl->assign('result_total', sprintf(_MD_JILLQUERY_TOTAL, count($result)));
        $xoopsTpl->assign('show_result', true);
        self::search_form($qsn);
    }

    //前台：公開事件的全部資料
    public static function public_query($qsn)
    {
        global $xoopsDB, $xoopsTpl;
        $query = Jill_query::get_readable($qsn);
        if (1 !== (int) $query['ispublic']) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 3, _MD_JILLQUERY_PUBLICERROR);
        }
        $qsn = (int) $query['qsn'];

        $sql     = 'SELECT `ssn` FROM `' . $xoopsDB->prefix('jill_query_sn') . "` WHERE `qsn` = '{$qsn}' ORDER BY `qrSort`, `ssn`";
        $PageBar = Utility::getPageBar($sql, 20, 10, '', "op=public_query&qsn={$qsn}");
        $result  = $xoopsDB->query($PageBar['sql']) or Utility::web_error($PageBar['sql'], __FILE__, __LINE__);
        $ssns    = [];
        while (list($ssn) = $xoopsDB->fetchRow($result)) {
            $ssns[] = (int) $ssn;
        }

        $show_cols = Jill_query_col::show_cols($qsn);
        $xoopsTpl->assign('query', Jill_query::for_tpl($query));
        $xoopsTpl->assign('show_col', Jill_query_col::for_tpl($show_cols));
        $xoopsTpl->assign('result', self::rows_values($ssns, array_keys($show_cols)));
        $xoopsTpl->assign('public_options', Jill_query::menu_options(true));
        $xoopsTpl->assign('bar', $PageBar['bar']);
    }

    //公開資料區塊用：前 N 筆
    public static function public_rows(array $query, int $limit): array
    {
        global $xoopsDB;
        $qsn = (int) $query['qsn'];
        $sql = 'SELECT `ssn` FROM `' . $xoopsDB->prefix('jill_query_sn') . "` WHERE `qsn` = '{$qsn}' ORDER BY `qrSort`, `ssn` LIMIT 0, {$limit}";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $ssns = [];
        while (list($ssn) = $xoopsDB->fetchRow($result)) {
            $ssns[] = (int) $ssn;
        }
        $show_cols = Jill_query_col::show_cols($qsn);
        return [
            'show_col' => Jill_query_col::for_tpl($show_cols),
            'result'   => self::rows_values($ssns, array_keys($show_cols)),
        ];
    }

    /**
     * 取多列的儲存格（一次查詢），回傳 [['ssn'=>, 'qrSort'=>, 'cells'=>[qcsn=>顯示值]] ...]，依 qrSort 排序
     */
    public static function rows_values(array $ssns, array $qcsns, bool $raw = false): array
    {
        global $xoopsDB;
        $ssns  = array_values(array_unique(array_map('intval', $ssns)));
        $qcsns = array_values(array_map('intval', $qcsns));
        if (empty($ssns) || empty($qcsns)) {
            return [];
        }
        $in_ssn = implode(',', $ssns);

        $rows = [];
        $sql  = 'SELECT `ssn`, `qrSort` FROM `' . $xoopsDB->prefix('jill_query_sn') . "` WHERE `ssn` IN ({$in_ssn}) ORDER BY `qrSort`, `ssn`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        while (list($ssn, $qrSort) = $xoopsDB->fetchRow($result)) {
            $rows[(int) $ssn] = ['ssn' => (int) $ssn, 'qrSort' => (int) $qrSort, 'cells' => array_fill_keys($qcsns, '')];
        }

        $sql = 'SELECT `ssn`, `qcsn`, `fillValue` FROM `' . $xoopsDB->prefix('jill_query_col_value') . "` WHERE `ssn` IN ({$in_ssn}) AND `qcsn` IN (" . implode(',', $qcsns) . ')';
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        while (list($ssn, $qcsn, $value) = $xoopsDB->fetchRow($result)) {
            if (isset($rows[(int) $ssn])) {
                $rows[(int) $ssn]['cells'][(int) $qcsn] = $raw ? (string) $value : Tools::show_value($value);
            }
        }
        return array_values($rows);
    }

    // 承辦人可管理的資料列條件：管理員看全部，其他承辦人只看自己匯入的
    private static function owner_where(string $prefix = ''): string
    {
        return Tools::isAdmin() ? '' : " AND {$prefix}`uid` = '" . Tools::uid() . "'";
    }

    //承辦人：資料管理頁
    public static function manage($qsn)
    {
        global $xoopsDB, $xoopsTpl;
        $query = Jill_query::get($qsn);
        Tools::chk_undertaker($query);
        $qsn = (int) $query['qsn'];

        $cols    = Jill_query_col::get_all($qsn);
        $keyword = Request::getText('keyword');
        $kw_qcsn = Request::getInt('kw_qcsn');
        $sn_tb   = $xoopsDB->prefix('jill_query_sn');

        if ('' !== $keyword && isset($cols[$kw_qcsn])) {
            $kw  = addcslashes($xoopsDB->escape($keyword), '%_');
            $sql = 'SELECT DISTINCT b.`ssn`, b.`qrSort` FROM `' . $xoopsDB->prefix('jill_query_col_value') . "` AS a
                JOIN `{$sn_tb}` AS b ON a.`ssn` = b.`ssn`
                WHERE b.`qsn` = '{$qsn}' AND a.`qcsn` = '{$kw_qcsn}' AND a.`fillValue` LIKE '%{$kw}%' " . self::owner_where('b.') . ' ORDER BY b.`qrSort`';
            $url_other = 'qsn=' . $qsn . '&kw_qcsn=' . $kw_qcsn . '&keyword=' . rawurlencode($keyword);
        } else {
            $sql       = "SELECT `ssn`, `qrSort` FROM `{$sn_tb}` WHERE `qsn` = '{$qsn}' " . self::owner_where() . ' ORDER BY `qrSort`, `ssn`';
            $url_other = "qsn={$qsn}";
            $keyword   = '';
        }
        $PageBar = Utility::getPageBar($sql, 20, 10, '', $url_other);
        $result  = $xoopsDB->query($PageBar['sql']) or Utility::web_error($PageBar['sql'], __FILE__, __LINE__);
        $ssns    = [];
        while (list($ssn) = $xoopsDB->fetchRow($result)) {
            $ssns[] = (int) $ssn;
        }

        $token = Tools::token();
        $url   = XOOPS_URL . '/modules/jill_query/excel.php';
        $xoopsTpl->assign('csrf_token', $token);
        $xoopsTpl->assign('query', Jill_query::for_tpl($query));
        $xoopsTpl->assign('all_col', Jill_query_col::for_tpl($cols));
        $xoopsTpl->assign('result', self::rows_values($ssns, array_keys($cols), true));
        $xoopsTpl->assign('total', (int) $PageBar['total']);
        $xoopsTpl->assign('bar', $PageBar['bar']);
        $xoopsTpl->assign('keyword', htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'));
        $xoopsTpl->assign('kw_qcsn', $kw_qcsn);

        $SweetAlert = new SweetAlert2();
        $SweetAlert->setVar('method', 'post');
        $SweetAlert->render('jill_query_row_destroy_func', "{$url}?op=row_destroy&qsn={$qsn}&XOOPS_TOKEN_REQUEST={$token}&ssn=", 'ssn');

        $jeditable = new Jeditable();
        $jeditable->setTextCol('.jq_data', $url, '80%', '1.5em', "{'op':'save_cell','qsn':{$qsn},'XOOPS_TOKEN_REQUEST':'{$token}'}");
        $jeditable->render();

        Utility::token_form();
    }

    //承辦人：依既有欄位匯入（第 1 列表頭略過，依欄位排序對應）
    public static function import($qsn)
    {
        global $xoopsDB;
        $query = Jill_query::get($qsn);
        Tools::chk_undertaker($query);
        $qsn  = (int) $query['qsn'];
        $back = XOOPS_URL . "/modules/jill_query/excel.php?qsn={$qsn}";

        $rows = Tools::read_excel($back);
        if (count($rows) < 2) {
            redirect_header($back, 3, _MD_JILLQUERY_NODATA);
        }
        $qcsns = array_keys(Jill_query_col::get_all($qsn));
        if (empty($qcsns)) {
            redirect_header($back, 3, _MD_JILLQUERY_NOCOL);
        }

        if ('on' !== Request::getString('continue_no', '', 'POST')) {
            self::delete_rows($qsn, Tools::uid());
        }
        $start = self::max_sort($qsn);
        array_shift($rows);
        self::insert_rows($qsn, $qcsns, $rows, $start);
    }

    //後台：上傳 Excel 直接建立事件（第 1 列為欄位名稱；* 開頭為搜尋欄，! 開頭為登入 Email 欄）
    public static function import_new(): int
    {
        global $xoopsDB;
        Tools::chk_admin();
        $back = XOOPS_URL . '/modules/jill_query/admin/import.php';

        $rows = Tools::read_excel($back);
        if (count($rows) < 2) {
            redirect_header($back, 3, _MD_JILLQUERY_NODATA);
        }
        $header = array_shift($rows);
        // 去掉表頭尾端的空欄
        while ($header && '' === end($header)) {
            array_pop($header);
        }
        if (empty($header)) {
            redirect_header($back, 3, _MD_JILLQUERY_NOCOL);
        }

        $title  = $xoopsDB->escape(preg_replace('/\.(xlsx|xls)$/i', '', basename((string) $_FILES['excel']['name']))); // xoops-lint-ignore 只取上傳檔名當事件名稱
        $email  = $xoopsDB->escape(Tools::email());
        $uid    = Tools::uid();
        $groups = $xoopsDB->escape(json_encode([(string) XOOPS_GROUP_USERS, (string) XOOPS_GROUP_ANONYMOUS]));
        $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_query') . "`
            (`title`, `directions`, `editorEmail`, `isEnable`, `counter`, `uid`, `passwd`, `ispublic`, `read_group`, `tag_sn`)
            VALUES ('{$title}', '', '{$email}', '1', '0', '{$uid}', '', '0', '{$groups}', '0')";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $qsn = (int) $xoopsDB->getInsertId();

        $qcsns = [];
        $sort  = 1;
        foreach ($header as $name) {
            $name = trim((string) $name);
            if (str_starts_with($name, '*')) {
                [$name, $search, $operator] = [substr($name, 1), 1, 'or'];
            } elseif (str_starts_with($name, '!')) {
                [$name, $search, $operator] = ['email', 1, 'and'];
            } else {
                [$search, $operator] = [0, 'or'];
            }
            $name = $xoopsDB->escape($name);
            $sql  = 'INSERT INTO `' . $xoopsDB->prefix('jill_query_col') . "`
                (`qsn`, `qc_title`, `qcsnSearch`, `search_operator`, `isShow`, `qcSort`, `isLike`)
                VALUES ('{$qsn}', '{$name}', '{$search}', '{$operator}', '1', '{$sort}', '0')";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            $qcsns[] = (int) $xoopsDB->getInsertId();
            $sort++;
        }

        self::insert_rows($qsn, $qcsns, $rows, 1);
        return $qsn;
    }

    // 寫入資料列；$qcsns[i] 對應每列第 i+1 欄；整列空白略過
    private static function insert_rows(int $qsn, array $qcsns, array $rows, int $start)
    {
        global $xoopsDB;
        $uid   = Tools::uid();
        $now   = date('Y-m-d H:i:s', xoops_getUserTimestamp(time()));
        $sort  = $start;
        $sn_tb = $xoopsDB->prefix('jill_query_sn');
        $vl_tb = $xoopsDB->prefix('jill_query_col_value');

        foreach ($rows as $cells) {
            $cells = array_values($cells);
            if ('' === implode('', array_slice($cells, 0, count($qcsns)))) {
                continue;
            }
            $sql = "INSERT INTO `{$sn_tb}` (`qsn`, `createDate`, `qrSort`, `uid`) VALUES ('{$qsn}', '{$now}', '{$sort}', '{$uid}')";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            $ssn = (int) $xoopsDB->getInsertId();

            $values = [];
            foreach ($qcsns as $i => $qcsn) {
                $values[] = "('{$ssn}', '{$qcsn}', '" . $xoopsDB->escape((string) ($cells[$i] ?? '')) . "')";
            }
            $sql = "INSERT INTO `{$vl_tb}` (`ssn`, `qcsn`, `fillValue`) VALUES " . implode(',', $values);
            if (!$xoopsDB->queryF($sql)) {
                // MyISAM 無交易：儲存格寫入失敗就刪掉剛建立的資料列
                $del = "DELETE FROM `{$sn_tb}` WHERE `ssn` = '{$ssn}'";
                $xoopsDB->queryF($del) or Utility::web_error($del, __FILE__, __LINE__);
                Utility::web_error($sql, __FILE__, __LINE__);
            }
            $sort++;
        }
    }

    //刪除事件的資料列；$uid 有值時只刪該使用者匯入的
    public static function delete_rows(int $qsn, int $uid = 0)
    {
        global $xoopsDB;
        $sn_tb   = $xoopsDB->prefix('jill_query_sn');
        $and_uid = $uid ? " AND `uid` = '{$uid}'" : '';

        $sql = 'DELETE v FROM `' . $xoopsDB->prefix('jill_query_col_value') . "` AS v JOIN `{$sn_tb}` AS s ON v.`ssn` = s.`ssn` WHERE s.`qsn` = '{$qsn}'" . ($uid ? " AND s.`uid` = '{$uid}'" : '');
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $sql = "DELETE FROM `{$sn_tb}` WHERE `qsn` = '{$qsn}' {$and_uid}";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        if ($uid) {
            self::resort($qsn);
        }
    }

    // 承辦人可操作的資料列（屬於該事件，且管理員或本人匯入）
    private static function own_row(int $qsn, int $ssn): array
    {
        global $xoopsDB;
        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_query_sn') . "` WHERE `ssn` = '{$ssn}' AND `qsn` = '{$qsn}' " . self::owner_where();
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        return $xoopsDB->fetchArray($result) ?: [];
    }

    //承辦人：刪除單列並重排
    public static function destroy_row($qsn, $ssn)
    {
        global $xoopsDB;
        $query = Jill_query::get($qsn);
        Tools::chk_undertaker($query);
        $qsn = (int) $query['qsn'];

        $row = self::own_row($qsn, (int) $ssn);
        if (empty($row)) {
            redirect_header(XOOPS_URL . "/modules/jill_query/excel.php?qsn={$qsn}", 3, _MD_JILLQUERY_ILLEGAL);
        }
        $ssn = (int) $row['ssn'];
        foreach (['jill_query_col_value', 'jill_query_sn'] as $table) {
            $sql = 'DELETE FROM `' . $xoopsDB->prefix($table) . "` WHERE `ssn` = '{$ssn}'";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        }
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_sn') . "` SET `qrSort` = `qrSort` - 1 WHERE `qsn` = '{$qsn}' AND `qrSort` > '" . (int) $row['qrSort'] . "'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //承辦人 AJAX：修改儲存格（id 格式 ssn-qcsn），回傳顯示值
    public static function save_cell($qsn, string $id, string $value): string
    {
        global $xoopsDB;
        $query = Jill_query::get($qsn);
        if (!Tools::is_undertaker($query)) {
            return '';
        }
        $qsn = (int) $query['qsn'];
        [$ssn, $qcsn] = array_map('intval', explode('-', $id . '-'));
        $cols = Jill_query_col::get_all($qsn);
        if (empty(self::own_row($qsn, $ssn)) || !isset($cols[$qcsn])) {
            return '';
        }
        $v   = $xoopsDB->escape($value);
        $sql = 'REPLACE INTO `' . $xoopsDB->prefix('jill_query_col_value') . "` (`ssn`, `qcsn`, `fillValue`) VALUES ('{$ssn}', '{$qcsn}', '{$v}')";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    //匯出事件全部資料（管理員）
    public static function export($qsn)
    {
        Tools::chk_admin();
        $query = Jill_query::get($qsn);
        if (empty($query)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/admin/main.php', 3, _MD_JILLQUERY_EMPTYQSN);
        }
        global $xoopsDB;
        $qsn  = (int) $qsn;
        $cols = Jill_query_col::get_all($qsn);

        $sql = 'SELECT `ssn` FROM `' . $xoopsDB->prefix('jill_query_sn') . "` WHERE `qsn` = '{$qsn}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $ssns = [];
        while (list($ssn) = $xoopsDB->fetchRow($result)) {
            $ssns[] = (int) $ssn;
        }

        $out = [array_column($cols, 'qc_title')];
        foreach (self::rows_values($ssns, array_keys($cols), true) as $row) {
            $out[] = $row['cells'];
        }
        Tools::output_excel($query['title'], 'datas', $out);
    }

    //下載空白範本（承辦人）
    public static function sample($qsn)
    {
        $query = Jill_query::get($qsn);
        Tools::chk_undertaker($query);
        Tools::output_excel($query['title'], 'sample', [array_column(Jill_query_col::get_all($qsn), 'qc_title')]);
    }

    //下載會員姓名／Email（僅「以登入 Email 查詢」事件的承辦人）
    public static function users($qsn)
    {
        global $xoopsDB;
        $query = Jill_query::get($qsn);
        Tools::chk_undertaker($query);
        if (2 !== (int) $query['ispublic']) {
            redirect_header(XOOPS_URL . "/modules/jill_query/excel.php?qsn={$qsn}", 3, _MD_JILLQUERY_ILLEGAL);
        }
        $sql = 'SELECT `name`, `uname`, `email` FROM `' . $xoopsDB->prefix('users') . "` WHERE `level` > 0 ORDER BY `uid`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $out = [[_MD_JILLQUERY_USER_NAME, 'email']];
        while (list($name, $uname, $email) = $xoopsDB->fetchRow($result)) {
            $out[] = ['' !== $name ? $name : $uname, $email];
        }
        Tools::output_excel('users', 'users', $out);
    }

    // 重排 qrSort（刪除部分資料後保持 1..N 連續）
    private static function resort(int $qsn)
    {
        global $xoopsDB;
        $sn_tb = $xoopsDB->prefix('jill_query_sn');
        $sql   = "SELECT `ssn` FROM `{$sn_tb}` WHERE `qsn` = '{$qsn}' ORDER BY `qrSort`, `ssn`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $i = 1;
        while (list($ssn) = $xoopsDB->fetchRow($result)) {
            $up = "UPDATE `{$sn_tb}` SET `qrSort` = '{$i}' WHERE `ssn` = '" . (int) $ssn . "'";
            $xoopsDB->queryF($up) or Utility::web_error($up, __FILE__, __LINE__);
            $i++;
        }
    }

    public static function max_sort(int $qsn): int
    {
        global $xoopsDB;
        $sql = 'SELECT MAX(`qrSort`) FROM `' . $xoopsDB->prefix('jill_query_sn') . "` WHERE `qsn` = '{$qsn}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        list($sort) = $xoopsDB->fetchRow($result);
        return (int) $sort + 1;
    }
}
