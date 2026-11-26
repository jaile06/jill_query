<?php
namespace XoopsModules\Jill_query;

use Xmf\Request;
use XoopsModules\Tadtools\MColorPicker;
use XoopsModules\Tadtools\SweetAlert2;
use XoopsModules\Tadtools\Utility;

/**
 * 事件分類標籤（jill_query_tags）
 */
class Jill_query_tags
{
    //取得全部標籤 [tag_sn => 已跳脫資料]
    public static function get_all(bool $only_enable = false): array
    {
        global $xoopsDB;
        $where  = $only_enable ? "WHERE `enable` = '1'" : '';
        $sql    = 'SELECT * FROM `' . $xoopsDB->prefix('jill_query_tags') . "` {$where} ORDER BY `tag_sn`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $tags   = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $tags[(int) $data['tag_sn']] = [
                'tag_sn'     => (int) $data['tag_sn'],
                'tag'        => htmlspecialchars($data['tag'], ENT_QUOTES, 'UTF-8'),
                'font_color' => self::color($data['font_color'], '#ffffff'),
                'color'      => self::color($data['color'], '#6c757d'),
                'enable'     => (int) $data['enable'],
            ];
        }
        return $tags;
    }

    // 只接受 #rgb / #rrggbb，避免把任意字串塞進 style
    private static function color($value, string $default): string
    {
        $value = trim((string) $value);
        return preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $value) ? $value : $default;
    }

    //後台：標籤管理頁
    public static function index($edit_sn = 0)
    {
        global $xoopsDB, $xoopsTpl;
        Tools::chk_admin();

        $used   = [];
        $sql    = 'SELECT `tag_sn`, COUNT(*) FROM `' . $xoopsDB->prefix('jill_query') . '` GROUP BY `tag_sn`';
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        while (list($tag_sn, $count) = $xoopsDB->fetchRow($result)) {
            $used[(int) $tag_sn] = (int) $count;
        }

        $tags = self::get_all();
        foreach ($tags as $sn => $tag) {
            $tags[$sn]['amount'] = $used[$sn] ?? 0;
            $tags[$sn]['edit']   = ((int) $edit_sn === $sn);
        }

        $token = Tools::token();
        $xoopsTpl->assign('csrf_token', $token);
        $xoopsTpl->assign('all_tags', array_values($tags));
        $xoopsTpl->assign('edit_sn', (int) $edit_sn);

        $MColorPicker = new MColorPicker('.color');
        $MColorPicker->render();
        $SweetAlert = new SweetAlert2();
        $SweetAlert->setVar('method', 'post');
        $SweetAlert->render('jill_query_tag_destroy_func', XOOPS_URL . "/modules/jill_query/admin/tag.php?op=tag_destroy&XOOPS_TOKEN_REQUEST={$token}&tag_sn=", 'tag_sn');
        Utility::token_form();
    }

    // POST 欄位
    private static function post_data(): array
    {
        global $xoopsDB;
        return [
            'tag'        => $xoopsDB->escape(Request::getText('tag', '', 'POST')),
            'font_color' => self::color(Request::getString('font_color', '', 'POST'), '#ffffff'),
            'color'      => self::color(Request::getString('color', '', 'POST'), '#6c757d'),
            'enable'     => Request::getInt('enable', 0, 'POST') ? 1 : 0,
        ];
    }

    public static function store()
    {
        global $xoopsDB;
        Tools::chk_admin();
        $d = self::post_data();
        if ('' === $d['tag']) {
            return;
        }
        $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_query_tags') . "` (`tag`, `font_color`, `color`, `enable`)
            VALUES ('{$d['tag']}', '{$d['font_color']}', '{$d['color']}', '{$d['enable']}')";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    public static function update($tag_sn)
    {
        global $xoopsDB;
        Tools::chk_admin();
        $d = self::post_data();
        if ('' === $d['tag']) {
            return;
        }
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_tags') . "` SET
            `tag` = '{$d['tag']}', `font_color` = '{$d['font_color']}', `color` = '{$d['color']}', `enable` = '{$d['enable']}'
            WHERE `tag_sn` = '" . (int) $tag_sn . "'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //切換啟用
    public static function toggle($tag_sn)
    {
        global $xoopsDB;
        Tools::chk_admin();
        $sql = 'UPDATE `' . $xoopsDB->prefix('jill_query_tags') . "` SET `enable` = IF(`enable` = '1', '0', '1') WHERE `tag_sn` = '" . (int) $tag_sn . "'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //刪除（仍有事件使用時不刪）
    public static function destroy($tag_sn)
    {
        global $xoopsDB;
        Tools::chk_admin();
        $tag_sn = (int) $tag_sn;
        $sql    = 'SELECT COUNT(*) FROM `' . $xoopsDB->prefix('jill_query') . "` WHERE `tag_sn` = '{$tag_sn}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        list($count) = $xoopsDB->fetchRow($result);
        if ($count > 0) {
            return;
        }
        $sql = 'DELETE FROM `' . $xoopsDB->prefix('jill_query_tags') . "` WHERE `tag_sn` = '{$tag_sn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }
}
