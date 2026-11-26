# jill_query 模組專屬規範

> XOOPS 通用規則（核心物件、安全性、架構鐵則、TadTools、CodeGraph 邊界）
> 由全域 Skill `xoops-core-dev` 提供，此處不重複。
>
> v3.0（2026-11-26）已改寫為 XOOPS 2.7.4 架構，相容 2.5.11，需 PHP 8.3 以上。

---

## 1. 模組基本資訊

| 項目 | 值 |
|---|---|
| 模組名稱 | `jill_query`（Excel 匯入式資料查詢：承辦人上傳名單，使用者依欄位查詢自己的資料） |
| 命名空間 | `XoopsModules\Jill_query`（PSR-4，`class/` 目錄） |
| 架構書 | 無，本檔即速查 |
| 管理員 Session | `$_SESSION['jill_query_adm']`（`Tools::get_session()` 每次請求重算，判斷模組管理員） |
| 資料表 | `jill_query`, `jill_query_col`, `jill_query_col_value`, `jill_query_sn`, `jill_query_tags`；另讀取核心 `users`（會員名單下載） |
| 外部相依 | TadTools；Excel 讀寫用 tad_base 模組 vendor 內的 PhpSpreadsheet 5（`Tools::load_spreadsheet_lib()`） |

---

## 2. 檔案結構速查

```text
前台入口：index.php（查詢）、excel.php（承辦人資料管理、範本／會員下載、儲存格 AJAX）
後台入口：admin/main.php（事件 CRUD、複製、匯出）、admin/setcol.php（欄位、運算子、排序／切換／改名 AJAX）、admin/import.php、admin/tag.php、admin/index.php、admin/about.php
共用頭尾：header.php、interface.php（選單＋get_session）、footer.php、admin/header.php、admin/footer.php
業務邏輯（全 static）：class/Jill_query.php（事件）、class/Jill_query_col.php（欄位）、class/Jill_query_data.php（資料列、查詢、匯入匯出）、class/Jill_query_tags.php（標籤）、class/Tools.php（權限、過濾、Excel）
升級：class/Update.php＋include/onUpdate.php（2.5 以前的升級鏈，保留）
樣板：templates/jill_query_index.tpl、templates/jill_query_excel.tpl、templates/jill_query_adm_main.tpl、templates/jill_query_adm_setcol.tpl、templates/jill_query_adm_import.tpl、templates/jill_query_adm_tag.tpl
子樣板（以檔案路徑 include，未登記在 xoops_version）：templates/sub_query_form.tpl、templates/sub_result.tpl
區塊：blocks/jill_query_show.php、blocks/jill_query_public.php
前端 JS：（無獨立檔，排序／切換寫在 templates/jill_query_adm_setcol.tpl）
語言包：language/tchinese_utf8/main.php, modinfo.php, admin.php, blocks.php；language/english/ 同名檔
```

---

## 3. 資料表結構

| 資料表 | 用途 | 關鍵欄位 |
|---|---|---|
| `jill_query` | 查詢事件 | `qsn` PK、`editorEmail`（承辦人，以 `;` 存）、`isEnable`、`passwd`（明文，後台要能看到發給使用者）、`ispublic` enum('0','1','2')、`read_group`（JSON 群組陣列）、`tag_sn`、`counter` |
| `jill_query_col` | 事件的欄位定義 | `qcsn` PK、`qsn`、`qc_title`、`qcsnSearch`、`search_operator`（and/or，同事件搜尋欄共用一值）、`isShow`、`isLike`、`qcSort` |
| `jill_query_sn` | 資料列 | `ssn` PK、`qsn`、`qrSort`（列序 1..N）、`uid`（匯入者）、`createDate` |
| `jill_query_col_value` | 儲存格值 | PK (`ssn`,`qcsn`)、`fillValue`（原文，顯示時才跳脫） |
| `jill_query_tags` | 事件分類標籤 | `tag_sn` PK、`tag`、`font_color`、`color`（只接受 #rgb/#rrggbb）、`enable` |

全部 MyISAM、`utf8`，無外鍵、無交易；結構與 2.51 版相同，升級不需改表。

`ispublic` 語意：`0` 需登入（依 `read_group`）、`1` 公開（可看全部資料列、公開區塊）、`2` 以登入 Email 查詢（`qc_title='email'` 的欄位由伺服器端帶入登入者 email，運算子強制 and，`read_group` 自動排除訪客）。

---

## 4. 權限模型

- 模組管理員：`Tools::isAdmin()`；後台頁另有 XOOPS `cp_header` 把關。
- 承辦人：`Tools::is_undertaker($query)` = 管理員，或登入 email 在 `editorEmail` 且事件啟用中。
- 讀取：`Tools::group_perm($read_group)`；`Jill_query::get_readable()` 一次檢查存在、啟用、群組。
- 資料列歸屬：管理員看全部；其他承辦人只能看／改／刪自己 `uid` 匯入的列（`Jill_query_data::owner_where()`）。
- 寫入：入口檔先 `Tools::chk_post()` 再 `Utility::xoops_security_check()`；AJAX 用 `Tools::chk_ajax_token()`（`check(false)` 不清 token，同頁可多次操作）。類別內不再驗 token，避免同一 token 被驗兩次而失效。

---

## 5. 匯入匯出格式

- 後台建事件（`admin/import.php`）：第 1 列欄位名；`*` 開頭為搜尋欄（or），`!` 開頭為登入 Email 欄（欄名改 `email`、and）。事件名稱取檔名。
- 承辦人匯入（`excel.php`）：第 1 列表頭略過，依欄位 `qcSort` 順序對應；選「接續」則 `qrSort` 往後接，否則先刪自己匯入的舊資料。
- 儲存格取值：公式取計算值、RichText 取純文字、日期格式轉 `Y-m-d`。不可用 `setReadDataOnly(true)`，否則日期格式會遺失。
- 讀 `.xlsx` 需要 PHP `zip` 擴充（寫出不需要）；缺少時顯示 `_MD_JILLQUERY_NEED_ZIP`。`.xls` 不需要 zip。

---

## 6. 模組特有注意事項

- Smarty 為 4.5（兩站皆是）：不可用 `includeq`，也不可把 PHP 函式當修飾器（`in_array`、`count`、`basename` 會產生棄用警告），需要的值在 PHP 端算好再 assign。
- 取參數一律用 `Xmf\Request`。使用者會輸入的文字（標題、密碼、欄位名、查詢值、儲存格、標籤）用 `getText`，陣列查詢值用 `getVar(..., 'array', Request::MASK_ALLOW_RAW)`；`getString` 會剝掉 HTML，「<5歲」這類內容會被改掉。寫入時 escape、輸出時跳脫。
- 區塊函式先 `Tools::get_session()` 再判斷權限；`language/*/blocks.php` 不可重複定義 `language/tchinese_utf8/main.php` 已有的常數（區塊會另外載入 main）。
- 2.5.11 測試站的 `php83/php_production.ini` 未開 `extension=zip`，在那裡測 `.xlsx` 匯入會出現缺 zip 的提示，屬環境問題。
