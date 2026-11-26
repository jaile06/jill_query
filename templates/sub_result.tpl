<{* 資料表；需傳入 show_col、result *}>
<div class="table-responsive">
  <table class="table table-striped table-hover table-sm">
    <thead>
      <tr>
        <th scope="col" class="text-center"><{$smarty.const._MD_JILLQUERY_ROW}></th>
        <{foreach from=$show_col item=col}>
          <th scope="col" class="text-center"><{$col.qc_title}></th>
        <{/foreach}>
      </tr>
    </thead>
    <tbody>
      <{foreach from=$result item=row name=rows}>
        <tr>
          <td class="text-center"><{$smarty.foreach.rows.iteration}></td>
          <{foreach from=$row.cells item=val}>
            <td><{$val}></td>
          <{/foreach}>
        </tr>
      <{/foreach}>
    </tbody>
  </table>
</div>
