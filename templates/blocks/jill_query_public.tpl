<h4><a href="<{$xoops_url}>/modules/jill_query/index.php?op=public_query&qsn=<{$block.qsn}>"><{$block.title}></a></h4>
<{if $block.result}>
  <{include file="$xoops_rootpath/modules/jill_query/templates/sub_result.tpl" show_col=$block.show_col result=$block.result}>
  <div class="text-end">[ <a href="<{$xoops_url}>/modules/jill_query/index.php?op=public_query&qsn=<{$block.qsn}>">more...</a> ]</div>
<{/if}>
