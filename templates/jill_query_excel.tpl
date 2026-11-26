<{$toolbar|default:''}>

<h3><a href="<{$xoops_url}>/modules/jill_query/index.php?qsn=<{$query.qsn}>"><i class="fa fa-reply" aria-hidden="true"></i> <{$query.title}></a></h3>

<div class="card card-body bg-light mb-3">
  <ol class="mb-2">
    <li><a href="<{$xoops_url}>/modules/jill_query/excel.php?op=sample&qsn=<{$query.qsn}>"><{$smarty.const._MD_JILLQUERY_SAMPLE}></a></li>
    <li><{$smarty.const._MD_JILLQUERY_STEP2}>
      <{if $query.ispublic == 2}>
        （<a href="<{$xoops_url}>/modules/jill_query/excel.php?op=users&qsn=<{$query.qsn}>"><i class="fa fa-users" aria-hidden="true"></i> <{$smarty.const._MD_JILLQUERY_USER}></a>）
      <{/if}>
    </li>
  </ol>
  <form action="<{$xoops_url}>/modules/jill_query/excel.php" method="post" enctype="multipart/form-data" class="row g-2 align-items-center">
    <div class="col-md-5">
      <label class="visually-hidden" for="excel"><{$smarty.const._MD_JILLQUERY_DATAMANAGEMENT}></label>
      <input type="file" name="excel" id="excel" class="form-control" accept=".xlsx,.xls" required>
    </div>
    <div class="col-md-5">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="continue_no" id="continue_no_0" value="0" checked>
        <label class="form-check-label" for="continue_no_0"><{$smarty.const._MD_CONTINUE_NO}></label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="continue_no" id="continue_no_1" value="on">
        <label class="form-check-label" for="continue_no_1"><{$smarty.const._MD_CONTINUE}></label>
      </div>
    </div>
    <div class="col-md-2">
      <input type="hidden" name="op" value="import">
      <input type="hidden" name="qsn" value="<{$query.qsn}>">
      <{$token_form}>
      <button type="submit" class="btn btn-primary w-100"><{$smarty.const._MD_JILLQUERY_DATAMANAGEMENT}></button>
    </div>
  </form>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
  <form action="<{$xoops_url}>/modules/jill_query/excel.php" method="get" class="d-flex gap-2">
    <input type="hidden" name="qsn" value="<{$query.qsn}>">
    <label class="visually-hidden" for="kw_qcsn"><{$smarty.const._MD_JILLQUERY_QCTITLESEARCH}></label>
    <select name="kw_qcsn" id="kw_qcsn" class="form-select" required>
      <option value=""><{$smarty.const._MD_JILLQUERY_QCTITLESEARCH}></option>
      <{foreach from=$all_col item=col}>
        <option value="<{$col.qcsn}>" <{if $kw_qcsn == $col.qcsn}>selected<{/if}>><{$col.qc_title}></option>
      <{/foreach}>
    </select>
    <label class="visually-hidden" for="keyword"><{$smarty.const._MD_JILLQUERY_KEYWORD}></label>
    <input type="text" id="keyword" name="keyword" class="form-control" placeholder="<{$smarty.const._MD_JILLQUERY_KEYWORD}>" value="<{$keyword}>" required>
    <button type="submit" class="btn btn-primary text-nowrap"><{$smarty.const._MD_JILLQUERY_SEARCH}></button>
    <{if $keyword}>
      <a href="<{$xoops_url}>/modules/jill_query/excel.php?qsn=<{$query.qsn}>" class="btn btn-link text-nowrap"><{$smarty.const._MD_JILLQUERY_BACKSEARCH}></a>
    <{/if}>
  </form>
  <{if $smarty.session.jill_query_adm|default:false}>
    <div>
      <a href="<{$xoops_url}>/modules/jill_query/admin/setcol.php?qsn=<{$query.qsn}>" class="btn btn-success btn-sm"><{$smarty.const._MD_JILLQUERY_SETCOL}></a>
      <a href="<{$xoops_url}>/modules/jill_query/admin/main.php?op=jill_query_edit&qsn=<{$query.qsn}>" class="btn btn-warning btn-sm"><{$smarty.const._TAD_EDIT}></a>
    </div>
  <{/if}>
</div>

<{if $result}>
  <h4><{$total|string_format:$smarty.const._MD_JILLQUERY_TITLE}></h4>
  <div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
      <thead>
        <tr>
          <th scope="col" class="text-center"><{$smarty.const._MD_JILLQUERY_ROW}></th>
          <{foreach from=$all_col item=col}>
            <th scope="col" class="text-center"><{$col.qc_title}></th>
          <{/foreach}>
          <th scope="col" class="text-center"><{$smarty.const._TAD_FUNCTION}></th>
        </tr>
      </thead>
      <tbody>
        <{foreach from=$result item=row}>
          <tr>
            <td class="text-center"><{$row.qrSort}></td>
            <{foreach from=$row.cells key=qcsn item=val}>
              <td id="<{$row.ssn}>-<{$qcsn}>" class="jq_data"><{$val|escape}></td>
            <{/foreach}>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-danger" onclick="jill_query_row_destroy_func(<{$row.ssn}>);" aria-label="<{$smarty.const._TAD_DEL}> <{$row.qrSort}>"><i class="fa fa-trash" aria-hidden="true"></i></button>
            </td>
          </tr>
        <{/foreach}>
      </tbody>
    </table>
  </div>
  <{$bar}>
<{else}>
  <div class="alert alert-warning text-center"><h4><{$smarty.const._MD_JILLQUERY_NODATA}></h4></div>
<{/if}>
