<div class="container-fluid">
  <h3><a href="<{$xoops_url}>/modules/jill_query/index.php?qsn=<{$query.qsn}>"><{$query.title}></a></h3>
  <div id="save_msg" role="status" class="text-success"></div>

  <form action="<{$xoops_url}>/modules/jill_query/admin/setcol.php" method="post" id="myForm">
    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered align-middle">
        <thead>
          <tr>
            <th scope="col" class="text-center"><{$smarty.const._MA_JILLQUERY_QC_TITLE}></th>
            <th scope="col" class="text-center"><{$smarty.const._MA_JILLQUERY_QCSNSEARCH}></th>
            <th scope="col" class="text-center"><{$smarty.const._MA_JILLQUERY_ISSHOW}></th>
            <th scope="col" class="text-center"><{$smarty.const._MA_JILLQUERY_ISLIKE}></th>
            <th scope="col" class="text-center"><{$smarty.const._TAD_FUNCTION}></th>
          </tr>
        </thead>
        <tbody id="sort">
          <{foreach from=$all_col item=col}>
            <tr id="tr_<{$col.qcsn}>">
              <td class="text-center">
                <{if $col.is_email}><{$col.qc_title}><{else}><div class="qc_title" id="qc_title:<{$col.qcsn}>"><{$col.qc_title}></div><{/if}>
              </td>
              <{foreach from=['qcsnSearch','isShow','isLike'] item=name}>
                <td class="text-center">
                  <{if $col.is_email || ($name == 'isLike' && !$col.qcsnSearch)}>
                    <i class="fa <{if $col.$name}>fa-check text-success<{else}>fa-times text-muted<{/if}>" aria-hidden="true"></i>
                  <{else}>
                    <button type="button" class="btn btn-link p-0 jq_toggle" data-qcsn="<{$col.qcsn}>" data-name="<{$name}>" aria-pressed="<{if $col.$name}>true<{else}>false<{/if}>" aria-label="<{$col.qc_title}> <{$name}>">
                      <i class="fa <{if $col.$name}>fa-check text-success<{else}>fa-times text-danger<{/if}>" aria-hidden="true"></i>
                    </button>
                  <{/if}>
                </td>
              <{/foreach}>
              <td class="text-center text-nowrap">
                <i class="fa fa-sort" style="cursor:move;" title="<{$smarty.const._TAD_SORTABLE}>" aria-hidden="true"></i>
                <{if $col.can_del}>
                  <button type="button" class="btn btn-sm btn-danger" onclick="jill_query_col_destroy_func(<{$col.qcsn}>);"><{$smarty.const._TAD_DEL}></button>
                <{/if}>
              </td>
            </tr>
          <{/foreach}>
        </tbody>
        <tfoot>
          <tr>
            <td>
              <label class="visually-hidden" for="new_qc_title"><{$smarty.const._MA_JILLQUERY_QC_TITLE}></label>
              <input type="text" name="qc_title" id="new_qc_title" class="form-control validate[required]" placeholder="<{$smarty.const._MA_JILLQUERY_QC_TITLE}>">
            </td>
            <td class="text-center"><input type="checkbox" class="form-check-input" name="qcsnSearch" value="1" aria-label="<{$smarty.const._MA_JILLQUERY_QCSNSEARCH}>"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input" name="isShow" value="1" checked aria-label="<{$smarty.const._MA_JILLQUERY_ISSHOW}>"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input" name="isLike" value="1" aria-label="<{$smarty.const._MA_JILLQUERY_ISLIKE}>"></td>
            <td class="text-center">
              <input type="hidden" name="qsn" value="<{$query.qsn}>">
              <input type="hidden" name="op" value="col_store">
              <{$token_form}>
              <button type="submit" class="btn btn-primary"><{$smarty.const._MA_JILLQUERY_ADDCOL}></button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </form>

  <div class="row">
    <div class="col-md-6">
      <h4><{$smarty.const._MA_JILLQUERY_SETSEARCH}></h4>
      <{if $search_count > 1}>
        <form action="<{$xoops_url}>/modules/jill_query/admin/setcol.php" method="post" class="card card-body bg-light">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="search_operator" id="op_and" value="and" <{if $operator == 'and'}>checked<{/if}>>
            <label class="form-check-label" for="op_and"><{$smarty.const._MA_JILLQUERY__OPERATOR_AND}> <strong class="text-danger">(<{$smarty.const._FILL_ALL_FIELDS}>)</strong></label>
          </div>
          <{if $query.ispublic != 2}>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="search_operator" id="op_or" value="or" <{if $operator == 'or'}>checked<{/if}>>
              <label class="form-check-label" for="op_or"><{$smarty.const._MA_JILLQUERY__OPERATOR_OR}> <strong class="text-danger">(<{$smarty.const._FILL_ANY_FIELDS}>)</strong></label>
            </div>
          <{/if}>
          <div class="mt-2">
            <input type="hidden" name="qsn" value="<{$query.qsn}>">
            <input type="hidden" name="op" value="update_operator">
            <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$csrf_token}>">
            <button type="submit" class="btn btn-primary"><{$smarty.const._TAD_SAVE}></button>
          </div>
        </form>
      <{elseif $search_count == 1}>
        <div class="alert alert-info"><{$smarty.const._MA_JILLQUERY_SETSEARCH_NON}></div>
      <{else}>
        <div class="alert alert-warning"><{$smarty.const._MA_EMPTYCOL}></div>
      <{/if}>
    </div>
    <div class="col-md-6">
      <h4><{$smarty.const._MA_JILLQUERY_SETCOL_STEP2}></h4>
      <div class="card card-body bg-light">
        <{foreach from=$search_col item=col}>
          <div class="row mb-2">
            <div class="col-sm-4 text-sm-end"><{$col.qc_title}></div>
            <div class="col-sm-8"><input type="text" class="form-control" disabled aria-label="<{$col.qc_title}>"></div>
          </div>
        <{/foreach}>
      </div>
    </div>
  </div>

  <div class="text-end mt-3">
    <a href="<{$xoops_url}>/modules/jill_query/admin/main.php?op=jill_query_edit&qsn=<{$query.qsn}>" class="btn btn-warning"><{$smarty.const._TAD_EDIT}></a>
    <a href="<{$xoops_url}>/modules/jill_query/admin/main.php" class="btn btn-primary"><{$smarty.const._MA_JILLQUERY__MAIN}></a>
  </div>
</div>

<script>
$(function () {
  var url = '<{$xoops_url}>/modules/jill_query/admin/setcol.php';
  var qsn = '<{$query.qsn}>';
  var token = '<{$csrf_token}>';

  $('#sort').sortable({opacity: 0.6, cursor: 'move', update: function () {
    $.post(url, $(this).sortable('serialize') + '&op=col_sort&qsn=' + qsn + '&XOOPS_TOKEN_REQUEST=' + token, function (msg) {
      $('#save_msg').text(msg);
    });
  }});

  $('.jq_toggle').on('click', function () {
    var $btn = $(this);
    $.post(url, {op: 'col_toggle', qsn: qsn, qcsn: $btn.data('qcsn'), name: $btn.data('name'), XOOPS_TOKEN_REQUEST: token}, function (res) {
      if (res && res.success) {
        location.reload();
      }
    }, 'json');
  });
});
</script>
