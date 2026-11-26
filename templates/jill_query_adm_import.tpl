<div class="container-fluid">
  <h3><{$smarty.const._MA_JILLQUERY_IMPORT_TITLE}></h3>

  <div class="alert alert-info">
    <ol class="mb-0">
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE1}></li>
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE2}></li>
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE3}></li>
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE8}></li>
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE4}></li>
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE5}></li>
      <li><{$smarty.const._MA_JILLQUERY_IMPORT_NOTE6}></li>
    </ol>
  </div>

  <form action="<{$xoops_url}>/modules/jill_query/admin/import.php" method="post" enctype="multipart/form-data" class="row g-2 align-items-center">
    <div class="col-sm-6">
      <label class="visually-hidden" for="excel"><{$smarty.const._MD_JILLQUERY_DATAMANAGEMENT}></label>
      <input type="file" name="excel" id="excel" class="form-control" accept=".xlsx,.xls" required>
    </div>
    <div class="col-sm-6">
      <input type="hidden" name="op" value="import">
      <{$token_form}>
      <button type="submit" class="btn btn-primary"><{$smarty.const._MD_JILLQUERY_DATAMANAGEMENT}></button>
    </div>
  </form>
</div>
