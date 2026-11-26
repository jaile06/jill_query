<{$toolbar|default:''}>

<{if $now_op == 'list'}>
  <{if $tags}>
    <nav class="mb-3" aria-label="<{$smarty.const._MD_JILLQUERY_PREFIX_TAG}>">
      <a href="<{$xoops_url}>/modules/jill_query/index.php" class="btn btn-sm <{if !$tag_sn}>btn-primary<{else}>btn-outline-primary<{/if}>"><{$smarty.const._MD_JILLQUERY_ALL}></a>
      <{foreach from=$tags item=tag}>
        <a href="<{$xoops_url}>/modules/jill_query/index.php?tag_sn=<{$tag.tag_sn}>" class="btn btn-sm" style="background-color:<{$tag.color}>;color:<{$tag.font_color}>;<{if $tag_sn == $tag.tag_sn}>outline:2px solid #000;<{/if}>"><{$tag.tag}></a>
      <{/foreach}>
    </nav>
  <{/if}>

  <{if $all_query}>
    <div class="list-group mb-3">
      <{foreach from=$all_query item=q}>
        <a href="<{$xoops_url}>/modules/jill_query/index.php?qsn=<{$q.qsn}>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
          <i class="fa fa-search" aria-hidden="true"></i>
          <span class="flex-grow-1"><{$q.title}></span>
          <{if $q.tag}><span class="badge" style="background-color:<{$q.tag.color}>;color:<{$q.tag.font_color}>;"><{$q.tag.tag}></span><{/if}>
        </a>
      <{/foreach}>
    </div>
    <{$bar}>
  <{else}>
    <div class="alert alert-info text-center"><h3><{$smarty.const._MD_NOFORM}></h3></div>
  <{/if}>

<{elseif $now_op == 'search'}>
  <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <h3 class="flex-grow-1 mb-0"><a href="<{$xoops_url}>/modules/jill_query/index.php" id="result"><i class="fa fa-reply" aria-hidden="true"></i> <{$query.title}></a></h3>
    <{if $undertaker}>
      <a href="<{$xoops_url}>/modules/jill_query/excel.php?qsn=<{$query.qsn}>" class="btn btn-danger btn-sm"><{$smarty.const._MD_JILLQUERY_DATAMANAGEMENT}></a>
    <{/if}>
    <{if $query.ispublic == 1}>
      <a href="<{$xoops_url}>/modules/jill_query/index.php?op=public_query&qsn=<{$query.qsn}>" class="btn btn-primary btn-sm"><{$smarty.const._MD_JILLQUERY_PUBLIC}></a>
    <{/if}>
    <{if $smarty.session.jill_query_adm|default:false}>
      <a href="<{$xoops_url}>/modules/jill_query/admin/setcol.php?qsn=<{$query.qsn}>" class="btn btn-success btn-sm"><{$smarty.const._MD_JILLQUERY_SETCOL}></a>
      <a href="<{$xoops_url}>/modules/jill_query/admin/main.php?op=jill_query_edit&qsn=<{$query.qsn}>" class="btn btn-warning btn-sm"><{$smarty.const._TAD_EDIT}></a>
    <{/if}>
  </div>

  <{if $query.directions}>
    <div class="alert alert-success"><{$query.directions}></div>
  <{/if}>

  <{if $no_search_col|default:false}>
    <div class="alert alert-warning text-center"><h3><{$smarty.const._MD_JILLQUERY_EMPTY_SEARCH}></h3></div>
  <{else}>
    <div class="card card-body bg-light mb-3">
      <{include file="$xoops_rootpath/modules/jill_query/templates/sub_query_form.tpl" form=$form}>
    </div>
  <{/if}>

  <{if $show_result|default:false}>
    <{if $result}>
      <h4><{$smarty.const._MD_JILLQUERY_RESULT}> <small class="text-muted"><{$result_total}></small></h4>
      <{include file="$xoops_rootpath/modules/jill_query/templates/sub_result.tpl" show_col=$show_col result=$result}>
    <{else}>
      <div class="alert alert-warning text-center"><h4><{$smarty.const._MD_JILLQUERY_NODATA}></h4></div>
    <{/if}>
  <{/if}>

<{elseif $now_op == 'public_query'}>
  <h3><a href="<{$xoops_url}>/modules/jill_query/index.php?qsn=<{$query.qsn}>"><{$smarty.const._MD_JILLQUERY_PUBLICQUERY}><{$query.title}></a></h3>
  <form action="<{$xoops_url}>/modules/jill_query/index.php" method="get" class="row g-2 mb-3">
    <div class="col-sm-6">
      <label class="visually-hidden" for="public_qsn"><{$smarty.const._MD_JILLQUERY_PUBLIC}></label>
      <select class="form-select" name="qsn" id="public_qsn">
        <{foreach from=$public_options key=sn item=title}>
          <option value="<{$sn}>" <{if $sn == $query.qsn}>selected<{/if}>><{$title}></option>
        <{/foreach}>
      </select>
    </div>
    <div class="col-sm-2">
      <input type="hidden" name="op" value="public_query">
      <button type="submit" class="btn btn-primary"><{$smarty.const._MD_JILLQUERY_SEARCH}></button>
    </div>
  </form>
  <{if $result}>
    <{include file="$xoops_rootpath/modules/jill_query/templates/sub_result.tpl" show_col=$show_col result=$result}>
    <{$bar}>
  <{else}>
    <div class="alert alert-warning text-center"><h4><{$smarty.const._MD_JILLQUERY_NODATA}></h4></div>
  <{/if}>
<{/if}>
