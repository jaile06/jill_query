<?php
namespace XoopsModules\Jill_query;

use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\Wcag;

/**
 * 共用工具：過濾、權限、Excel 讀寫
 */
class Tools
{
    // 變數過濾（與 jill_leave 同一套規則）
    public static function filter($key, $value, $mode = 'read', $filter_arr = [])
    {
        global $xoopsDB;
        $myts = \MyTextSanitizer::getInstance();

        if (isset($filter_arr['pass']) && in_array($key, $filter_arr['pass'], true)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::filter($key, $v, $mode, $filter_arr);
            }
            return $value;
        }

        if (isset($filter_arr['int']) && in_array($key, $filter_arr['int'], true)) {
            return (int) $value;
        }

        if (isset($filter_arr['html']) && in_array($key, $filter_arr['html'], true)) {
            if ('edit' === $mode) {
                return $myts->htmlSpecialChars((string) $value);
            }
            return ('write' === $mode) ? $xoopsDB->escape(Wcag::amend(trim((string) $value))) : $myts->displayTarea((string) $value, 1, 1, 1, 1, 0);
        }

        if ('write' === $mode) {
            return $xoopsDB->escape(trim((string) $value));
        }
        return $myts->htmlSpecialChars((string) $value);
    }

    // 過濾整列資料
    public static function filter_all_data($mode, $data, $filter_arr)
    {
        if ($mode) {
            foreach ($data as $key => $value) {
                $data[$key] = self::filter($key, $value, $mode, $filter_arr);
            }
        }
        return $data;
    }

    //每次請求重算管理權限，寫入 session 供樣板判斷
    public static function get_session()
    {
        global $xoopsUser, $xoopsModule;

        $is_adm = false;
        if ($xoopsUser) {
            $module = (is_object($xoopsModule) && 'jill_query' === $xoopsModule->getVar('dirname'))
                ? $xoopsModule
                : xoops_getHandler('module')->getByDirname('jill_query');
            $is_adm = is_object($module) && $xoopsUser->isAdmin((int) $module->getVar('mid'));
        }
        $_SESSION['jill_query_adm'] = $is_adm;
    }

    public static function isAdmin(): bool
    {
        return !empty($_SESSION['jill_query_adm']);
    }

    //只有模組管理員可通過，否則導回
    public static function chk_admin()
    {
        if (!self::isAdmin()) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 3, _TAD_PERMISSION_DENIED);
        }
    }

    // 目前登入者 uid（未登入為 0）
    public static function uid(): int
    {
        global $xoopsUser;
        return $xoopsUser ? (int) $xoopsUser->uid() : 0;
    }

    // 目前登入者 email（未登入為空字串）
    public static function email(): string
    {
        global $xoopsUser;
        return $xoopsUser ? trim((string) $xoopsUser->getVar('email', 'n')) : '';
    }

    // 承辦人 Email 字串 → 陣列（分號、逗號、空白、換行都當分隔）
    public static function email_list($editorEmail): array
    {
        $arr = preg_split('/[;,\s]+/', (string) $editorEmail, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter($arr, static fn($e) => false !== filter_var($e, FILTER_VALIDATE_EMAIL))));
    }

    //是否為該事件承辦人（模組管理員一律是；一般承辦人需事件啟用中）
    public static function is_undertaker(array $query): bool
    {
        if (empty($query)) {
            return false;
        }
        if (self::isAdmin()) {
            return true;
        }
        $email = self::email();
        if ('' === $email || empty($query['isEnable'])) {
            return false;
        }
        return in_array(strtolower($email), array_map('strtolower', self::email_list($query['editorEmail'])), true);
    }

    //承辦人權限守門
    public static function chk_undertaker(array $query)
    {
        if (!self::is_undertaker($query)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 3, _MD_JILLQUERY_ILLEGAL);
        }
    }

    //可讀取群組檢查（read_group 為 JSON 字串或陣列）
    public static function group_perm($read_group): bool
    {
        global $xoopsUser;
        if (self::isAdmin()) {
            return true;
        }
        $groups = is_array($read_group) ? $read_group : json_decode((string) $read_group, true);
        if (!is_array($groups)) {
            return false;
        }
        $groups = array_map('intval', $groups);
        $mine   = $xoopsUser ? array_map('intval', $xoopsUser->getGroups()) : [XOOPS_GROUP_ANONYMOUS];
        return [] !== array_intersect($mine, $groups);
    }

    // 全部群組 [id => name]
    public static function all_groups(): array
    {
        return xoops_getHandler('member')->getGroupList();
    }

    // 寫入類請求守門：拒絕非 POST（token 由入口檔接著用 Utility::xoops_security_check() 驗）
    public static function chk_post($redirect = '')
    {
        if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')) {
            redirect_header($redirect ?: XOOPS_URL . '/modules/jill_query/index.php', 3, _MD_JILLQUERY_ILLEGAL);
        }
    }

    // AJAX 寫入守門：POST＋token（不清除 token，同頁可多次操作），失敗直接結束
    public static function chk_ajax_token()
    {
        if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') || !$GLOBALS['xoopsSecurity']->check(false)) {
            http_response_code(403);
            exit(_MD_JILLQUERY_ILLEGAL);
        }
    }

    // 關閉 XOOPS logger，供 AJAX／下載輸出
    public static function raw_output()
    {
        global $xoopsLogger;
        if (is_object($xoopsLogger)) {
            $xoopsLogger->activated = false;
        }
        error_reporting(0);
    }

    // 載入 tad_base 附的 PhpSpreadsheet
    public static function load_spreadsheet_lib()
    {
        $autoload = XOOPS_ROOT_PATH . '/modules/tad_base/vendor/autoload.php';
        if (!file_exists($autoload)) {
            redirect_header(XOOPS_URL . '/modules/jill_query/index.php', 5, _MD_JILLQUERY_NEED_TADBASE);
        }
        require_once $autoload;
    }

    /**
     * 讀取上傳的 Excel 第一個工作表，回傳 [列號 => [欄序 => 值]]（列號、欄序從 1 起）
     */
    public static function read_excel(string $back, string $field = 'excel'): array
    {
        $file = $_FILES[$field] ?? null; // xoops-lint-ignore 只在記憶體解析 Excel、不落地存檔，TadUpFiles 不適用
        if (empty($file['tmp_name']) || UPLOAD_ERR_OK !== $file['error'] || !is_uploaded_file($file['tmp_name'])) {
            redirect_header($back, 3, _MD_JILLQUERY_NOFILE);
        }
        if (!preg_match('/\.(xlsx|xls)$/i', (string) $file['name'], $m)) {
            redirect_header($back, 3, _MD_JILLQUERY_BAD_FILE);
        }
        $type = ('xlsx' === strtolower($m[1])) ? 'Xlsx' : 'Xls';
        // 讀 xlsx 需要 PHP zip 擴充（寫出不需要）
        if ('Xlsx' === $type && !class_exists('ZipArchive')) {
            redirect_header($back, 5, _MD_JILLQUERY_NEED_ZIP);
        }

        self::load_spreadsheet_lib();
        try {
            // 不用 setReadDataOnly：要靠儲存格格式判斷日期
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($type);
            $sheet  = $reader->load($file['tmp_name'])->getSheet(0);
        } catch (\Throwable $e) {
            redirect_header($back, 3, _MD_JILLQUERY_BAD_FILE);
        }

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $col = 1;
            foreach ($cellIterator as $cell) {
                $cells[$col++] = self::cell_value($cell);
            }
            $rows[$row->getRowIndex()] = $cells;
        }
        return $rows;
    }

    //儲存格取值：公式取計算值、日期轉 Y-m-d、其餘轉字串
    private static function cell_value($cell): string
    {
        if (null === $cell) {
            return '';
        }
        try {
            $value = $cell->getCalculatedValue();
        } catch (\Throwable $e) {
            $value = $cell->getValue();
        }
        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $value = $value->getPlainText();
        } elseif (is_numeric($value) && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
            $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        return trim((string) $value);
    }

    /**
     * 輸出 xlsx 下載（$rows 為二維陣列，第一列為標題）
     */
    public static function output_excel(string $filename, string $sheet_title, array $rows)
    {
        self::load_spreadsheet_lib();
        self::raw_output();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheet_title, 0, 31));
        $sheet->fromArray(array_values(array_map('array_values', $rows)), null, 'A1', true);

        $filename = str_replace(['"', "\r", "\n", '/', '\\'], '', $filename) . '.xlsx';
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($filename));
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    //儲存格顯示：先跳脫，http 開頭轉成連結
    public static function show_value($value): string
    {
        $value = (string) $value;
        $safe  = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if (preg_match('#^https?://#i', trim($value))) {
            return "<a href=\"{$safe}\" target=\"_blank\" rel=\"noopener noreferrer\">{$safe}</a>";
        }
        return $safe;
    }

    // 樣板用的 token 字串
    public static function token(): string
    {
        return $GLOBALS['xoopsSecurity']->createToken();
    }
}
