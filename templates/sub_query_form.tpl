<{* 查詢表單欄位；需傳入 form（Jill_query_data::form_data） *}>
<form action="<{$xoops_url}>/modules/jill_query/index.php#result" method="post" id="myForm">
  <{foreach from=$form.cols item=col}>
    <div class="row mb-2">
      <label class="col-sm-4 col-form-label text-sm-end" for="fill_<{$col.qcsn}>"><{$col.qc_title}></label>
      <div class="col-sm-8">
        <{if $col.is_email}>
          <input class="form-control" type="text" id="fill_<{$col.qcsn}>" value="<{$form.email}>" disabled readonly>
        <{else}>
          <input type="text" class="form-control<{if $col.required}> validate[required]<{/if}>" id="fill_<{$col.qcsn}>" name="fillValue[<{$col.qcsn}>]" placeholder="<{if $col.required}><{$smarty.const._MD_REQUIRED}><{else}><{$smarty.const._MD_OPTIONAL}><{/if}>">
        <{/if}>
      </div>
    </div>
  <{/foreach}>
  <{if $form.query.has_passwd}>
    <div class="row mb-2">
      <label class="col-sm-4 col-form-label text-sm-end" for="passwd_<{$form.query.qsn}>"><{$smarty.const._MD_JILLQUERY_PASSWD}></label>
      <div class="col-sm-8">
        <input type="password" name="passwd" id="passwd_<{$form.query.qsn}>" class="form-control validate[required]" placeholder="<{$smarty.const._MD_REQUIRED}>" autocomplete="off">
      </div>
    </div>
  <{/if}>
  <div class="text-center">
    <input type="hidden" name="qsn" value="<{$form.query.qsn}>">
    <input type="hidden" name="op" value="show_result">
    <button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i> <{$smarty.const._MD_JILLQUERY_SEARCH}></button>
  </div>
</form>
